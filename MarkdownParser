<?php
	/* Main Parser */
	class MarkdownParser {
		private $bold;
		private $italic;
		private $h1;
		private $h2;
		private $h3;
		private $h4;
		private $h5;
		private $h6;
		private $paragraph;
		
		private $newline;
		private $skip;
		
		private $stack_markdown;
		private $stack_operands;
		private $stack_string;
		
		public function __construct() {
			$this->bold = false;
			$this->italic = false;
			$this->h1 = false;
			$this->h2 = false;
			$this->h3 = false;
			$this->h4 = false;
			$this->h5 = false;
			$this->h6 = false;
			$this->paragraph = false;
			
			$this->newline = true;
			$this->skip = false;
			
			$this->stack_markdown = new ParserStack();
			$this->stack_operands = new ParserStack();
			
			$this->stack_string = new ParserStack();
		}
		
		private function normalize($string) {
			$string = str_replace("\n\r", "\n", $string);
			$string = str_replace("\r\n", "\n", $string);
			$string = str_replace("\r", "\n", $string);
			$string = preg_replace("/[\n][\n]([\n]+)/", "\n\n", $string);
			return "\n\n".$string."\n";
		}
		
		private function read_next($string, &$pointer) {
			if($pointer >= strlen($string)) return null;
			return substr($string, $pointer++, 1);
		}
		
		private function flip_bold() {
			if($this->bold = !$this->bold) $opcode = MarkdownOpCode::BOLD_ON;
			else $opcode = MarkdownOpCode::BOLD_OFF;
			$this->stack_markdown->push(new MarkdownOpCode($opcode));
			$this->newline = false;
		}
		
		private function flip_italic() {
			if($this->italic = !$this->italic) $opcode = MarkdownOpCode::ITALIC_ON;
			else $opcode = MarkdownOpCode::ITALIC_OFF;
			$this->stack_markdown->push(new MarkdownOpCode($opcode));
			$this->newline = false;
		}
		
		private function flip_h1() {
			if((false
				|| (!$this->newline && !$this->h1)
				|| $this->h2
				|| $this->h3
				|| $this->h4
				|| $this->h5
				|| $this->h6)) {
				$this->stack_markdown->push(new MarkdownString("#"));
				return;
			}
			
			$this->end_paragraph();
			if($this->h1 = !$this->h1) $opcode = MarkdownOpCode::H1_ON;
			else $opcode = MarkdownOpCode::H1_OFF;
			$this->stack_markdown->push(new MarkdownOpCode($opcode));
			$this->newline = false;
		}
		
		private function flip_h2() {
			if((false
				|| (!$this->newline && !$this->h2)
				|| $this->h1
				|| $this->h3
				|| $this->h4
				|| $this->h5
				|| $this->h6)) {
				$this->stack_markdown->push(new MarkdownString("##"));
				return;
			}
			
			$this->end_paragraph();
			if($this->h2 = !$this->h2) $opcode = MarkdownOpCode::H2_ON;
			else $opcode = MarkdownOpCode::H2_OFF;
			$this->stack_markdown->push(new MarkdownOpCode($opcode));
			$this->newline = false;
		}
		
		private function flip_h3() {
			if((false
				|| (!$this->newline && !$this->h3)
				|| $this->h1
				|| $this->h2
				|| $this->h4
				|| $this->h5
				|| $this->h6)) {
				$this->stack_markdown->push(new MarkdownString("###"));
				return;
			}
			
			$this->end_paragraph();
			if($this->h3 = !$this->h3) $opcode = MarkdownOpCode::H3_ON;
			else $opcode = MarkdownOpCode::H3_OFF;
			$this->stack_markdown->push(new MarkdownOpCode($opcode));
			$this->newline = false;
		}
		
		private function flip_h4() {
			if((false
				|| (!$this->newline && !$this->h4)
				|| $this->h1
				|| $this->h2
				|| $this->h3
				|| $this->h5
				|| $this->h6)) {
				$this->stack_markdown->push(new MarkdownString("####"));
				return;
			}
			
			$this->end_paragraph();
			if($this->h4 = !$this->h4) $opcode = MarkdownOpCode::H4_ON;
			else $opcode = MarkdownOpCode::H4_OFF;
			$this->stack_markdown->push(new MarkdownOpCode($opcode));
			$this->newline = false;
		}
		
		private function flip_h5() {
			if((false
				|| (!$this->newline && !$this->h5)
				|| $this->h1
				|| $this->h2
				|| $this->h3
				|| $this->h4
				|| $this->h6)) {
				$this->stack_markdown->push(new MarkdownString("#####"));
				return;
			}
			
			$this->end_paragraph();
			if($this->h5 = !$this->h5) $opcode = MarkdownOpCode::H5_ON;
			else $opcode = MarkdownOpCode::H5_OFF;
			$this->stack_markdown->push(new MarkdownOpCode($opcode));
			$this->newline = false;
		}
		
		private function flip_h6() {
			if((false
				|| (!$this->newline && !$this->h6)
				|| $this->h1
				|| $this->h2
				|| $this->h3
				|| $this->h4
				|| $this->h5)) {
				$this->stack_markdown->push(new MarkdownString("######"));
				return;
			}
			
			$this->end_paragraph();
			if($this->h6 = !$this->h6) $opcode = MarkdownOpCode::H6_ON;
			else $opcode = MarkdownOpCode::H6_OFF;
			$this->stack_markdown->push(new MarkdownOpCode($opcode));
			$this->newline = false;
		}
		
		private function start_paragraph() {
			$this->paragraph = true;
			$this->stack_markdown->push(new MarkdownOpCode(MarkdownOpCode::PARAGRAPH_START));
		}
		
		private function end_paragraph() {
			if($this->paragraph) {
				$this->paragraph = false;
				$this->stack_markdown->push(new MarkdownOpCode(MarkdownOpCode::PARAGRAPH_END));
			}
		}
		
		private function event_newparagraph() {
			$this->newline = true;
			$this->cleanup(true);
			if($this->paragraph) $this->end_paragraph();
			$this->start_paragraph();
		}
		
		private function event_newline() {
			$this->newline = true;
			$this->cleanup(true);
			if($this->paragraph) $this->stack_markdown->push(new MarkdownOpCode(MarkdownOpCode::LINEBREAK));
			else $this->start_paragraph();
		}
		
		private function cleanup($inline_only = false) {
			if($this->bold) $this->flip_bold();
			if($this->italic) $this->flip_italic();
			if($this->h6) $this->flip_h6();
			if($this->h5) $this->flip_h5();
			if($this->h4) $this->flip_h4();
			if($this->h3) $this->flip_h3();
			if($this->h2) $this->flip_h2();
			if($this->h1) $this->flip_h1();
			
			if(!$inline_only) {
				//>
			}
		}
		
		private function process_stack_string() {
			$s = "";
			while(!is_null($p = $this->stack_string->pop())) $s .= $p;
			if(strlen($s)) {
				$this->stack_markdown->push(new MarkdownString($s));
				$this->newline = false;
			}
			$this->stack_string->clear();
		}
		
		private function process_stack_operands() {
			$s = "";
			while(!is_null($p = $this->stack_operands->pop())) $s .= $p;
			
			$ops = array(
				"**" => "flip_bold",
				"__" => "flip_bold",
				"*" => "flip_italic",
				"_" => "flip_italic",
				"######" => "flip_h6",
				"#####" => "flip_h5",
				"####" => "flip_h4",
				"###" => "flip_h3",
				"##" => "flip_h2",
				"#" => "flip_h1",
				"\n\n" => "event_newparagraph",
				"\n" => "event_newline",
			);
			
			while(strlen($s)) {
				$ss = $s;
				foreach($ops as $pattern => $operation) {
					$count = 0;
					$l = strlen($pattern);
					$pattern = "/[".implode("][", str_split($pattern))."]/";
					$s = preg_replace($pattern, "", substr($s, 0, $l), 1, $count).substr($s, $l);
					if($count) $this->$operation();
				}
				if($ss == $s) $s = "";
			}
			
			$this->stack_operands->clear();
		}
		
		private function process_char($c) {
			switch($c) {
				case "*":
				case "_":
				case "#":
					if($this->skip) {
						if(!$this->code) {
							$this->stack_string->pop(ParserStack::FILO);
							$this->skip = false;
						}
						$this->stack_string->push($c);
						return;
					}
				case "\n":
					$this->stack_operands->push($c);
					$this->process_stack_string();
					return;
				case "\\":
					$this->skip = true;
					$this->stack_string->push($c);
					return;
				default:
					$this->stack_string->push($c);
					$this->process_stack_operands();
					return;
			}
		}
		
		public function to_html($string) {
			$string = $this->normalize($string);
			
			$pointer = 0;
			while(!is_null($c = $this->read_next($string, $pointer)))
				$this->process_char($c);
			$this->cleanup();
			
			$out = "";
			$last = $this->stack_markdown->pop();
			while(!is_null($item = $this->stack_markdown->pop())) {
				if($item->get_opcode() != -1 && ($item->get_opcode()-1 == $last->get_opcode())) {
					$last = $this->stack_markdown->pop();
				}
				else {
					$out .= $last->translate();
					$last = $item;
				}
			}
			if(!is_null($last)) {
				$out .= $last->translate();
			}
			return $out;
		}
	}
