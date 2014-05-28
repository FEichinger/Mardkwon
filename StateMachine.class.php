<?php
	require_once("MarkdownOpCode.class.php");
	require_once("MarkdownChar.class.php");
	
	class StateMachine {
		const MAINFLOW = 0x0001;
		const WHITESPACE = 0x0002;
		const NEWLINE = 0x0003;
		const ASTERISK_OPEN = 0x0004;
		const ASTERISK_CLOSE = 0x0005;
		const UNDERLINE_OPEN = 0x0006;
		const UNDERLINE_CLOSE = 0x0007;
		const NEWPARAGRAPH = 0x0008;
		const ULISTNEWLINE = 0x0010;
		const ULISTITEM = 0x0011;
		const HASH_OPEN = 0x0012;
		const HEADLINE = 0x0013;
		const HASH_CLOSE = 0x0014;
		
		private $asterisk = 0;
		private $underline = 0;
		private $hash = 0;
		
		private $bold_ast = false;
		private $italic_ast = false;
		private $bold_und = false;
		private $italic_und = false;
		private $ulist = false;
		private $headline = 0;
		
		private $current_state = self::MAINFLOW;
		
		private $stack_out;
		
		public function __construct(ParserStack &$stack) {
			$this->stack_out = $stack;
		}
		
		public function process_character($c) {
			switch($this->current_state) {
				case self::MAINFLOW:
					switch($c) {
						case " ":
						case ".":
						case "'":
						case "\"":
							$this->current_state = self::WHITESPACE;
							$this->append_character($c);
							break;
							
						case "*":
							if($this->italic_ast || $this->bold_ast) {
								$this->asterisk++;
								$this->current_state = self::ASTERISK_CLOSE;
							}
							else $this->append_character($c);
							break;
							
						case "_":
							if($this->italic_und || $this->bold_und) {
								$this->underline++;
								$this->current_state = self::UNDERLINE_CLOSE;
							}
							else $this->append_character($c);
							break;
							
						case "\n":
							$this->current_state = self::NEWLINE;
							break;
							
						default:
							$this->append_character($c);
					}
					break;
					
				case self::NEWPARAGRAPH:
					switch($c) {
						case "*":
							if(!($this->italic_ast || $this->bold_ast)) {
								$this->asterisk++;
								$this->current_state = self::ASTERISK_OPEN;
							}
							else $this->append_character($c);
							break;
							
						case "_":
							if(!($this->italic_und || $this->bold_und)) {
								$this->underline++;
								$this->current_state = self::UNDERLINE_OPEN;
							}
							else $this->append_character($c);
							break;
						
						case "-":
							$this->switch_ulist(true);
							$this->add_listitem();
							$this->current_state = self::ULISTITEM;
							break;
							
						case "#":
							if(!($this->headline)) {
								$this->hash++;
								$this->current_state = self::HASH_OPEN;
							}
							else $this->append_character($c);
							break;
							
						default:
							$this->current_state = self::MAINFLOW;
							$this->append_character($c);
					}
					break;
					
				case self::ULISTITEM:
					switch($c) {
						case "\n":
							$this->current_state = self::ULISTNEWLINE;
							$this->append_character($c);
							break;
							
						default:
							$this->append_character($c);
					}
					break;
					
				case self::ULISTNEWLINE:
					switch($c) {
						case " ":
							$this->append_character($c);
							break;
							
						case "-":
							$this->add_listitem();
							$this->current_status = self::ULISTITEM;
							break;
							
						case "\n":
							$this->current_state = self::NEWPARAGRAPH;
							$this->switch_ulist(false);
							$this->append_character($c);
							break;
						
						default:
							$this->current_state = self::MAINFLOW;
							$this->append_character($c);
					}
					break;
					
				case self::NEWLINE:
					switch($c) {
						case "*":
							if(!($this->italic_ast || $this->bold_ast)) {
								$this->asterisk++;
								$this->current_state = self::ASTERISK_OPEN;
							}
							else $this->append_character($c);
							break;
							
						case "_":
							if(!($this->italic_und || $this->bold_und)) {
								$this->underline++;
								$this->current_state = self::UNDERLINE_OPEN;
							}
							else $this->append_character($c);
							break;
							
						case "#":
							if(!($this->headline)) {
								$this->hash++;
								$this->current_state = self::HASH_OPEN;
							}
							else $this->append_character($c);
							break;
							
						case "\n":
							if($this->ulist) $this->switch_ulist(false);
							$this->new_paragraph();
							$this->current_state = self::NEWPARAGRAPH;
							break;
							
						default:
							$this->current_state = self::MAINFLOW;
							$this->append_character($c);
					}
					break;
					
				case self::WHITESPACE:
					switch($c) {
						case "*":
							if(!($this->italic_ast || $this->bold_ast)) {
								$this->asterisk++;
								$this->current_state = self::ASTERISK_OPEN;
							}
							else $this->append_character($c);
							break;
							
						case "_":
							if(!($this->italic_und || $this->bold_und)) {
								$this->underline++;
								$this->current_state = self::UNDERLINE_OPEN;
							}
							else $this->append_character($c);
							break;
							
						case "\n":
							$this->current_state = self::NEWLINE;
							$this->add_linebreak();
							break;
							
						default:
							$this->current_state = self::MAINFLOW;
							$this->append_character($c);
					}
					break;
					
				case self::ASTERISK_OPEN:
					switch($c) {
						case "*":
							$this->asterisk++;
							break;
							
						case " ":
						case ".":
						case "'":
						case "\"":
							$this->current_state = self::WHITESPACE;
							$this->append_character($c);
							break;
							
						case "\n":
							$this->current_state = self::NEWLINE;
							break;
							
						default:
							if($this->asterisk === 1) $this->switch_italic_ast(true);
							else if($this->asterisk === 2) $this->switch_bold_ast(true);
							else if($this->asterisk === 3) { $this->switch_italic_ast(true); $this->switch_bold_ast(true); }
							$this->asterisk = 0;
							$this->current_state = self::MAINFLOW;
							$this->append_character($c);
					}
					break;
					
				case self::UNDERLINE_OPEN:
					switch($c) {
						case "_":
							$this->underline++;
							break;
							
						case " ":
						case ".":
						case "'":
						case "\"":
							$this->current_state = self::WHITESPACE;
							$this->append_character($c);
							break;
							
						case "\n":
							$this->current_state = self::NEWLINE;
							$this->append_character($c);
							break;
							
						default:
							if($this->underline === 1) $this->switch_italic_und(true);
							else if($this->underline === 2) $this->switch_bold_und(true);
							else if($this->underline === 3) { $this->switch_italic_und(true); $this->switch_bold_und(true); }
							$this->underline = 0;
							$this->current_state = self::MAINFLOW;
							$this->append_character($c);
					}
					break;
					
				case self::ASTERISK_CLOSE:
					switch($c) {
						case "*":
							$this->asterisk++;
							break;
							
						case " ":
						case ".":
						case "'":
						case "\"":
							if($this->asterisk === 1) $this->switch_italic_ast(false);
							else if($this->asterisk === 2) $this->switch_bold_ast(false);
							else if($this->asterisk === 3) { $this->switch_italic_ast(false); $this->switch_bold_ast(false); }
							$this->asterisk = 0;
							$this->current_state = self::WHITESPACE;
							$this->append_character($c);
							break;
							
						case "\n":
							$this->current_state = self::NEWLINE;
							$this->append_character($c);
							break;
							
						default:
							$this->current_state = self::MAINFLOW;
							$this->append_character($c);
					}
					break;
					
				case self::UNDERLINE_CLOSE:
					switch($c) {
						case "_":
							$this->underline++;
							break;
							
						case " ":
						case ".":
						case "'":
						case "\"":
							if($this->underline === 1) $this->switch_italic_und(false);
							else if($this->underline === 2) $this->switch_bold_und(false);
							else if($this->underline === 3) { $this->switch_italic_und(false); $this->switch_bold_und(false); }
							$this->underline = 0;
							$this->current_state = self::WHITESPACE;
							$this->append_character($c);
							break;
							
						case "\n":
							$this->current_state = self::NEWLINE;
							$this->append_character($c);
							break;
							
						default:
							$this->current_state = self::MAINFLOW;
							$this->append_character($c);
					}
					break;
					
				case self::HASH_OPEN:
					switch($c) {
						case "#":
							$this->hash++;
							break;
							
						default:
							$this->headline = $this->hash;
							$this->switch_headline($this->headline, true);
							$this->hash = 0;
							$this->current_state = self::HEADLINE;
							$this->append_character($c);
					}
					break;
					
				case self::HEADLINE:
					switch($c) {
						case "#":
							$this->current_state = self::HASH_CLOSE;
							break;
							
						default:
							$this->append_character($c);
					}
					break;
					
				case self::HASH_CLOSE:
					switch($c) {
						case "#":
							break;
						
						case "\n":
							$this->switch_headline($this->headline, false);
							$this->headline = 0;
							$this->current_state = self::NEWLINE;
							break;
							
						default:
							$this->current_state = self::HEADLINE;
							$this->append_character($c);
					}
					break;
					
				default:
					throw new Exception("Error");
			}
		}
		
		private function switch_italic_ast($on) {
			$this->italic_ast = $on;
			if($on) $this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::ITALIC_ON));
			else $this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::ITALIC_OFF));
		}
		
		private function switch_bold_ast($on) {
			$this->bold_ast = $on;
			if($on) $this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::BOLD_ON));
			else $this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::BOLD_OFF));
		}
		
		private function switch_italic_und($on) {
			$this->italic_und = $on;
			if($on) $this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::ITALIC_ON));
			else $this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::ITALIC_OFF));
		}
		
		private function switch_bold_und($on) {
			$this->bold_und = $on;
			if($on) $this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::BOLD_ON));
			else $this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::BOLD_OFF));
		}
		
		private function append_character($c) {
			$this->stack_out->push(new MarkdownChar($c));
		}
		
		private function add_linebreak() {
			$this->stack_out->push(new MarkdownopCode(MarkdownOpCode::LINEBREAK));
		}
		
		private function new_paragraph() {
			$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::PARAGRAPH_END));
			$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::PARAGRAPH_START));
		}
		
		private function switch_ulist($on) {
			$this->ulist = $on;
			if($on) {
				$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::PARAGRAPH_END));
				$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::ULIST_START));
			}
			else {
				$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::ULIST_END));
				$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::PARAGRAPH_START));
			}
		}
		
		private function add_listitem() {
			$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::LISTITEM));
		}
		
		private function switch_headline($level, $on) {
			switch($level) {
				case 1: $opcode_o = MarkdownOpCode::H1_ON; $opcode_c = MarkdownOpCode::H1_OFF; break;
				case 2: $opcode_o = MarkdownOpCode::H2_ON; $opcode_c = MarkdownOpCode::H2_OFF; break;
				case 3: $opcode_o = MarkdownOpCode::H3_ON; $opcode_c = MarkdownOpCode::H3_OFF; break;
				case 4: $opcode_o = MarkdownOpCode::H4_ON; $opcode_c = MarkdownOpCode::H4_OFF; break;
				case 5: $opcode_o = MarkdownOpCode::H5_ON; $opcode_c = MarkdownOpCode::H5_OFF; break;
				default: $opcode_o = MarkdownOpCode::H6_ON; $opcode_c = MarkdownOpCode::H6_OFF; break;
			}
			
			if($on) $this->stack_out->push(new MarkdownOpCode($opcode_o));
			else $this->stack_out->push(new MarkdownOpCode($opcode_c));
		}
	}
	