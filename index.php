<?php
	require_once("MarkdownParser.class.php");

	$string = "**This is* a valid Markdown __string__\n\nWith multiple lines\n\n- And even\n-a list!";
	$parser = new MarkdownParser();
	echo $parser->to_html($string);
