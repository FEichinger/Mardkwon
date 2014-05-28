<?php
	require_once("MarkdownParser.class.php");
	
	$string = "#This works!#\n... *Hopefully!*";
	if(isset($_POST["string"])) $string = $_POST["string"];
	
	$parser = new MarkdownParser();
?>
<form action="" method="POST">
	<textarea style="width: 50%; height: 20em;" name="string"><?php echo $string; ?></textarea><br>
	<input type="submit" />
</form>

<div style="background-color: #ccc; padding: 0.5em;">
<?php echo $parser->to_html($string); ?>
</div>
