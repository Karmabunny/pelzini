<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of docu.

Docu is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Docu is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with docu.  If not, see <http://www.gnu.org/licenses/>.
*/
?>


<style>
div {padding: 0.5em; margin: 0.5em;}
</style>

<?php
function __autoload ($class) {
	$filename = preg_replace('/([A-Z])/', '_$1', $class);
	$filename = substr($filename, 1) . '.php';
	require_once strtolower($filename);
}

ini_set ('memory_limit', '64M');

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
		$result = $parsers[$ext]->Tokenise ($file);
		if ($result != null) {
		  $file_objects[] = $result;
    } else {
      output_status ("Processing of file {$file} failed!");
    }
	}
}
output_status ("Processing complete");

//foreach ($file_objects as $a) $a->dump();

// Save to the db
$outputter = new MysqlOutputter('josh', 'password', 'localhost', 'docu');
$outputter->output($file_objects);
output_status ("Saved to database");

?>
