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
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
**/

require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
  $name = trim($_GET['name']);
  if ($name == '') {
    fatal ("<p>Invalid filename!</p>");
  }
  $name = mysql_escape ($name);
  $where = "Name LIKE '{$name}'";
} else {
  $where = "ID = {$id}";
}


// Get the details of this file
$q = "SELECT Name, Description, Source FROM Files WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
echo $row['Description'];

$source = trim($row['Source']);
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

