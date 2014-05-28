<?php
	class ParserStack {
		private $stack;
		
		public function __construct() {
			$this->stack = array();
		}
		
		public function pop() {
			return array_shift($this->stack);
		}
		
		public function push(MarkdownStackItem $item) {
			array_push($this->stack, $item);
		}
		
		public function cleanup() {
			if(!count($this->stack)) return;
			
			$newstack = array();
			
			$last = $this->stack[0];
			for($i = 1; $i < count($this->stack); $i++) {
				$next = $this->stack[$i];
				if($last->get_opcode() != $next->get_opcode()-1) {
					$newstack[] = $last;
				}
				else {
					$i++;
					$next = (isset($this->stack[$i])) ? ($this->stack[$i]) : null;
				}
				$last = $next;
			}
			$newstack[] = $last;
			$this->stack = $newstack;
		}
		
		public function clear() {
			$this->stack = array();
		}
	}