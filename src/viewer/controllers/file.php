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
 * Shows information about a specific file
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @see ParserFile
 * @tag i18n-done
 **/

require_once 'functions.php';


// Determine what to show
$sql_name = db_quote($_GET['name']);
$q = new SelectQuery();
$q->addFields('files.id, files.name, files.description, files.packageid, files.namespace, packages.name as package, files.sinceid');
$q->setFrom('files');
$q->addInnerJoin('packages ON files.packageid = packages.id');
$q->addWhere("files.name = {$sql_name}");
$q->addProjectWhere();

$q = $q->buildQuery();
$res = db_query ($q);
$file = db_fetch_assoc ($res);


if ($file == null) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo '<p>', str(STR_FILE_INVALID), '</p>';
    require_once 'foot.php';
}


$skin['page_name'] = $file['name'];
require_once 'head.php';


echo '<h2>', str(STR_FILE_PAGE_TITLE, 'name', $file['name']), '</h2>';

if ($file['packageid'] != null) {
    echo '<p>', str(STR_PACKAGE, 'id', $file['packageid'], 'name', $file['package']), '</p>';
}

if ($file['namespace'] != null) {
    echo '<p>', str(STR_NAMESPACE, 'name', $file['namespace']), '</p>';
}

if ($file['sinceid'] != null) {
    echo '<p>', str(STR_AVAIL_SINCE, 'version', get_since_version($file['sinceid'])), '</p>';
}

echo "<p><small><a href=\"file_source?name=", urlencode($file['name']), "\">", str(STR_FILE_VIEW_SOURCE), '</a></small></p>';

echo '<br>', process_inline($file['description']);


show_authors ($file['id'], LINK_TYPE_FILE);
show_tables ($file['id'], LINK_TYPE_FILE);


// Show classes
$q = "SELECT id, name, description
  FROM classes
  WHERE fileid = {$file['id']}
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<a name="classes"></a>';
    echo '<h3>', str(STR_CLASSES), '</h3>';

    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        echo "<div class=\"{$class}\">";
        echo "<p><strong>", get_class_link($row['name']), "</strong></p>";
        echo process_inline($row['description']);
        echo '</div>';

        $alt = ! $alt;
    }
    echo '</div>';
}


// Show interfaces
$q = "SELECT id, name, description
  FROM interfaces
  WHERE fileid = {$file['id']}
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<a name="interfaces"></a>';
    echo '<h3>', str(STR_INTERFACES), '</h3>';

    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        echo "<div class=\"{$class}\">";
        echo "<p><strong>", get_interface_link($row['name']), "</strong></p>";
        echo process_inline($row['description']);
        echo '</div>';

        $alt = ! $alt;
    }
    echo '</div>';
}


// Show functions
$q = "SELECT id, name, description, arguments
  FROM functions
  WHERE fileid = {$file['id']} AND classid IS NULL AND interfaceid IS NULL
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<a name="functions"></a>';
    echo '<h3>', str(STR_FUNCTIONS), '</h3>';

    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        // display the function
        echo "<div class=\"{$class}\">";
        echo "<p><strong>", get_function_link(null, $row['name']), "</strong></p>";
        echo process_inline($row['description']);
        echo '</div>';

        $alt = ! $alt;
    }
    echo '</div>';
}


// Show enums
$q = "SELECT id, name, description
  FROM enumerations
  WHERE fileid = {$file['id']}
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<a name="functions"></a>';
    echo '<h3>', str(STR_ENUMERATIONS), '</h3>';

    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        // display the function
        echo "<div class=\"{$class}\">";
        echo "<p><strong><a href=\"enumeration?name=", urlencode($row['name']), "\">", htmlspecialchars($row['name']), "</a></strong></p>";
        echo process_inline($row['description']);
        echo '</div>';

        $alt = ! $alt;
    }
    echo '</div>';
}


// Show constants
$q = "SELECT name, value, description
  FROM constants
  WHERE fileid = {$file['id']} AND enumerationid IS NULL
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<a name="constants"></a>';
    echo '<h3>', str(STR_CONSTANTS), '</h3>';

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


show_see_also ($file['id'], LINK_TYPE_FILE);
show_tags ($file['id'], LINK_TYPE_FILE);


require_once 'foot.php';
?>
