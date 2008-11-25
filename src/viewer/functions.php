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




/**
* Determines the link for a specified name (might be a class or an interface)
*
* @param string $name The name to check
* @return string A piece of HTML usable to represent the object, as a link if possible
**/
function get_object_link($name) {

  // check classes
  $sql_name = db_escape($name);
  $q = "SELECT id FROM classes WHERE name = '{$sql_name}' LIMIT 1";
  $res = db_query($q);
  
  if (db_num_rows($res) != 0) {
    $row = db_fetch_assoc($res);
    $ret = "<a href=\"class.php?id={$row['id']}\">{$name}</a>";
    return $ret;
  }
  
  // check interfaces
  $sql_name = db_escape($name);
  $q = "SELECT id FROM interfaces WHERE name = '{$sql_name}' LIMIT 1";
  $res = db_query($q);
  
  if (db_num_rows($res) != 0) {
    $row = db_fetch_assoc($res);
    $ret = "<a href=\"interface.php?id={$row['id']}\">{$name}</a>";
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
?>
