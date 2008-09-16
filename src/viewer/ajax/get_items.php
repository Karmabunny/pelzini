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


header('Content-type: text/xml; charset=UTF-8');
require_once '../functions.php';


$where = array ();

switch ($_GET['type']) {
  case 'classes':
    $table = 'Classes';
    break;
    
  case 'functions':
    $table = 'Functions';
    $where[] = 'ClassID IS NULL';
    break;
    
  case 'files':
    $table = 'Files';
    break;
    
  default:
    echo '<error>Invalid parameters.</error>';
    unset ($_SESSION['last_selected_type']);
    exit;
    
}

$_SESSION['last_selected_type'] = $_GET['type'];

if ($_SESSION['current_package'] != null) {
  $where[] = 'Files.PackageID = ' . $_SESSION['current_package'];
}


$where = implode(' AND ', $where);

if ($table == 'Files') {
  // files don't need a join
  $q = "SELECT ID, Name
    FROM Files";
  if ($where != '') $q .= ' WHERE ' . $where;
  $q .= ' ORDER BY Name';
  
  
} else {
  // other tables do
  $q = "SELECT {$table}.ID, {$table}.Name
    FROM {$table}
    INNER JOIN Files ON {$table}.FileID = Files.ID";
  if ($where != '') $q .= ' WHERE ' . $where;
  $q .= " ORDER BY {$table}.Name";
  
}


// return the items
$res = execute_query($q);
echo "<items type=\"{$_GET['type']}\">";
while ($row = mysql_fetch_assoc ($res)) {

  if ($_GET['type'] == 'files') {
    $row['Name'] = basename($row['Name']);
  }

  $row['Name'] = htmlspecialchars($row['Name']);
  echo "<item id=\"{$row['ID']}\">{$row['Name']}</item>";
}
echo '</items>';
?>
