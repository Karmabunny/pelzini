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
* @since 0.2
**/

require_once 'head.php';


$_GET['name'] = trim($_GET['name']);
if ($_GET['name'] == '') {
  echo "Invalid author specified";
  exit;
}

$name_sql = db_quote ($_GET['name']);


echo "<h2>Code written by <i>{$_GET['name']}</i></h2>";


// Show files
$q = "SELECT files.id, files.name, item_authors.description
  FROM files
  INNER JOIN item_authors ON item_authors.linktype = " . LINK_TYPE_FILE . " AND item_authors.linkid = files.id
  WHERE item_authors.name = {$name_sql}
  ORDER BY files.name";
$res = db_query ($q);
if (db_num_rows($res) > 0) {
  echo "<h3>Files</h3>";
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    // output
    echo "<div class=\"{$class}\">";
    echo "<p><i>{$row['action']}</i> <strong><a href=\"file.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo $row['description'];
    echo '</div>';
    
    $file_ids[] = $row['id'];
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show classes
$q = "SELECT classes.id, classes.name, item_authors.description
  FROM classes
  INNER JOIN item_authors ON item_authors.linktype = " . LINK_TYPE_CLASS . " AND item_authors.linkid = classes.id
  WHERE item_authors.name = {$name_sql}
  ORDER BY classes.name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="classes"></a>';
  echo "<h3>Classes</h3>";
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<p><i>{$row['action']}</i> <strong><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo $row['description'];
    echo '</div>';
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show functions
$q = "SELECT functions.id, functions.name, item_authors.description, classes.name AS classname,
      interfaces.name AS interfacename
  FROM functions
  INNER JOIN item_authors ON item_authors.linktype = " . LINK_TYPE_FUNCTION . " AND item_authors.linkid = functions.id
  LEFT JOIN classes ON functions.classid = classes.id
  LEFT JOIN interfaces ON functions.interfaceid = interfaces.id
  WHERE item_authors.name = {$name_sql}
  ORDER BY interfacename, classname, functions.name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="functions"></a>';
  echo "<h3>Functions</h3>";
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    // encode for output
    $row['name'] = htmlspecialchars($row['name']);
    $row['arguments'] = htmlspecialchars($row['arguments']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    // display the function
    echo "<div class=\"{$class}\">";
    
    echo "<p><i>{$row['action']}</i> <strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong>";
    if ($row['classname']) echo ' <small>from class ', get_object_link($row['classname']), '</small>';
    if ($row['interfacename']) echo ' <small>from interface ', get_object_link($row['interfacename']), '</small>';
    echo "</p>";
    
    echo $row['description'];
    echo "</div>";
    
    $alt = ! $alt;
  }
  echo '</div>';
}


require_once 'foot.php';
?>
