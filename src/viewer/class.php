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
* Shows information about a specific class
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @see ParserClass
* @tag i18n-done
**/

require_once 'functions.php';


define ('PAGE_CLASS_GENERAL',  0);
define ('PAGE_CLASS_USED_BY',  1);
define ('PAGE_CLASS_EXTENDS',  2);
define ('PAGE_CLASS_SOURCE',   3);
 

$id = (int) $_GET['id'];
$_GET['page'] = (int) $_GET['page'];


// Determine what to show
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

if (! $class = db_fetch_assoc ($res)) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo '<p>', str(STR_CLASS_INVALID), '</p>';
    require_once 'foot.php';
}

$skin['page_name'] = str(STR_CLASS_BROWSER_TITLE, 'name', $class['name']);
require_once 'head.php';


// Pages
$pages = array(
  str(STR_CLASS_PAGE_GENERAL),
  str(STR_CLASS_PAGE_USED_BY),
  str(STR_CLASS_PAGE_EXTENDS),
  str(STR_CLASS_PAGE_SOURCE)
);

echo "<div class=\"viewer_options\">";
echo '<p><b>', str(STR_CLASS_PAGE), '</b></p>';
foreach ($pages as $num => $page) {
  if ($_GET['page'] == $num) {
    echo "<p class=\"on\"><a href=\"class.php?id={$class['id']}&page={$num}\">{$page}</a></p>";
  } else {
    echo "<p><a href=\"class.php?id={$class['id']}&page={$num}\">{$page}</a></p>";
  }
}
echo "</div>";

// Page options
if ($_GET['page'] == 0) {
  echo "<div class=\"viewer_options\">";
  echo '<p><b>', str(STR_CLASS_OPTIONS), '</b></p>';
  if ($_GET['complete'] == 1) {
    echo "<p class=\"on\"><a href=\"class.php?id={$class['id']}\">", str(STR_CLASS_INHERITED), "</a></p>";
  } else {
    echo "<p><a href=\"class.php?id={$class['id']}&complete=1\">", str(STR_CLASS_INHERITED), "</a></p>";
  }
  echo "</div>";
}




echo '<h2>', str(STR_CLASS_PAGE_TITLE, 'name', $class['name']), '</h2>';

echo process_inline($class['description']);


// Basic class details
echo "<ul>";
echo '<li>', str(STR_FILE, 'filename', $class['filename']), '</li>';

if ($class['extends'] != null) {
  echo '<li>', str(STR_CLASS_EXTENDS, 'link', get_object_link($class['extends'])), '</li>';
}

// Show implements
$q = "SELECT name FROM class_implements WHERE classid = {$class['id']}";
$res = db_query ($q);

if (db_num_rows ($res) > 0) {
  echo '<li>', str(STR_CLASS_IMPLEMENTS);
  
  $j = 0;
  while ($row = db_fetch_assoc ($res)) {
    if ($j++ > 0) echo ', ';
    echo get_object_link ($row['name']);
  }
  echo '</li>';
}

if ($class['abstract'] == 1) echo '<li>', str(STR_CLASS_ABSTRACT), '</li>';
if ($class['final'] == 1) echo '<li>', str(STR_CLASS_FINAL), '</li>';

if ($class['sinceid']) {
  echo '<li>', str(STR_AVAIL_SINCE, 'version', get_since_version($class['sinceid'])), '</li>';
}
echo "</ul>";



