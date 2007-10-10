<style>
div {padding: 0.5em; margin: 0.5em;}
</style>

<?php
function __autoload ($class) {
	$filename = preg_replace('/([A-Z])/', '_$1', $class);
	$filename = substr($filename, 1) . '.php';
	require_once strtolower($filename);
}

require_once 'functions.php';
require_once 'config.php';

// set up some space
$file_objects = array();
$parsers = array ();

// load the file names
$file_names = get_filenames ('../test/');

// initalise each parser
$parsers['php'] = call_user_func (array('PhpTokeniser', 'CreateInstance'));

// process each file usign its parser
foreach ($file_names as $file) {
	$bits = explode ('.', $file);
	$ext = $bits[count($bits) - 1];
	
	if (isset($parsers[$ext])) {
		$file_objects[] = $parsers[$ext]->Tokenise ('../test/stuff.php');
	}
}

echo "good";
?>
