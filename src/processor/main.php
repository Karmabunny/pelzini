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
output_status ('');


// Initalise each parser
$parsers = array();
$parsers['php'] = new PhpTokeniser();
output_status("Initalised PHP parser.");

// Determine the file names
output_status ("Getting filenames for parsing.");
$file_names = get_filenames ('/');
output_status ("Retrieved " . count($file_names) . " files.");

output_status ('');

// Process each file using its parser to build a code tree.
$parsed_files = array();
foreach ($file_names as $file) {
  $ext = array_pop(explode ('.', $file));
  
  if (isset($parsers[$ext])) {
    output_status ("Processing file {$file}");
    $result = $parsers[$ext]->Tokenise ($file);
    
    if ($result != null) {
      $parsed_files[] = $result;
    } else {
      output_status ("Processing of file {$file} failed!");
    }
  }
}
output_status ("Processing complete.");

output_status ('');

// Output the generated tree to the specified outputters
foreach ($dpgOutputters as $outputter) {
  switch ($outputter) {
    case OUTPUTTER_MYSQL:
      $outputter = new MysqlOutputter(
        $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_username'],
        $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_password'],
        $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_server'],
        $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_name']
      );
      
      $result = $outputter->output($parsed_files);
      
      if ($result) {
        output_status ("Saved to MySQL database succesfully.");
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
      
      $result = $outputter->output($parsed_files);
      
      if ($result) {
        output_status ("Saved to PostgreSQL database succesfully.");
      } else {
        output_status ("Saving to PostgreSQL database failed.");
      }
      break;
      
      
    case OUTPUTTER_DEBUG:
      $outputter = new DebugOutputter();
      $outputter->output($parsed_files);
      break;
      
      
  }
}
?>
