<?php
	/* Generic Stack class */
	class ParserStack {
		private $stack;
		
		const FIFO = 0x0001;
		const FILO = 0x0002;
		
		public function __construct() {
			$this->stack = array();
		}
		
		public function pop($method = self::FIFO) {
			if($method == self::FIFO) {
				return array_shift($this->stack);
			}
			else {
				return array_pop($this->stack);
			}
		}
		
		public function push($item) {
			array_push($this->stack, $item);
		}
		
		public function clear() {
			$this->stack = array();
		}
	}
