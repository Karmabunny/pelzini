<?php

function get_strings($lang) {
  $strings = array();
  
  $file_lines = file ("lang/{$lang}.txt");
  $index = 0;
  foreach ($file_lines as $line) {
      $line = preg_replace ('/;(.*)$/', '', $line);
      $line = trim ($line);
      if ($line == '') continue;
      
      $parts = preg_split ('/\s+/', $line, 2);
      
      $strings[$parts[0]] = $parts[1];
  }
  
  return $strings;
}

?>
