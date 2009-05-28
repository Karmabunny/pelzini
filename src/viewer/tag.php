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
* Shows information about a specific tag
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.3
* @tag i18n-done
**/

require_once 'functions.php';

$_GET['name'] = trim($_GET['name']);
if ($_GET['name'] == '') {
  require_once 'head.php';
  echo "Invalid tag specified";
  require_once 'foot.php';
  exit;
}

$name_sql = db_quote ($_GET['name']);

$skin['page_name'] = str(STR_TAG_PAGE_TITLE, 'name', $_GET['name']);
require_once 'head.php';

echo '<h2>', str(STR_TAG_TITLE, 'name', $_GET['name']), '</h2>';



// Show files
$q = "SELECT files.id, files.name
  FROM files
  INNER JOIN item_info_tags ON item_info_tags.linktype = " . LINK_TYPE_FILE . " AND item_info_tags.linkid = files.id
  WHERE item_info_tags.name = {$name_sql}
  ORDER BY files.name";
$res = db_query ($q);
if (db_num_rows($res) > 0) {
  echo '<h3>', str(STR_FILES), '</h3>';
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    // output
    echo "<div class=\"{$class}\">";
    echo "<p><strong><a href=\"file.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo '</div>';
    
    $file_ids[] = $row['id'];
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show classes
$q = "SELECT classes.id, classes.name
  FROM classes
  INNER JOIN item_info_tags ON item_info_tags.linktype = " . LINK_TYPE_CLASS . " AND item_info_tags.linkid = classes.id
  WHERE item_info_tags.name = {$name_sql}
  ORDER BY classes.name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="classes"></a>';
  echo '<h3>', str(STR_CLASSES), '</h3>';
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<p><strong><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo '</div>';
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show functions
$q = "SELECT functions.id, functions.name, classes.name AS classname,
      interfaces.name AS interfacename
  FROM functions
  INNER JOIN item_info_tags ON item_info_tags.linktype = " . LINK_TYPE_FUNCTION . " AND item_info_tags.linkid = functions.id
  LEFT JOIN classes ON functions.classid = classes.id
  LEFT JOIN interfaces ON functions.interfaceid = interfaces.id
  WHERE item_info_tags.name = {$name_sql}
  ORDER BY interfacename, classname, functions.name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="functions"></a>';
  echo '<h3>', str(STR_FUNCTIONS), '</h3>';
  
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
    
    echo "<p><strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong>";
    if ($row['classname']) echo str(STR_FROM_CLASS, 'class', get_object_link($row['classname']));
    if ($row['interfacename']) echo str(STR_FROM_INTERFACE, 'interface', get_object_link($row['interfacename']));
    echo "</p>";
    echo "</div>";
    
    $alt = ! $alt;
  }
  echo '</div>';
}


require_once 'foot.php';
?>
