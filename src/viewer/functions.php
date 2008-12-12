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


chdir(dirname(__FILE__));

require_once 'constants.php';
require_once 'tree.php';
require_once 'select_query.php';


// Load configs
if (file_exists('config.php')) {
  require_once 'config.php';
  $config_found = true;
}

if (file_exists('config.viewer.php')) {
  require_once 'config.viewer.php';
  $config_found = true;
}

// Throw an error if no config
if (! $config_found) {
  header ('Content-type: text/plain');
  echo "ERROR:\n";
  echo "Unable to find required configuration file 'config.php' or 'config.viewer.php'.\n";
  echo "Please configure the docu viewer. For more information, see:\n";
  echo "http://docu.sourceforge.net\n\n";
  echo "The easiest way to configure docu is to run the installer and follow the instructions provided.";
  exit;
}

// Defaults if config options are ommited
if ($dvgDatabaseEngine == null) $dvgDatabaseEngine = 'mysql';


// Load the database
require_once "database_{$dvgDatabaseEngine}.php";
db_connect();

session_start();

if (get_magic_quotes_gpc ()) {
  $_POST = fix_magic_quotes ($_POST);
  $_GET = fix_magic_quotes ($_GET);
}




/**
* Fixes all magically quoted strings in the given array or string
* 
* @param mixed &$item The string or array in which to fix magic quotes
* @return mixed The resultant string or array
*/
function fix_magic_quotes (&$item) {
  if (is_array ($item)) {
    // if a key is magically quoted, it needs to be modified - do key modifications after the loop is done,
    // so that the same data does not get fixed twice
    $key_replacements = array ();
    foreach ($item as $key => $val) {
      $new_key = stripslashes ($key);
      if ($new_key != $key) $key_replacements[$key] = $new_key;
      $item[$key] = fix_magic_quotes ($val);
    }
    
    foreach ($key_replacements as $old_key => $new_key) {
      $item[$new_key] = $item[$old_key];
      unset ($item[$old_key]);
    }
    
  } else {
    $item = stripslashes ($item);
  }
  
  return $item;
}


/**
* Determines the link for a specified name (might be a class, an interface or a function)
*
* @param string $name The name to check
* @return string A piece of HTML usable to represent the object, as a link if possible
**/
function get_object_link($name) {

  // check classes
  $sql_name = db_escape($name);
  $q = "SELECT id FROM classes WHERE name = '{$sql_name}'";
  $res = db_query($q);
  
  if (db_num_rows($res) != 0) {
    $row = db_fetch_assoc($res);
    $ret = "<a href=\"class.php?id={$row['id']}\">{$name}</a>";
    return $ret;
  }
  
  // check interfaces
  $sql_name = db_escape($name);
  $q = "SELECT id FROM interfaces WHERE name = '{$sql_name}'";
  $res = db_query($q);
  
  if (db_num_rows($res) != 0) {
    $row = db_fetch_assoc($res);
    $ret = "<a href=\"interface.php?id={$row['id']}\">{$name}</a>";
    return $ret;
  }
  
  // check functions
  $sql_name = db_escape($name);
  $q = "SELECT id FROM functions WHERE name = '{$sql_name}' AND classid IS NULL AND interfaceid IS NULL";
  $res = db_query($q);
  
  if (db_num_rows($res) != 0) {
    $row = db_fetch_assoc($res);
    $ret = "<a href=\"function.php?id={$row['id']}\">{$name}</a>";
    return $ret;
  }
  
  return $name;
}


function show_authors ($link_id, $link_type) {
  $q = "SELECT name, email, description FROM item_authors WHERE linkid = {$link_id} AND linktype = {$link_type}";
  $res = db_query($q);
  
  if (db_num_rows($res) > 0) {
    echo "<h3>Authors</h3>";
    
    echo '<ul>';
    while ($row = db_fetch_assoc ($res)) {
      $row['name'] = htmlspecialchars($row['name']);
      $row['email'] = htmlspecialchars($row['email']);
      
      echo "<li><a href=\"author.php?name={$row['name']}\">{$row['name']}</a>";
      
      if ($row['email']) {
        echo "<br><a href=\"mailto:{$row['email']}\">{$row['email']}</a>";
      }
      
      if ($row['description']) {
        echo "<br><small>{$row['description']}</small>";
      }
      
      echo '</li>';
    }
    echo '</ul>';
  }
}


/**
* Shows the tables used by a specific file, function or class
**/
function show_tables ($link_id, $link_type) {
  $q = "SELECT name, action, description FROM item_tables WHERE linkid = {$link_id} AND linktype = {$link_type}";
  $res = db_query($q);
  
  if (db_num_rows($res) > 0) {
    echo "<h3>Tables used</h3>";
    
    echo '<ul>';
    while ($row = db_fetch_assoc ($res)) {
      $name_url = urlencode($row['name']);
      $row['name'] = htmlspecialchars($row['name']);
      
      echo "<li><i>{$row['action']}</i> <a href=\"table.php?name={$name_url}\">{$row['name']}</a>";
      
      if ($row['description']) {
        echo "<br><small>{$row['description']}</small>";
      }
      
      echo '</li>';
    }
    echo '</ul>';
  }
}


