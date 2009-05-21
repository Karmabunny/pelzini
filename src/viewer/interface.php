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
* Shows information about a specific interface
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @see ParserInterface
* @tag i18n-needed
**/

require_once 'functions.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
  $name = trim($_GET['name']);
  if ($name == '') {
    fatal ("<p>Invalid interface name!</p>");
  }
  $name = db_escape ($name);
  $where = "interfaces.name LIKE '{$name}'";
} else {
  $where = "interfaces.id = {$id}";
}


// Get the details of this class
$q = "SELECT interfaces.id, interfaces.name, interfaces.description, files.name AS filename,
  interfaces.sinceid
  FROM interfaces
  INNER JOIN files ON interfaces.fileid = files.id
  WHERE {$where} LIMIT 1";
$res = db_query ($q);
$interface = db_fetch_assoc ($res);

$skin['page_name'] = "{$interface['name']} interface";
require_once 'head.php';


echo "<h2><span class=\"unimportant\">interface</span> <i>{$interface['name']}</i></h2>";

$filename_clean = htmlentities(urlencode($interface['filename']));
echo "<p>File: <a href=\"file.php?name={$filename_clean}\">" . htmlentities($interface['filename']) . "</a></p>\n";
echo process_inline($interface['description']);

if ($interface['sinceid']) echo '<p>Available since: ', get_since_version($function['sinceid']), '</p>';


show_authors ($interface['id'], LINK_TYPE_INTERFACE);
show_tables ($interface['id'], LINK_TYPE_INTERFACE);


// Show implementors
$name = db_quote($interface['name']);
$q = "SELECT classes.id, classes.name
  FROM classes
  INNER JOIN class_implements ON class_implements.classid = classes.id
  WHERE class_implements.name = {$name}";
$res = db_query ($q);
if (db_num_rows($res) > 0) {
  echo "<h3>Implemented by</h3>";
  echo "<ul>";
  while ($row = db_fetch_assoc ($res)) {
    echo "<li>", get_object_link($row['name']);
  }
  echo "</ul>";
}


// Show functions
$q = "SELECT id, name, description, arguments FROM functions WHERE interfaceid = {$interface['id']}";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  while ($row = db_fetch_assoc ($res)) {
    if ($row['description'] == null) {
      $row['description'] = '<em>This function does not have a description</em>';
    }
    
    // display
    echo "<h3>{$row['visibility']} <a href=\"function.php?id={$row['id']}\">{$row['name']}</a>";
    if ($row['classname'] != $class['name']) {
      echo " <small>(from <a href=\"class.php?name={$row['classname']}\">{$row['classname']}</a>)</small>";
    }
    echo "</h3>";
    
    show_function_usage ($row['id']);
    echo '<br>';
    echo process_inline($row['description']);
  }
}


show_tags ($interface['id'], LINK_TYPE_INTERFACE);
show_see_also ($interface['id'], LINK_TYPE_INTERFACE);


require_once 'foot.php';
?>

