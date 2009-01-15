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
* Shows information about a specific class
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @see ParserClass
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
  $where = "classes.name LIKE '{$name}'";
} else {
  $where = "classes.id = {$id}";
}


// Get the details of this class
$q = "SELECT classes.id, classes.name, classes.description, classes.extends, files.name as filename,
  classes.final, classes.abstract, classes.sinceid
  FROM classes
  INNER JOIN files ON classes.fileid = files.id
  WHERE {$where}
  LIMIT 1";
$res = db_query ($q);

if (db_num_rows ($res) == 0) {
  echo "<p>Invalid class specified.</p>";
  exit;
}

$class = db_fetch_assoc ($res);


echo "<div class=\"viewer_options\">";
echo "<p><b>Viewer options:</b></p>";
if ($_GET['complete'] == 1) {
  echo "<p><a href=\"class.php?id={$class['id']}\">Hide inherited members</a></p>";
} else {
  echo "<p><a href=\"class.php?id={$class['id']}&complete=1\">Show inherited members</a></p>";
}
echo "</div>";


echo "<h2><span class=\"unimportant\">class</span> <i>{$class['name']}</i></h2>";

echo process_inline($class['description']);


echo "<ul>";

$filename_url = 'file.php?name=' . urlencode($class['filename']);
echo '<li>File: <a href="', htmlspecialchars($filename_url), '">';
echo htmlspecialchars($class['filename']), '</a></li>';

if ($class['extends'] != null) {
  echo '<li>Extends: ', get_object_link($class['extends']), '</li>';
}

if ($class['abstract'] == 1) echo '<li>This class is abstract</li>';
if ($class['final'] == 1) echo '<li>This class is final</li>';

// Show implements
$q = "SELECT name FROM class_implements WHERE classid = {$class['id']}";
$res = db_query ($q);

if (db_num_rows ($res) > 0) {
  echo "<li>Implements: ";
  
  $j = 0;
  while ($row = db_fetch_assoc ($res)) {
    if ($j++ > 0) echo ', ';
    echo get_object_link ($row['name']);
  }
  echo '</li>';
}

if ($class['sinceid']) {
  echo '<li>Available since: ', get_since_version($class['sinceid']), '</li>';
}

echo "</ul>";


// Loads the classes tree
// and finds this class within it
$root = create_classes_tree ();
$matcher = new FieldTreeNodeMatcher('name', $class['name']);
$node = $root->findNode ($matcher);

// If our class was found - which it should be - find the top ancestor
// and then draw unordered lists of the class structure
if ($node != null) {
  echo "<h3>Class structure</h3>";
  
  $ancestors = $node->findAncestors();
  $top = end ($ancestors);
  
  echo "<ul class=\"tree\">\n";
  draw_class_tree($top, array($node));
  echo "</ul>\n";
}



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
show_tables ($class['id'], LINK_TYPE_CLASS);


// Show variables
if (count($variables) > 0) {
  echo '<a name="variables"></a>';
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


show_see_also ($class['id'], LINK_TYPE_CLASS);


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
  $q = "SELECT id, extends FROM classes WHERE name LIKE '{$name}'";
  $res = db_query($q);
  if (db_num_rows ($res) == 0) {
    return null;
  }
  
  $row = db_fetch_assoc($res);
  $id = $row['id'];
  $parent = $row['extends'];
  
  // determine functions
  $functions = array();
  $q = "SELECT *, '{$name}' AS classname FROM functions WHERE classid = {$id}";
  $res = db_query($q);
  while ($row = db_fetch_assoc($res)) {
    $functions[$row['name']] = $row;
  }
  
  // determine variables
  $variables = array();
  $q = "SELECT *, '{$name}' AS classname FROM variables WHERE classid = {$id}";
  $res = db_query($q);
  while ($row = db_fetch_assoc($res)) {
    $variables[$row['name']] = $row;
  }
  
  return array($functions, $variables, $parent);
}


/**
* Draws the tree from this node and below as unordered lists within unordered lists
*
* @param array $higlight_nodes The nodes to put class="on" for the LI element.
**/
function draw_class_tree($node, $higlight_nodes) {
  // Draw this item
  if (in_array($node, $higlight_nodes, true)) {
    echo '<li class="on">', get_object_link($node['name']);
  } else {
    echo '<li>', get_object_link($node['name']);
  }
  
  // Draw its children if it has any
  $children = $node->getChildren();
  usort($children, 'nodenamesort');
  
  if (count($children) > 0) {
    echo "<ul>\n";
    foreach ($children as $child) {
      draw_class_tree($child, $higlight_nodes);
    }
    echo "</ul>\n";
  }
  
  echo "</li>\n";
}

function nodenamesort($a, $b) {
  return strcasecmp($a['name'], $b['name']);
}
?>