/**
* Gets HTML for a version, based on the version id
*
* @table select versions Will only select once, results are stored in a static array
**/
function get_since_version($version_id) {
  $version_id = (int) $version_id;
  
  $q = "SELECT name FROM versions WHERE id = {$version_id}";
  $res = db_query($q);
  $row = db_fetch_assoc($res);
  
  return $row['name'];
}

/**
* Processes inline tags within text
*
* @param string $text the input text
* @return string The output, with inline text replaced
**/
function process_inline($text) {
  $text = preg_replace ('/{@link ([^}]*?)}/ie', 'process_inline_link(\'$1\')', $text);
  $text = preg_replace ('/{@see ([^}]*?)}/ie', 'process_inline_link(\'$1\')', $text);
  return $text;
}

/**
* Replaces @link tags
**/
function process_inline_link($original_text) {
  list ($text, $link_text) = explode(' ', $original_text, 2);
  if ($link_text == '') $link_text = $text;
  
  $text = trim($text);
  $text_sql = db_quote($text);
  
  if (preg_match('/^(?:https?|ftp|mailto|telnet|ssh|rsync):/', $text)) {
    // It's a URL
    return "<a href=\"{$text}\">{$link_text}</a>";
    
  } else if (strpos($text, '::') !== false) {
    // It's a class member
    list ($class, $member) = explode ('::', $text, 2);
    
    $class_sql = db_quote($class);
    $q = "SELECT id, name FROM classes WHERE name LIKE {$class_sql}";
    $res = db_query($q);
    if ($row = db_fetch_assoc($res)) {
      $class_id = $row['id'];
      
      if (substr($member, -2) == '()') {
        $member = trim(substr($member, 0, -2));
      }
      $text_sql = db_quote($member);
      
      // member functions
      $q = "SELECT id, name FROM functions WHERE name LIKE {$text_sql} AND classid = {$class_id}";
      $res = db_query($q);
      if ($row = db_fetch_assoc($res)) {
        return "<a href=\"function.php?id={$row['id']}\">{$link_text}</a>";
      }
      
      // member variables
      $q = "SELECT id, name FROM variables WHERE name LIKE {$text_sql} AND classid = {$class_id}";
      $res = db_query($q);
      if ($row = db_fetch_assoc($res)) {
        return "<a href=\"class.php?id={$class_id}#variables\">{$link_text}</a>";
      }
      
      return $link_text;
    }
  }
  
  // Look for classes
  $q = "SELECT id, name FROM classes WHERE name LIKE {$text_sql}";
  $res = db_query($q);
  if ($row = db_fetch_assoc($res)) {
    return "<a href=\"class.php?id={$row['id']}\">{$link_text}</a>";
  }
  
  // Look for files
  $file = $text;
  if ($file[0] != '/') $file = '/' . $file;
  $file_sql = db_quote($file);
  $q = "SELECT id, name FROM files WHERE name LIKE {$file_sql}";
  $res = db_query($q);
  if ($row = db_fetch_assoc($res)) {
    return "<a href=\"file.php?id={$row['id']}\">{$link_text}</a>";
  }
  
  // Look for constants
  $q = "SELECT id, name, fileid FROM constants WHERE name LIKE {$text_sql}";
  $res = db_query($q);
  if ($row = db_fetch_assoc($res)) {
    return "<a href=\"file.php?id={$row['fileid']}#constants\">{$link_text}</a>";
  }
  
  if (substr($text, -2) == '()') {
    $text = trim(substr($text, 0, -2));
    $text_sql = db_quote($text);
  }
      
  // Look for functions
  $q = "SELECT id, name FROM functions WHERE name LIKE {$text_sql} AND classid IS NULL AND interfaceid IS NULL";
  if ($class_id) $q .= " AND classid = {$class_id}";
  $res = db_query($q);
  if ($row = db_fetch_assoc($res)) {
    return "<a href=\"function.php?id={$row['id']}\">{$link_text}</a>";
  }
  
  // Look for documents
  // This is very last, and is done against the original full text (you cannot define an alternate name for the link of a document)
  $orig_text = db_quote($original_text);
  $q = "SELECT id, name FROM documents WHERE name LIKE {$orig_text}";
  $res = db_query($q);
  if ($row = db_fetch_assoc($res)) {
    $row['name'] = urlencode($row['name']);
    return "<a href=\"document.php?name={$row['name']}\">{$original_text}</a>";
  }
  
  return $original_text;
}


?>
