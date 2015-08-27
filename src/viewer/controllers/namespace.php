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
$q = "SELECT id, name FROM namespaces WHERE name = {$sql_name} AND projectid = {$project['id']} LIMIT 1";
$res = db_query($q);

if (! $namespace = db_fetch_assoc ($res)) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo str(STR_NAMESPACE_INVALID);
    require_once 'foot.php';
}

$namespace['name'] = htmlspecialchars($namespace['name']);

$skin['page_name'] = $namespace['name'];
require_once 'head.php';

echo '<h2>', str(STR_NAMESPACE_PAGE_TITLE, 'name', $namespace['name']), '</h2>';


// Show sub-namespaces
$q = "SELECT id, name
  FROM namespaces
  WHERE parentid = {$namespace['id']}
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<div>';
    echo '<a name="classes"></a>';
    echo '<h3>', str(STR_NAMESPACES), '</h3>';
    echo '<img src="assets/icon_remove.png" alt="" title="Hide this result" onclick="hide_content(event)" class="showhide" style="margin-top: -40px;">';

    $alt = false;
    echo '<div class="list content">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        echo "<div class=\"{$class}\">";
        echo "<p><strong><a href=\"namespace?name=", htmlspecialchars($row['name']), "\">", htmlspecialchars($row['name']), "</a></strong></p>";
        echo '</div>';

        $alt = ! $alt;
    }
    echo '</div>';
    echo '</div>';
}


// Show classes
$q = "SELECT id, name, description
  FROM classes
  WHERE namespaceid = {$namespace['id']}
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
  WHERE namespaceid = {$namespace['id']}
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
  WHERE namespaceid = {$namespace['id']} AND classid IS NULL AND interfaceid IS NULL
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


require_once 'foot.php';

