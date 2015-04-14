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
 * Displays information about a specific package
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @tag i18n-done
 **/

require_once 'functions.php';


$sql_name = db_quote($_GET['name']);
$q = "SELECT id, name FROM packages WHERE name = {$sql_name} AND projectid = {$project['id']} LIMIT 1";
$res = db_query($q);

if (! $package = db_fetch_assoc ($res)) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo str(STR_PKG_INVALID);
    require_once 'foot.php';
}

$package['name'] = htmlspecialchars($package['name']);

$skin['page_name'] = $package['name'];
require_once 'head.php';

echo '<h2>', str(STR_PKG_PAGE_TITLE, 'name', $package['name']), '</h2>';


// Show files
$file_ids = array();

$q = new SelectQuery();
$q->addFields('files.id, files.name, files.description');
$q->setFrom('files');
$q->addWhere("files.packageid = {$package['id']}");

$q = $q->buildQuery();
$res = db_query ($q);

if (db_num_rows($res) > 0) {
    echo '<div>';
    echo '<h3>', str(STR_FILES), '</h3>';
    echo '<img src="assets/icon_add.png" alt="" title="Show this result" onclick="show_content(event)" class="showhide" style="margin-top: -40px;">';

    $alt = false;
    echo '<div class="list content" style="display: none">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        // output
        echo "<div class=\"{$class}\">";
        echo "<p><strong>", get_file_link($row['name']), "</strong></p>";
        echo delink_inline($row['description']);
        echo '</div>';

        $file_ids[] = $row['id'];
        $alt = ! $alt;
    }
    echo '</div>';
    echo '</div>';


} else {
    echo "<p>There are no files in this package for the version you have selected.</p>";
    require_once 'foot.php';
    exit;
}


$file_ids = implode(', ', $file_ids);


// Show classes
$q = "SELECT id, name, description
  FROM classes
  WHERE fileid IN ({$file_ids})
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<div>';
    echo '<a name="classes"></a>';
    echo '<h3>', str(STR_CLASSES), '</h3>';
    echo '<img src="assets/icon_remove.png" alt="" title="Hide this result" onclick="hide_content(event)" class="showhide" style="margin-top: -40px;">';

    $alt = false;
    echo '<div class="list content">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        echo "<div class=\"{$class}\">";
        echo "<p><strong>", get_class_link($row['name']), "</strong></p>";
        echo delink_inline($row['description']);
        echo '</div>';

        $alt = ! $alt;
    }
    echo '</div>';
    echo '</div>';
}


// Show interfaces
$q = "SELECT id, name, description
  FROM interfaces
  WHERE fileid IN ({$file_ids})
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<div>';
    echo '<a name="interfaces"></a>';
    echo '<h3>', str(STR_INTERFACES), '</h3>';
    echo '<img src="assets/icon_remove.png" alt="" title="Hide this result" onclick="hide_content(event)" class="showhide" style="margin-top: -40px;">';

    $alt = false;
    echo '<div class="list content">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        echo "<div class=\"{$class}\">";
        echo "<p><strong>", get_interface_link($row['name']), "</strong></p>";
        echo delink_inline($row['description']);
        echo '</div>';

        $alt = ! $alt;
    }
    echo '</div>';
    echo '</div>';
}


// Show functions
$q = "SELECT id, name, description, arguments
  FROM functions
  WHERE fileid IN ({$file_ids}) AND classid IS NULL AND interfaceid IS NULL
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<div>';
    echo '<a name="functions"></a>';
    echo '<h3>', str(STR_FUNCTIONS), '</h3>';
    echo '<img src="assets/icon_remove.png" alt="" title="Hide this result" onclick="hide_content(event)" class="showhide" style="margin-top: -40px;">';

    $alt = false;
    echo '<div class="list content">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        // display the function
        echo "<div class=\"{$class}\">";
        echo "<p><strong>", get_function_link(null, $row['name']), "</strong></p>";
        echo delink_inline($row['description']);
        echo "</div>";

        $alt = ! $alt;
    }
    echo '</div>';
    echo '</div>';
}


// Show constants
$q = "SELECT name, value, description
  FROM constants
  WHERE fileid IN ({$file_ids})
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<div>';
    echo '<a name="constants"></a>';
    echo '<h3>', str(STR_CONSTANTS), '</h3>';
    echo '<img src="assets/icon_remove.png" alt="" title="Hide this result" onclick="hide_content(event)" class="showhide" style="margin-top: -40px;">';

    echo "<table class=\"function-list content\">\n";
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
    echo '</div>';
}


require_once 'foot.php';
?>
