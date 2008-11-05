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


if (file_exists('config.php')) {
  require_once 'config.php';
  $config_found = true;
}

if (file_exists('config.viewer.php')) {
  require_once 'config.viewer.php';
  $config_found = true;
}

if (! $config_found) {
  header ('Content-type: text/plain');
  echo "ERROR:\n";
  echo "Unable to find required configuration file 'config.php' or 'config.viewer.php'.\n";
  echo "Please configure the docu viewer. For more information, see:\n";
  echo "http://docu.sourceforge.net\n\n";
  echo "The easiest way to configure docu is to run the installer and follow the instructions provided.";
  exit;
}


session_start();

$dbc = mysql_connect ($dvgDatabaseSettings['server'], $dvgDatabaseSettings['username'], $dvgDatabaseSettings['password']);
mysql_select_db ($dvgDatabaseSettings['name']);


/**
* Executes a MySQL query
*
* @param $q The query to execute
* @return The result from the query
**/
function execute_query($q) {
  global $dbc;
  $res = mysql_query ($q, $dbc);
  if ($res === false) {
    echo mysql_error ($dbc);
  }
  return $res;
}


/**
* Escapes a string ready for putting into MySQL
*
* @param string $string The string to escape
*/
function mysql_escape($string) {
  global $dbc;
  return mysql_real_escape_string ($string, $dbc);
}


/**
* Determines the link for a specified name (might be a class or an interface)
*
* @param string $name The name to check
* @return string A piece of HTML usable to represent the object, as a link if possible
**/
function get_object_link($name) {

  // check classes
  $sql_name = mysql_escape($name);
  $q = "SELECT ID FROM Classes WHERE Name = '{$sql_name}' LIMIT 1";
  $res = execute_query($q);
  
  if (mysql_num_rows($res) != 0) {
    $row = mysql_fetch_assoc($res);
    $ret = "<a href=\"class.php?id={$row['ID']}\">{$name}</a>";
    return $ret;
  }
  
  // check interfaces
  $sql_name = mysql_escape($name);
  $q = "SELECT ID FROM Interfaces WHERE Name = '{$sql_name}' LIMIT 1";
  $res = execute_query($q);
  
  if (mysql_num_rows($res) != 0) {
    $row = mysql_fetch_assoc($res);
    $ret = "<a href=\"interface.php?id={$row['ID']}\">{$name}</a>";
    return $ret;
  }
  
  return $name;
}


function show_authors ($link_id, $link_type) {
  $q = "SELECT Name, Email, Description FROM Authors WHERE LinkID = {$link_id} AND LinkType = {$link_type}";
  $res = execute_query($q);
  
  if (mysql_num_rows($res) > 0) {
    echo "<h3>Authors</h3>";
    
    echo '<ul>';
    while ($row = mysql_fetch_assoc ($res)) {
      $row['Name'] = htmlspecialchars($row['Name']);
      $row['Email'] = htmlspecialchars($row['Email']);
      
      echo "<li>{$row['Name']}";
      
      if ($row['Email']) {
        echo "<br><a href=\"mailto:{$row['Email']}\">{$row['Email']}</a>";
      }
      
      if ($row['Description']) {
        echo "<br>{$row['Description']}";
      }
      
      echo '</li>';
    }
    echo '</ul>';
  }
}
?>
