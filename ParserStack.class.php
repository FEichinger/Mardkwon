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
			$i = count($this->stack)-1;
			if(isset($this->stack[$i]) && $this->stack[$i]->get_opcode() == "-1") $this->stack[$i]->append_char($item->translate());
			else array_push($this->stack, $item);
		}
		
		public function cleanup() {
			if(!count($this->stack)) return;
			
			$newstack = array();
			
			for($i = 0; $i < count($this->stack); $i++) {
				$current = $this->stack[$i];
				
				if(isset($this->stack[$i+1])) {
					$next = $this->stack[$i+1];
				}
				else {
					$newstack[] = $current;
					break;
				}
				
				if($current->get_opcode()+1 == $next->get_opcode()) $i++;
				else $newstack[] = $current;
			}
			$this->stack = $newstack;
		}
		
		public function clear() {
			$this->stack = array();
		}
	}