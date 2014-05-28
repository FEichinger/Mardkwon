<?php
	require_once("StateMachine.class.php");
	require_once("ParserStack.class.php");
	
	class MarkdownParser {
		private $state_machine;
		
		private $stack_out;
		
		private function normalize($string) {
			$string = str_replace("\n\r", "\n", $string);
			$string = str_replace("\r\n", "\n", $string);
			$string = str_replace("\r", "\n", $string);
			$string = trim($string);
			return "\n\n".$string."\n\n";
		}
		
		public function to_html($string) {
			$string = $this->normalize($string);
			
			$this->stack_out = new ParserStack();
			$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::PARAGRAPH_START));
			$this->state_machine = new StateMachine($this->stack_out);
			foreach(str_split($string) as $c) $this->state_machine->process_character($c);
			$this->stack_out->push(new MarkdownOpCode(MarkdownOpCode::PARAGRAPH_END));
			$this->stack_out->cleanup();
			
			$out = "";
			while(!is_null($item = $this->stack_out->pop())) $out .= $item->translate();
			return $out;
		}
	}
	