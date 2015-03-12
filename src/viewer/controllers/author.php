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
 * Shows information about a specific author
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.2
 * @tag i18n-done
 **/

$_GET['name'] = trim($_GET['name']);
if ($_GET['name'] == '') {
    require_once 'head.php';
    echo "Invalid author specified";
    require_once 'foot.php';
    exit;
}

$name_sql = db_quote ($_GET['name']);

$skin['page_name'] = str(STR_AUTHOR_PAGE_TITLE, 'name', $_GET['name']);
require_once 'head.php';




echo '<h2>', str(STR_AUTHOR_TITLE, 'name', $_GET['name']), '</h2>';


// Show files
$q = "SELECT files.id, files.name, item_authors.description
  FROM files
  INNER JOIN item_authors ON item_authors.linktype = " . LINK_TYPE_FILE . " AND item_authors.linkid = files.id
  WHERE item_authors.name = {$name_sql}
  ORDER BY files.name";
$res = db_query ($q);
if (db_num_rows($res) > 0) {
    echo '<h3>', str(STR_FILES), '</h3>';

    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
        $class = 'item';
        if ($alt) $class .= '-alt';

        // output
        echo "<div class=\"{$class}\">";
        echo "<p><i>{$row['action']}</i> <strong>", get_file_link($row['name']), "</strong></p>";
        echo process_inline($row['description']);
        echo '</div>';

        $file_ids[] = $row['id'];
        $alt = ! $alt;
    }
    echo '</div>';
}


// Show classes
$q = "SELECT classes.id, classes.name, item_authors.description
  FROM classes
  INNER JOIN item_authors ON item_authors.linktype = " . LINK_TYPE_CLASS . " AND item_authors.linkid = classes.id
  WHERE item_authors.name = {$name_sql}
  ORDER BY classes.name";
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
        echo "<p><i>{$row['action']}</i> <strong>", get_class_link($row['name']), "</strong></p>";
        echo process_inline($row['description']);
        echo '</div>';

        $alt = ! $alt;
    }
    echo '</div>';
}


// Show functions
$q = "SELECT functions.id, functions.name, item_authors.description, classes.name AS classname,
      interfaces.name AS interfacename
  FROM functions
  INNER JOIN item_authors ON item_authors.linktype = " . LINK_TYPE_FUNCTION . " AND item_authors.linkid = functions.id
  LEFT JOIN classes ON functions.classid = classes.id
  LEFT JOIN interfaces ON functions.interfaceid = interfaces.id
  WHERE item_authors.name = {$name_sql}
  ORDER BY interfacename, classname, functions.name";
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

        $classname = null;
        if ($row['classname']) $classname = $row['classname'];
        if ($row['interfacename']) $classname = $row['interfacename'];

        echo "<p><i>{$row['action']}</i> <strong>", get_function_link($classname, $row['name']), "</strong> ";
        if ($row['classname']) echo str(STR_FROM_CLASS, 'class', get_object_link($row['classname']));
        if ($row['interfacename']) echo str(STR_FROM_INTERFACE, 'interface', get_object_link($row['interfacename']));
        echo "</p>";

        echo process_inline($row['description']);
        echo "</div>";

        $alt = ! $alt;
    }
    echo '</div>';
}


require_once 'foot.php';
?>
