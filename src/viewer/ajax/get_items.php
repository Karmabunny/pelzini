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
* Returns a list of items
* Not used.
*
* @package Viewer
**/

header('Content-type: text/xml; charset=UTF-8');
require_once '../functions.php';


$where = array ();

switch ($_GET['type']) {
  case 'classes':
    $table = 'classes';
    break;
    
  case 'functions':
    $table = 'functions';
    $where[] = 'classid IS NULL';
    break;
    
  case 'files':
    $table = 'files';
    break;
    
  default:
    echo '<error>Invalid parameters.</error>';
    unset ($_SESSION['last_selected_type']);
    exit;
    
}

$_SESSION['last_selected_type'] = $_GET['type'];

if ($_SESSION['current_package'] != null) {
  $where[] = 'files.packageid = ' . $_SESSION['current_package'];
}


$where = implode(' AND ', $where);

if ($table == 'Files') {
  // files don't need a join
  $q = "SELECT id, name
    FROM files";
  if ($where != '') $q .= ' WHERE ' . $where;
  $q .= ' ORDER BY name';
  
  
} else {
  // other tables do
  $q = "SELECT {$table}.id, {$table}.name
    FROM {$table}
    INNER JOIN files ON {$table}.fileid = files.id";
  if ($where != '') $q .= ' WHERE ' . $where;
  $q .= " ORDER BY {$table}.name";
  
}


// return the items
$res = db_query($q);
echo "<items type=\"{$_GET['type']}\">";
while ($row = db_fetch_assoc ($res)) {

  if ($_GET['type'] == 'files') {
    $row['name'] = basename($row['name']);
  }

  $row['name'] = htmlspecialchars($row['name']);
  echo "<item id=\"{$row['id']}\">{$row['name']}</item>";
}
echo '</items>';
?>
