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
 * Shows information about a specific interface
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @see ParserInterface
 * @tag i18n-done
 **/

require_once 'functions.php';


// Get the details of this interface
$sql_name = db_quote($_GET['name']);
$q = "SELECT interfaces.id, interfaces.name, interfaces.namespace, interfaces.description, files.name AS filename,
  interfaces.sinceid
  FROM interfaces
  INNER JOIN files ON interfaces.fileid = files.id
  WHERE interfaces.name = {$sql_name}
    AND interfaces.projectid = {$project['id']}
  LIMIT 1";
$res = db_query ($q);

if (! $interface = db_fetch_assoc ($res)) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo '<p>', str(STR_INTERFACE_INVALID), '</p>';
    require_once 'foot.php';
}

$skin['page_name'] = str(STR_INTERFACE_BROWSER_TITLE, 'name', $interface['name']);
require_once 'head.php';


// Show basic details
echo '<h2>', str(STR_INTERFACE_PAGE_TITLE, 'name', $interface['name']), '</h2>';

echo process_inline($interface['description']);


echo '<ul>';
echo '<li>', str(STR_FILE, 'filename', $interface['filename']), '</li>';

if ($interface['namespace'] != null) {
    echo '<li>', str(STR_NAMESPACE, 'name', $interface['namespace']), '</li>';
}

if ($interface['sinceid']) {
    echo '<li>', str(STR_AVAIL_SINCE, 'version', get_since_version($interface['sinceid'])), '</li>';
}
echo '</ul>';



show_authors ($interface['id'], LINK_TYPE_INTERFACE);
show_tables ($interface['id'], LINK_TYPE_INTERFACE);


// Show implementors
$name = db_quote($interface['name']);
$q = "SELECT classes.id, classes.name
  FROM classes
  INNER JOIN class_implements ON class_implements.classid = classes.id
  WHERE class_implements.name = {$name}";
$res = db_query ($q);
if (db_num_rows($res) > 0) {
    echo '<h3>', str(STR_INTERFACE_IMPLEMENTORS), '</h3>';
    echo "<ul>";
    while ($row = db_fetch_assoc ($res)) {
        echo "<li>", get_object_link($row['name']);
    }
    echo "</ul>";
}


// Show functions
$q = "SELECT id, name, description, arguments FROM functions WHERE interfaceid = {$interface['id']}";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    while ($row = db_fetch_assoc ($res)) {
        if ($row['description'] == null) {
            $row['description'] = '<em>This function does not have a description</em>';
        }

        // display
        echo "<h3>{$row['visibility']} ", get_function_link($interface['name'], $row['name']);
        echo "</h3>";

        show_function_usage ($row['id']);
        echo '<br>';
        echo process_inline($row['description']);
    }
}


show_tags ($interface['id'], LINK_TYPE_INTERFACE);
show_see_also ($interface['id'], LINK_TYPE_INTERFACE);


require_once 'foot.php';
?>
