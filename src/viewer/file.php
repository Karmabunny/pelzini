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
* Shows information about a specific file
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @see ParserFile
**/

require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
  $name = trim($_GET['name']);
  if ($name == '') {
    fatal ("<p>Invalid filename!</p>");
  }
  $name = db_escape ($name);
  $where = "files.name LIKE '{$name}'";
} else {
  $where = "files.id = {$id}";
}


$q = new SelectQuery();
$q->addFields('files.id, files.name, files.description, files.packageid, packages.name as package, files.sinceid');
$q->setFrom('files');
$q->addInnerJoin('packages ON files.packageid = packages.id');
$q->addWhere($where);

$q = $q->buildQuery();
$res = db_query ($q);
$row = db_fetch_assoc ($res);
echo "<h2>{$row['name']}</h2>";

if ($row['packageid'] != null) {
  echo "<p>Package: <a href=\"package.php?id={$row['packageid']}\">{$row['package']}</a></p>";
}

if ($row['sinceid'] != null) {
  echo '<p>Available since: ', get_since_version($row['sinceid']), '</p>';
}

echo "<p><small><a href=\"file_source.php?id={$row['id']}\">View Source</a></small></p>";

echo '<br>', process_inline($row['description']);
$id = $row['id'];


show_authors ($row['id'], LINK_TYPE_FILE);
show_tables ($row['id'], LINK_TYPE_FILE);


// Show classes
$q = "SELECT id, name, description
  FROM classes
  WHERE fileid = {$id}
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="classes"></a>';
  echo "<h3>Classes</h3>";
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<p><strong><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo process_inline($row['description']);
    echo '</div>';
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show interfaces
$q = "SELECT id, name, description
  FROM interfaces
  WHERE fileid = {$id}
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="interfaces"></a>';
  echo "<h3>Interfaces</h3>";
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<p><strong><a href=\"interface.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo process_inline($row['description']);
    echo '</div>';
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show functions
$q = "SELECT id, name, description, arguments
  FROM functions
  WHERE fileid = {$id} AND classid IS NULL AND interfaceid IS NULL
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="functions"></a>';
  echo "<h3>Functions</h3>";
  
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
    echo "<p><strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo process_inline($row['description']);
    echo '</div>';
    
    $alt = ! $alt;
  }
  echo '</div>';
}



// Show constants
$q = "SELECT name, value, description
  FROM constants
  WHERE fileid = {$id}
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="constants"></a>';
  echo "<h3>Constants</h3>";
  
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Value</th><th>Description</th></tr>\n";
  while ($row = db_fetch_assoc ($res)) {
    // encode for output
    $row['name'] = htmlspecialchars($row['name']);
    $row['value'] = htmlspecialchars($row['value']);
    if ($row['description'] == null) $row['description'] = '&nbsp;';
    
    // display the constant
    echo "<tr>";
    echo "<td><code>{$row['name']}</code></td>";
    echo "<td><code>{$row['value']}</code></td>";
    echo "<td>{$row['description']}</td>";
    echo "</tr>\n";
  }
  echo "</table>\n";
}


require_once 'foot.php';
?>
