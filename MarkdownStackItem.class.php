<?php
	interface MarkdownStackItem {
		public function translate();
		public function get_opcode();
	}