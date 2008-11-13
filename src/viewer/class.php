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
    fatal ("<p>Invalid class name!</p>");
  }
  $name = db_escape ($name);
  $where = "Classes.Name LIKE '{$name}'";
} else {
  $where = "Classes.ID = {$id}";
}


// Get the details of this class
$q = "SELECT Classes.ID, Classes.Name, Classes.Description, Classes.Extends, Files.Name AS Filename,
  Classes.Final, Classes.Abstract, Classes.SinceVersion
  FROM Classes
  INNER JOIN Files ON Classes.FileID = Files.ID
  WHERE {$where}
  LIMIT 1";
$res = db_query ($q);

if (db_num_rows ($res) == 0) {
  echo "<p>Invalid class specified.</p>";
  exit;
}

$class = db_fetch_assoc ($res);
$filename_clean = htmlentities(urlencode($class['filename']));

echo "<h2>{$class['name']}</h2>";
echo "<p>File: <a href=\"file.php?name={$filename_clean}\">" . htmlentities($class['filename']) . "</a></p>\n";
echo $class['description'];

if ($class['abstract'] == 1) echo '<p>Abstract</p>';

if ($class['extends'] != null) {
  $class['extends'] = htmlspecialchars($class['extends']);
  echo "<p>Extends <a href=\"class.php?name={$class['extends']}\">{$class['extends']}</a>";
  
  if ($_GET['complete'] == 1) {
    echo " | <a href=\"class.php?id={$class['id']}\">Hide inherited members</a></p>";
  } else {
    echo " | <a href=\"class.php?id={$class['id']}&complete=1\">Show inherited members</a></p>";
  }
}

if ($class['sinceversion']) echo '<p>Available since: ', htmlspecialchars ($class['sinceversion']), '</p>';


$functions = array();
$variables = array();
$name = $class['name'];
$class_names = array();

if ($_GET['complete'] == 1) {
  do {
    $class_names[] = $name;
    
    $result = load_class($name);
    if ($result == null) break;
    
    list ($funcs, $vars, $parent) = $result;
    
    $functions = array_merge($funcs, $functions);
    $variables = array_merge($vars, $variables);
    
    $name = $parent;
  } while ($name != null);
  
} else {
  list ($functions, $variables) = load_class($name);
}

ksort($functions);
ksort($variables);


show_authors ($class['id'], LINK_TYPE_CLASS);


if ($_GET['complete'] == 1 and count ($class_names) > 0) {
  echo "<h3>Class structure</h3>";
  echo "<ul>";
  foreach ($class_names as $index => $name) {
    if ($index == 0) {
      echo '<li>', $name;
      if ($class['final'] == 1) echo ' <small>(Final)</small>';
      echo '</li>';
      
    } else {
      echo "<li><a href=\"class.php?name={$name}\">{$name}</a></li>";
    }
  }
  echo "</ul>";
}

// Show variables
if (count($variables) > 0) {
  echo "<h3>Variables</h3>";
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  foreach ($variables as $row) {
    // encode for output
    $row['name'] = htmlspecialchars($row['name']);
    if ($row['description'] == null) $row['description'] = '&nbsp;';
    
    if ($row['static']) $row['name'] .= ' <small>(static)</small>';
    
    // display
    echo "<tr>";
    echo "<td><code>{$row['name']}</code></td>";
    echo "<td>{$row['description']}</td>";
    echo "</tr>\n";
  }
  echo "</table>\n";
}


// Show functions
if (count($functions) > 0) {
  foreach ($functions as $row) {
    // encode for output
    if ($row['description'] == null) {
      $row['description'] = '<em>This function does not have a description</em>';
    }
    
    // display
    echo "<h3>{$row['visibility']} <a href=\"function.php?id={$row['id']}\">{$row['name']}</a>";
    if ($row['classname'] != $class['name']) {
      echo " <small>(from <a href=\"class.php?name={$row['classname']}\">{$row['classname']}</a>)</small>";
    }
    echo "</h3>";
    
    echo $row['description'];
    
    // show return value
    if ($row['returntype'] != null) {
      $link = get_object_link($row['returntype']);
      echo "<p>Returns <b>{$link}</b>";
      
      if ($row['returndescription'] != null) {
        echo ': ', $row['returndescription'];
      }
      echo '</p>';
    }
    
    // Show parameters
    $q = "SELECT Name, Type, Description FROM Arguments WHERE FunctionID = {$row['id']}";
    $res = db_query($q);
    if (db_num_rows($res) > 0) {
      echo "<ul>\n";
      while ($param = db_fetch_assoc ($res)) {
        if ($param['description'] != null) {
          $param['description'] = ': ' . str_replace("\n", '<br>', $param['description']);
        }
        
        $link = get_object_link($param['type']);
        echo "<li>{$link} <b>{$param['name']}</b>{$param['description']}</li>";
      }
      echo "</ul>\n";
    }
  }
}


require_once 'foot.php';


/**
* Returns an array of
* [0] => functions
* [1] => variables
* [2] => parent class
**/
function load_class($name) {
  // determine parent class
  $name = db_escape ($name);
  $q = "SELECT ID, Extends FROM Classes WHERE Name LIKE '{$name}'";
  $res = db_query($q);
  if (db_num_rows ($res) == 0) {
    return null;
  }
  
  $row = db_fetch_assoc($res);
  $id = $row['id'];
  $parent = $row['extends'];
  
  // determine functions
  $functions = array();
  $q = "SELECT *, '{$name}' AS ClassName FROM Functions WHERE ClassID = {$id}";
  $res = db_query($q);
  while ($row = db_fetch_assoc($res)) {
    $functions[$row['name']] = $row;
  }
  
  // determine variables
  $variables = array();
  $q = "SELECT *, '{$name}' AS ClassName FROM Variables WHERE ClassID = {$id}";
  $res = db_query($q);
  while ($row = db_fetch_assoc($res)) {
    $variables[$row['name']] = $row;
  }
  
  return array($functions, $variables, $parent);
}
?>
