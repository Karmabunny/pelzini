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
* Shows information about a specific function
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @see ParserFunciton
**/

require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
  $name = trim($_GET['name']);
  if ($name == '') {
    fatal ("<p>Invalid function name!</p>");
  }
  $name = db_escape ($name);
  $where = "Functions.name LIKE '{$name}'";
} else {
  $where = "functions.id = {$id}";
}


// Get the details of this function
$q = "SELECT functions.id, functions.name, functions.description, files.name AS filename, functions.classid,
  classes.name AS class, functions.static, functions.final, functions.sinceid,
  functions.returntype, functions.returndescription
  FROM functions
  INNER JOIN files ON functions.fileid = files.id
  LEFT JOIN classes ON functions.classid = classes.id
  WHERE {$where} LIMIT 1";
$res = db_query ($q);
$function = db_fetch_assoc ($res);

echo "<h2><span class=\"unimportant\">function</span> <i>{$function['name']}</i>()</h2>";

echo process_inline($function['description']);


echo "<ul>";

$filename_url = 'file.php?name=' . urlencode($function['filename']);
echo '<li>File: <a href="', htmlspecialchars($filename_url), '">';
echo htmlspecialchars($function['filename']), '</a></li>';

if ($function['classid']) {
  echo "<li>Class: <a href=\"class.php?id={$function['classid']}\">{$function['class']}</a>";
  
  if ($function['static']) echo ', Static method';
  if ($function['final']) echo ', Final method';
  
  echo '</li>';
}

if ($function['sinceid']) {
  echo '<li>Available since: ', get_since_version($function['sinceid']), '</li>';
}

echo "</ul>";


// Usage
echo "<h3>Usage</h3>";
show_function_usage ($function['id']);


show_authors ($function['id'], LINK_TYPE_FUNCTION);
show_tables ($function['id'], LINK_TYPE_FUNCTION);


// Show Arguments
$q = "SELECT id, name, type, defaultvalue, description FROM arguments WHERE functionid = {$function['id']}";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo "<h3>Arguments</h3>";
  
  echo "<ol class=\"spaced-list\">";
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    $row['type'] = htmlspecialchars($row['type']);
    $row['defaultvalue'] = htmlspecialchars($row['defaultvalue']);
    
    echo '<li>', get_object_link ($row['type']), " <strong>{$row['name']}</strong>";
    if ($row['defaultvalue']) echo " = {$row['defaultvalue']}";
    echo '<br>', process_inline ($row['description']);
    echo "</li>";
  }
  echo "</ol>\n";
}


// Return value
if ($function['returntype'] or $function['returndescription']) {
  $function['returntype'] = htmlspecialchars ($function['returntype']);
  
  echo "<h3>Return value</h3>";
  
  echo "<ul><li>";
  if ($function['returntype']) {
    echo get_object_link ($function['returntype']), '<br>';
  }
  
  echo process_inline ($function['returndescription']);
  echo "</li></ul>";
}


show_see_also ($function['id'], LINK_TYPE_FUNCTION);


require_once 'foot.php';
?>