switch ($_GET['page']) {
  case PAGE_CLASS_GENERAL:
    // Determine a list of variables and functions
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
      echo '<h3>', str(STR_VARIABLES), '</h3>';
      echo "<table class=\"function-list\">\n";
      echo '<tr><th>', str(STR_NAME), '</th><th>', str(STR_DESCRIPTION), "</th></tr>\n";
      foreach ($variables as $row) {
        // encode for output
        $row['name'] = htmlspecialchars($row['name']);
        if ($row['description'] == null) $row['description'] = '&nbsp;';
        
        if ($row['static']) $row['name'] .= ' ' . str(STR_CLASS_VAR_STATIC);
        
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
    break;
    
    
  case PAGE_CLASS_USED_BY:
    // Loads the classes tree
    // and finds this class within it
    $root = create_classes_tree ();
    $matcher = new FieldTreeNodeMatcher('name', $class['name']);
    $node = $root->findNode ($matcher);

    // If our class was found - which it should be - find the top ancestor
    // and then draw unordered lists of the class structure
    if ($node != null) {
      echo '<h3>', str(STR_CLASS_STRUCTURE), '</h3>';
      
      $ancestors = $node->findAncestors();
      $top = end ($ancestors);
      
      echo "<ul class=\"tree\">\n";
      draw_class_tree($top, array($node));
      echo "</ul>\n";
    }
    
    
    $sql_class_name = db_quote ($class['name']);
    
    // Query to get functions which return this class
    $q = "SELECT functions.id, functions.name, functions.description, functions.classid,
          files.name as filename, functions.fileid, classes.name as class
      FROM functions
      INNER JOIN files ON functions.fileid = files.id
      LEFT JOIN classes ON functions.classid = classes.id
      WHERE functions.returntype = {$sql_class_name} ORDER BY functions.name";
    $res = db_query ($q);
    
    // Display any functions which return this class
    if (db_num_rows ($res) > 0) {
      echo '<h3>', str(STR_CLASS_FUNC_RETURN), '</h3>';
      
      echo '<div class="list">';
      while ($row = db_fetch_assoc ($res)) {
        $row['name'] = htmlspecialchars ($row['name']);
        $row['filename'] = htmlspecialchars ($row['filename']);
        
        $class = 'item';
        if ($alt) $class .= '-alt';
        
        echo "<div class=\"{$class}\">";
        echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
        echo "<p><strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong>";
        
        if ($row['class'] != null) {
          $row['class'] = htmlspecialchars($row['class']);
          echo " <small>from class <a href=\"class.php?id={$row['classid']}\">{$row['class']}</a></small>";
        }
        
        echo "<div class=\"content\">";
        echo delink_inline($row['description']);
        echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small></div>";
        echo "</div>";
        
        $alt = ! $alt;
      }
      echo '</div>';
    }
    
    
    // Query to get functions which use this class as an argument
    $q = "SELECT functions.id, functions.name, functions.description, functions.classid,
          files.name as filename, functions.fileid, classes.name as class, arguments.name as argname
      FROM functions
      INNER JOIN arguments ON arguments.functionid = functions.id
      INNER JOIN files ON functions.fileid = files.id
      LEFT JOIN classes ON functions.classid = classes.id
      WHERE arguments.type = {$sql_class_name} ORDER BY functions.name";
    $res = db_query ($q);
    
    // Display any functions which use this class as an argument
    if (db_num_rows ($res) > 0) {
      echo '<h3>', str(STR_CLASS_FUNC_ARG), '</h3>';
      
      echo '<div class="list">';
      while ($row = db_fetch_assoc ($res)) {
        $row['name'] = htmlspecialchars ($row['name']);
        $row['filename'] = htmlspecialchars ($row['filename']);
        
        $class = 'item';
        if ($alt) $class .= '-alt';
        
        echo "<div class=\"{$class}\">";
        echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
        echo "<p><strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong>";
        
        if ($row['class'] != null) {
          $row['class'] = htmlspecialchars($row['class']);
          echo " <small>from class <a href=\"class.php?id={$row['classid']}\">{$row['class']}</a></small>";
        }
        
        echo "<div class=\"content\">";
        echo delink_inline($row['description']);
        echo "<br><small>Argument name: {$row['argname']}</small>";
        echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small>";
        echo "</div>";
        echo "</div>";
        
        $alt = ! $alt;
      }
      echo '</div>';
    }
    break;
    
    
  case PAGE_CLASS_EXTENDS:
    echo '<h3>', str(STR_CLASS_EXTENDING), '</h3>';
    
    require_once 'php_code_renderer.php';
    $renderer = new PHPCodeRenderer();
    $code = $renderer->drawClassExtends ($class['id']);
    
    echo "<pre class=\"source\">";
    echo htmlspecialchars ($code);
    echo "</pre>";
    break;
    
    
  case PAGE_CLASS_SOURCE:
    require_once 'search_functions.php';
    search_source ($class['name'], true);
    break;
    
    
  default:
    echo '<h3>', str(STR_ERROR_TITLE), '</h3>';
    echo '<p>', str(STR_CLASS_INVALID_INFO), '</p>';
    break;
}

show_see_also ($class['id'], LINK_TYPE_CLASS);
show_tags ($class['id'], LINK_TYPE_CLASS);


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
