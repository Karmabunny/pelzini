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

require_once 'functions.php';

$q = "SELECT name, license FROM projects WHERE id = {$dvgProjectID}";
$res = db_query($q);
$project = db_fetch_assoc($res);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>Documentation for <?= $project['name']; ?></title>
  <link href="style.css" rel="stylesheet" type="text/css">
  <script language="javascript" src="ajax/ajax.js"></script>
  
<?php
$body = '<body>';

if (isset($_SESSION['last_selected_type'])) {
  $body = '<body onload="load();">';
  
  echo "<script>
    function load() {
      change_sidebar_type('{$_SESSION['last_selected_type']}');
    }
  </script>\n";
}
?>

</head>
<?= $body; ?>

<div class="header">
  <h1>Documentation for <?= $project['name']; ?></h1>
</div>

<div class="navigation">
  <a href="index.php">Home</a>
  <a href="select_package.php">All packages</a>
  &nbsp;
  
  <?php
  $q = "SELECT id, name FROM packages ORDER BY name";
  $res = db_query($q);
  while ($row = db_fetch_assoc($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    if ($_SESSION['current_package'] == $row['id']) {
      echo "<a href=\"select_package.php?id={$row['id']}\" class=\"on\">{$row['name']}</a> ";
    } else {
      echo "<a href=\"select_package.php?id={$row['id']}\">{$row['name']}</a> ";
    }
  }
  ?>
</div>

<table class="main">
<tr>
<td class="sidebar">
  <div class="box-nohead">
    <form action="search.php" method="get">
      <input type="text" name="q" style="width: 135px;" value="<?= htmlspecialchars ($_GET['q']); ?>">
      <input type="submit" value="Search">
    </form>
  </div>
  
  
<?php
// Classes list
$q = "SELECT classes.id, classes.name
  FROM classes
  INNER JOIN files ON classes.fileid = files.id";
if ($_SESSION['current_package']) $q .= " WHERE files.packageid = {$_SESSION['current_package']}";
$q .= " ORDER BY classes.name";

$res = db_query ($q);
if (db_num_rows ($res) > 0) {
  echo '  <div class="box">';
  echo '    <h2>Classes</h2>';
  echo '    <div id="sidebar_items">';
  
  while ($row = db_fetch_assoc ($res)) {
    echo "<p><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></p>";
  }
  
  echo '    </div>';
  echo '  </div>';
}


// Functions list
$q = "SELECT functions.id, functions.name
  FROM functions
  INNER JOIN files ON functions.fileid = files.id
  WHERE functions.classid IS NULL AND functions.interfaceid IS NULL";
if ($_SESSION['current_package']) $q .= " AND files.packageid = {$_SESSION['current_package']}";
$q .= " ORDER BY functions.name";
$res = db_query ($q);

if (db_num_rows ($res) > 0) {
  echo '  <div class="box">';
  echo '    <h2>Functions</h2>';
  echo '    <div id="sidebar_items">';
  
  while ($row = db_fetch_assoc ($res)) {
    echo "<p><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></p>";
  }
  
  echo '    </div>';
  echo '  </div>';
}


// Files list
$q = "SELECT files.id, files.name
  FROM files";
if ($_SESSION['current_package']) $q .= " WHERE files.packageid = {$_SESSION['current_package']}";
$q .= " ORDER BY files.name";
$res = db_query ($q);

if (db_num_rows ($res) > 0) {
  echo '  <div class="box">';
  echo '    <h2>Files</h2>';
  echo '    <div id="sidebar_items">';
  
  while ($row = db_fetch_assoc ($res)) {
    echo "<p><a href=\"file.php?id={$row['id']}\">{$row['name']}</a></p>";
  }
  
  echo '    </div>';
  echo '  </div>';
}
?>
</td>

<td class="main">
