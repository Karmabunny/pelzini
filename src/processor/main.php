<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of Pelzini.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
* This is the main processor engine. It does all of the grunt-work of the processor
*
* @package Processor
* @author Josh
* @since 0.1
**/




require_once 'functions.php';
require_once 'constants.php';
require_once 'misc_classes.php';
require_once 'load_config.php';

chdir(dirname(__FILE__));

output_status ('This is the Pelzini processor, Pelzini version ' . DOCU_VERSION);
output_status ('Pelzini is Copyright 2008 Josh Heidenreich, licenced under GPL 3');
output_status ('For more information, see <a href="http://docu.sourceforge.net/">http://docu.sourceforge.net/</a>');

// Initalise each parser
output_status ('');
$parsers = array();
$parsers['php'] = new PhpParser();
output_status("Initalised the PHP parser.");

//$parsers['js'] = new JavascriptParser();
//output_status("Initalised the Javascript parser.");

//$parsers['c'] = new CParser();
//output_status("Initalised the (expermiental) C parser.");

$parser_model = array();


// Determine the file names
output_status ('');
output_status ("Getting filenames for parsing.");
$file_names = get_filenames ($dpgBaseDirectory, '');
output_status ("Found " . count($file_names) . " files.");


// Process each file using its parser to build a code tree.
output_status ('');
output_status ('Processing files.');
$success = 0;
$failure = 0;
foreach ($file_names as $file) {
  $ext = array_pop(explode ('.', $file));
  
  if (isset($parsers[$ext])) {
    output_status ("Processing file {$file}");
    $result = $parsers[$ext]->parseFile ($file);
    
    if ($result != null) {
      $parser_model[] = $result;
      $success++;
    } else {
      output_status ("ERROR: Processing of file {$file} failed!");
      $failure++;
    }
  }
}


// Give a status output for the parsed files
$total = $success + $failure;
$noop = $total - count($file_names);
output_status ('');
output_status ("Processed {$total} file(s):");
output_status ("  {$success} file(s) were parsed successfully");
output_status ("  {$failure} file(s) failed to be parsed");


// Does processing of Javadoc tags
// Ths should be hidden away somewhere, but meh
foreach ($parser_model as $item) {
  if ($item instanceof ParserFile) {
    $item->treeWalk ('process_javadoc_tags');
  }
}


if (isset($dpgProjectDocumentsDirectory)) {
  // Determine the file names for project documents
  output_status ('');
  output_status ("Getting filenames for project documents.");
  $file_names = get_filenames ($dpgProjectDocumentsDirectory, '');
  output_status ("Found " . count($file_names) . " files.");
  
  
  // Process each document, and add a ParserDocument
  output_status ('');
  output_status ('Processing files.');
  $success = 0;
  $failure = 0;
  foreach ($file_names as $file) {
    $file_parts = explode ('.', basename ($file));
    $ext = array_pop($file_parts);
    if ($ext != 'txt') continue;
    
    $content = file_get_contents ($dpgProjectDocumentsDirectory . $file);
    if ($content == '') continue;
    
    $doc = new ParserDocument ();
    $doc->name = implode ('.', $file_parts);
    $doc->description = htmlify_text ($content);
    $parser_model[] = $doc;
  }
}


// Transform the data model
output_status ('');
foreach ($dpgTransformers as $transformer) {
  output_status ('Running ' . get_class($transformer));
  
  $result = $transformer->transform($parser_model);
  
  if ($result) {
    output_status ('Processed transformer ' . get_class($transformer) . ' successfully');
    $parser_model = $result;
  }
}


// Output the generated tree to the specified outputters
output_status ('');
foreach ($dpgOutputters as $outputter) {
  $outputter->check_layout('database.layout');
  
  output_status ('Running ' . get_class($outputter));
  
  $result = $outputter->output($parser_model);
  
  if ($result) {
    output_status ('Processed outputter ' . get_class($outputter) . ' successfully');
  }
}
?>
