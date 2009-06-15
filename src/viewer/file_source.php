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
* Shows the source of a specific file
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @see ParserFile
* @tag i18n-needed
**/

require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
  $name = trim($_GET['name']);
  if ($name == '') {
    fatal ("<p>Invalid filename!</p>");
  }
  $name = db_escape ($name);
  $where = "name LIKE '{$name}'";
} else {
  $where = "id = {$id}";
}

// Line highlighting
if ($_GET['highlight']) {
  $parts = explode ('-', $_GET['highlight']);
  
  if (count($parts) == 1) {
    $highlight_begin = $parts[0];
    $highlight_end = $parts[0];
    
  } else if (count($parts) == 2) {
    $highlight_begin = $parts[0];
    $highlight_end = $parts[1];
  }
}

// Keyword highlighting
if ($_GET['keyword']) {
  $keyword_search = htmlspecialchars($_GET['keyword']);
  $keyword_search = '/(' . preg_quote ($keyword_search, '/'). ')/i';
}



// Get the details of this file
$q = "SELECT name, description, source FROM files WHERE {$where} LIMIT 1";
$res = db_query ($q);
$row = db_fetch_assoc ($res);
echo "<h2>{$row['name']}</h2>";
echo process_inline($row['description']);

$source = trim($row['source']);
$source = explode("\n", "\n" . $source);
unset ($source[0]);

$num = count($source);
$cols = strlen($num);


echo "<table><tr>";

echo '<td><pre>';
foreach ($source as $num => $line) {
  echo "<a name=\"line{$num}\"></a>", str_pad($num, $cols, ' ', STR_PAD_LEFT) . "\n";
  
  if ($num == $highlight_begin) {
    $lines .= '<span class="highlight">';
  }
  
  $line = htmlspecialchars ($line);
  if ($keyword_search) {
    $line = preg_replace($keyword_search, "<span class=\"highlight\">\$1</span>", $line);
  }
  
  $lines .= "{$line}\n";
  
  if ($num == $highlight_end) {
    $lines .= '</span>';
  }
}
echo '</pre></td>';

echo '<td><pre class="source">', $lines, '</pre></td>';

echo '</tr></table>';


require_once 'foot.php';
?>

