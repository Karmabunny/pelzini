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

$base_dir = '../test';

// initalise each parser
$parsers['php'] = call_user_func (array('PhpTokeniser', 'CreateInstance'));
output_status ("Initalised parser PhpTokeniser");

// load the file names
output_status ("Getting filenames");
$file_names = get_filenames ('/');
output_status ("Got " . count($file_names) . " files");

// process each file usign its parser
foreach ($file_names as $file) {
	$bits = explode ('.', $file);
	$ext = $bits[count($bits) - 1];
	
	if (isset($parsers[$ext])) {
		output_status ("Processing file {$file}");
		$file_objects[] = $parsers[$ext]->Tokenise ($file);
	}
}
output_status ("Processing complete");

//foreach ($file_objects as $a) $a->dump();

// Save to the db
$outputter = new MysqlOutputter('josh', 'password', 'localhost', 'docu');
$outputter->output($file_objects);
output_status ("Saved to database");

?>
