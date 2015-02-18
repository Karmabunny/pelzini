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
 * @tag i18n-done
 **/

require_once 'functions.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
    $name = trim($_GET['name']);
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


if ($enumeration == null) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo '<p>', str(STR_ENUM_INVALID), '</p>';
    require_once 'foot.php';
}


$skin['page_name'] = str(STR_ENUM_BROWSER_TITLE, 'name', $enumeration['name']);
require_once 'head.php';


echo '<h2>', str(STR_ENUM_PAGE_TITLE, 'name', $enumeration['name']), '</h2>';

echo process_inline($enumeration['description']);


echo "<ul>";
echo '<li>', str(STR_FILE, 'filename', $enumeration['filename']), '</li>';

if ($enumeration['virtual']) {
    echo '<li>', str(STR_ENUM_VIRTUAL), '</li>';
}

if ($enumeration['sinceid']) {
    echo '<li>', str(STR_AVAIL_SINCE, 'version', get_since_version($enumeration['sinceid'])), '</li>';
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
    echo '<h3>', str(STR_CONSTANTS), '</h3>';

    echo "<table class=\"function-list\">\n";
    echo '<tr><th>', str(STR_NAME), '</th><th>', str(STR_VALUE), '</th><th>', str(STR_DESCRIPTION), "</th></tr>\n";
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
show_tags ($enumeration['id'], LINK_TYPE_ENUMERATION);


require_once 'foot.php';
?>
