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

/**
* Shows the source of a specific file
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @see ParserFile
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


// Get the details of this file
$q = "SELECT name, description, source FROM files WHERE {$where} LIMIT 1";
$res = db_query ($q);
$row = db_fetch_assoc ($res);
echo "<h2>{$row['name']}</h2>";
echo $row['description'];

$source = trim($row['source']);
$source = explode("\n", $source);

$num = count($source);
$cols = strlen($num);


echo "<table><tr>";

echo '<td><pre>';
foreach ($source as $num => $line) {
  echo str_pad(($num + 1), $cols, ' ', STR_PAD_LEFT) . "\n";
  $lines .= htmlspecialchars ($line) . "\n";
}
echo '</pre></td>';

echo '<td><pre class="source">', $lines, '</pre></td>';

echo '</tr></table>';


require_once 'foot.php';
?>

