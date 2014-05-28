Mardkwon
========

Markdwon is a PHP Markdown Parser

## Installation

Simply include all the necessary files:

    require_once("MarkdownParser.class.php");
    require_once("ParserStack.class.php");
    require_once("MarkdownStackItems.class.php");

Then create a new MarkdownParser object, and let it parse the Markdown into HTML:

    $string = "**This is* a valid Markdown __string__";
    $parser = new MarkdownParser();
    $parser->to_html($string);

The actual parsing is a bit wonky and currently only supports `[#]{1..6}`, `[*]*`, and `[_]*`, but it works for what we need.
