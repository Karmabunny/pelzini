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


chdir(dirname(__FILE__));

require_once 'functions.php';
require_once 'constants.php';
require_once 'misc_classes.php';
require_once 'load_config.php';


output_status ('This is the docu processor, docu version ' . DOCU_VERSION);
output_status ('Docu is Copyright 2008 Josh Heidenreich, licenced under GPL 3');
output_status ('For more information, see <a href="http://docu.sourceforge.net/">http://docu.sourceforge.net/</a>');

// Initalise each parser
output_status ('');
$parsers = array();
$parsers['php'] = new PhpParser();
output_status("Initalised PHP parser.");
$parsers['js'] = new JavascriptParser();
output_status("Initalised Javascript parser.");


// Determine the file names
output_status ('');
output_status ("Getting filenames for parsing.");
$file_names = get_filenames ('');
output_status ("Found " . count($file_names) . " files.");


// Process each file using its parser to build a code tree.
output_status ('');
output_status ('Processing files.');
$parser_model = array();
$success = 0;
$failure = 0;
foreach ($file_names as $file) {
  $ext = array_pop(explode ('.', $file));
  
  if (isset($parsers[$ext])) {
    //output_status ("Processing file {$file}");
    $result = $parsers[$ext]->parseFile ($file);
    
    if ($result != null) {
      $parser_model[] = $result;
      $success++;
    } else {
      output_status ("Processing of file {$file} failed!");
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
    $item->treeWalk ('processJavadocTags');
  }
}

function processJavadocTags(ParserItem $parser_item) {
  //$parser_item->cascadeTags($item);
  $parser_item->processTags();
}


// Transform the data model
output_status ('');
foreach ($dpgTransformers as $transformer) {
  switch ($transformer) {
    case TRANSFORMER_QUALITY_CHECK:
      $transformer = new QualityCheckTransformer();
      
      $result = $transformer->transform($parser_model);
      
      if ($result) {
        output_status ("Generated low quality documentation report succesfully.");
        $parser_model = $result;
      } else {
        output_status ("Generating of low quality documentation report failed.");
      }
      break;
      
  }
}


// Output the generated tree to the specified outputters
output_status ('');
foreach ($dpgOutputters as $outputter) {
  switch ($outputter) {
    case OUTPUTTER_MYSQL:
      $outputter = new MysqlOutputter(
        $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_username'],
        $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_password'],
        $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_server'],
        $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_name']
      );
      
      $result = $outputter->output($parser_model);
      
      if ($result) {
        output_status ("Saved to MySQL database succesfully.");
        $uses_viewer = true;
      } else {
        output_status ("Saving to MySQL database failed.");
      }
      break;
      
      
    case OUTPUTTER_PGSQL:
      $outputter = new PostgresqlOutputter(
        $dpgOutputterSettings[OUTPUTTER_PGSQL]['database_username'],
        $dpgOutputterSettings[OUTPUTTER_PGSQL]['database_password'],
        $dpgOutputterSettings[OUTPUTTER_PGSQL]['database_server'],
        $dpgOutputterSettings[OUTPUTTER_PGSQL]['database_name']
      );
      
      $result = $outputter->output($parser_model);
      
      if ($result) {
        output_status ("Saved to PostgreSQL database succesfully.");
        $uses_viewer = true;
      } else {
        output_status ("Saving to PostgreSQL database failed.");
      }
      break;
      
      
    case OUTPUTTER_SQLITE:
      $outputter = new SqliteOutputter(
        $dpgOutputterSettings[OUTPUTTER_SQLITE]['filename']
      );
      
      $result = $outputter->output($parser_model);
      
      if ($result) {
        output_status ("Saved to SQLite database succesfully.");
        $uses_viewer = true;
      } else {
        output_status ("Saving to SQLite database failed.");
      }
      break;
      
      
    case OUTPUTTER_DEBUG:
      $outputter = new DebugOutputter();
      $outputter->output($parser_model);
      break;
      
      
  }
}


// If any of the outputters use the viewer, output a small message to that effect.
if ($uses_viewer) {
  output_status ('');
  output_status ("<a href=\"../viewer\">View the generated documentation</a>");
}
?>
