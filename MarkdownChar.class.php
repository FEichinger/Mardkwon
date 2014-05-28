<?php
	class MarkdownChar implements MarkdownStackItem {
		private $char;
		
		public function __construct($c) {
			$this->char = $c;
		}
		
		public function get_opcode() {
			return -1;
		}
		
		public function translate() {
			return $this->char;
		}
	}