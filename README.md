Mardkwon
========

Markdwon is a PHP Markdown Parser

## Installation

Simply include the main parser class:

    require_once("MarkdownParser.class.php");

Then create a new MarkdownParser object, and let it parse the Markdown into HTML:

    $string = "**This is* a valid Markdown __string__";
    $parser = new MarkdownParser();
    $parser->to_html($string);

Supports `*` & `_` bold/italic, `#` headlines, and `-` lists.
