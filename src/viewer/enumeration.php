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
* Shows information about a specific enumeration
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.3
* @see ParserEnumeration
**/

require_once 'functions.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
  $name = trim($_GET['name']);
  if ($name == '') {
    fatal ("<p>Invalid function name!</p>");
  }
  $name = db_escape ($name);
  $where = "enumerations.name LIKE '{$name}'";
} else {
  $where = "enumerations.id = {$id}";
}


$q = new SelectQuery();
$q->addFields('enumerations.id, enumerations.name, enumerations.description, enumerations.virtual, files.name AS filename, enumerations.sinceid');
$q->setFrom('enumerations');
$q->addInnerJoin('files ON enumerations.fileid = files.id');
$q->addWhere($where);
$q->addSinceVersionWhere();

$q = $q->buildQuery();
$res = db_query ($q);
$enumeration = db_fetch_assoc ($res);


$skin['page_name'] = "{$enumeration['name']} enum";
require_once 'head.php';


echo "<h2><span class=\"unimportant\">enum</span> <i>{$enumeration['name']}</i></h2>";

echo process_inline($enumeration['description']);


echo "<ul>";

$filename_url = 'file.php?name=' . urlencode($enumeration['filename']);
echo '<li>File: <a href="', htmlspecialchars($filename_url), '">';
echo htmlspecialchars($enumeration['filename']), '</a></li>';

if ($enumeration['virtual']) {
  echo '<li>This enumeration is virtual</li>';
}

if ($enumeration['sinceid']) {
  echo '<li>Available since: ', get_since_version($enumeration['sinceid']), '</li>';
}

echo "</ul>";


show_authors ($enumeration['id'], LINK_TYPE_ENUMERATION);
show_tables ($enumeration['id'], LINK_TYPE_ENUMERATION);


// Show constants
$q = "SELECT name, value, description
  FROM constants
  WHERE enumerationid = {$enumeration['id']}
  ORDER BY value";
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


show_see_also ($enumeration['id'], LINK_TYPE_FUNCTION);


require_once 'foot.php';
?>

