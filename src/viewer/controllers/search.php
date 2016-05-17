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
 * Does a search of the database
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @tag i18n-done
 **/

require_once 'functions.php';
require_once 'search_functions.php';

$_GET['q'] = @trim($_GET['q']);
$_GET['path'] = @trim($_GET['path']);

// class::method -> redirect to function page
if (preg_match('/^([a-zA-Z0-9_]+)::([a-zA-Z0-9_]+)$/', $_GET['q'], $matches)) {
    $class = db_quote($matches[1]);
    $method = db_quote($matches[2]);
    $q = "SELECT functions.id
          FROM functions
          INNER JOIN classes ON functions.classid = classes.id
          WHERE classes.name = {$class}
            AND functions.name = {$method}
            AND functions.projectid = {$project['id']}
          LIMIT 2";
    $res = db_query($q);
    $num = db_num_rows($res);
    if ($num == 1) {
        $row = db_fetch_assoc($res);
        redirect('function?name=' . urlencode($matches[2]) . '&memberof=' . urlencode($matches[1]) . '&q=' . urlencode($_GET['q']));
    }
}

$skin['page_name'] = str(STR_SEARCH_TITLE);
require_once 'head.php';

if ($_GET['q'] == '' and $_GET['path'] == '') {
	echo '<p>You must specify a search term.</p>';
	require_once 'foot.php';
	exit;
}

$query = db_quote($_GET['q']);
$_GET['advanced'] = (int) $_GET['advanced'];
$results = false;

// Determine the match string
// #ITEM# will be replaced in the specific search query
// for for classes, #ITEM# will become classes.name
$match_string = "#ITEM# '";

$extra_where = '1';
if (!empty($_GET['path'])) {
    $path = db_quote($_GET['path']);
    $extra_where = "files.name LIKE CONCAT('%', {$path}, '%')";
}


echo "<img src=\"assets/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
echo "<span style=\"float: right;\">", str(STR_SHOW_HIDE_ALL), " &nbsp;</span>";

echo '<h2>', str(STR_SEARCH_TITLE), '</h2>';
echo '<p>', str(STR_YOU_SEARCHED_FOR, 'term', htmlspecialchars($_GET['q'])), '</p>';

// classes
if (@$_GET['advanced'] == 0 or @$_GET['classes'] == 'y') {
    $q = "SELECT classes.id, classes.name, classes.description, classes.extends, classes.abstract,
        files.name as filename, classes.fileid,
            IF(BINARY classes.name = {$query}, 1, 0) +
            IF(classes.name LIKE {$query}, 1, 0) +
            IF(classes.name LIKE CONCAT({$query}, '%'), 1, 0) +
        0 AS relevancy
    FROM classes
    INNER JOIN files ON classes.fileid = files.id
    WHERE classes.name LIKE CONCAT('%', {$query}, '%')
      AND classes.projectid = {$project['id']}
      AND {$extra_where}
    ORDER BY relevancy DESC, classes.name";

    $res = db_query ($q);
    $num = db_num_rows ($res);
    if ($num != 0) {
        $results = true;
        echo '<h3>', str(STR_CLASSES_RESULT, 'num', $num), '</h3>';

        $alt = false;
        echo '<div class="list">';
        while ($row = db_fetch_assoc ($res)) {
            $class = 'item';
            if ($alt) $class .= '-alt';

            echo "<div class=\"{$class}\">";
            echo "<img src=\"assets/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
            echo "<p><strong>", get_class_link($row['name'], $row['filename']), "</strong>";

            if ($row['extends'] != null) {
                echo " <small>extends ", get_class_link($row['extends']), "</small>";
            }

            if ($row['abstract'] == 1) {
                echo " <small>(abstract)</small>";
            }

            echo "<div class=\"content\">";
            echo delink_inline($row['description']);
            echo "</div>";
            echo "</div>";

            $alt = ! $alt;
        }
        echo '</div>';
    }
}

// interfaces
if (@$_GET['advanced'] == 0 or @$_GET['interfaces'] == 'y') {
    $q = "SELECT interfaces.id, interfaces.name, interfaces.description, interfaces.extends,
        files.name as filename, interfaces.fileid,
            IF(BINARY interfaces.name = {$query}, 1, 0) +
            IF(interfaces.name LIKE {$query}, 1, 0) +
            IF(interfaces.name LIKE CONCAT({$query}, '%'), 1, 0) +
        0 AS relevancy
    FROM interfaces
    INNER JOIN files ON interfaces.fileid = files.id
    WHERE interfaces.name LIKE CONCAT('%', {$query}, '%')
      AND interfaces.projectid = {$project['id']}
      AND {$extra_where}
    ORDER BY relevancy DESC, interfaces.name";

    $res = db_query ($q);
    $num = db_num_rows ($res);
    if ($num != 0) {
        $results = true;
        echo '<h3>', str(STR_INTERFACES_RESULT, 'num', $num), '</h3>';

        $alt = false;
        echo '<div class="list">';
        while ($row = db_fetch_assoc ($res)) {
            $class = 'item';
            if ($alt) $class .= '-alt';

            echo "<div class=\"{$class}\">";
            echo "<img src=\"assets/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
            echo "<p><strong>", get_interface_link($row['name'], $row['filename']), "</strong>";

            if ($row['extends'] != null) {
                echo " <small>extends ", get_interface_link($row['extends']), "</small>";
            }

            echo "<div class=\"content\">";
            echo delink_inline($row['description']);
            echo "</div>";
            echo "</div>";

            $alt = ! $alt;
        }
        echo '</div>';
    }
}

// functions
if (@$_GET['advanced'] == 0 or @$_GET['functions'] == 'y') {
    $q = "SELECT functions.id, functions.name, functions.description, functions.classid, functions.linenum,
        files.name as filename, functions.fileid, classes.name as class,
            IF(BINARY functions.name = {$query}, 1, 0) +
            IF(functions.name LIKE {$query}, 1, 0) +
            IF(functions.name LIKE CONCAT({$query}, '%'), 1, 0) +
        0 AS relevancy
    FROM functions
    INNER JOIN files ON functions.fileid = files.id
    LEFT JOIN classes ON functions.classid = classes.id
    WHERE functions.name LIKE CONCAT('%', {$query}, '%')
      AND functions.projectid = {$project['id']}
      AND {$extra_where}
    ORDER BY relevancy DESC, functions.name";
    $res = db_query ($q);
    $num = db_num_rows ($res);
    if ($num != 0) {
        $results = true;
        echo '<h3>', str(STR_FUNCTIONS_RESULT, 'num', $num), '</h3>';

        $alt = false;
        echo '<div class="list">';
        while ($row = db_fetch_assoc ($res)) {
            $class = 'item';
            if ($alt) $class .= '-alt';

            echo "<div class=\"{$class}\">";
            echo "<img src=\"assets/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";

            if ($row['class'] != null) {
                $link_text = $row['class'] . '::' . $row['name'];
            } else {
                $link_text = $row['name'];
            }
            echo "<p><strong>", get_function_link($row['class'], $row['name'], $link_text), "</strong>";

            echo "<div class=\"content\">";
            echo delink_inline($row['description']);
            echo "</div>";
            echo "</div>";

            $alt = ! $alt;
        }
        echo '</div>';
    }
}


// constants
if (@$_GET['advanced'] == 0 or @$_GET['constants'] == 'y') {
    $q = "SELECT constants.name, constants.description, files.name as filename, constants.fileid, constants.value,
            IF(BINARY constants.name = {$query}, 1, 0) +
            IF(constants.name LIKE {$query}, 1, 0) +
            IF(constants.name LIKE CONCAT({$query}, '%'), 1, 0) +
        0 AS relevancy
    FROM constants
    INNER JOIN files ON constants.fileid = files.id
    WHERE constants.name LIKE CONCAT('%', {$query}, '%')
      AND constants.projectid = {$project['id']}
      AND {$extra_where}
    ORDER BY relevancy DESC, constants.name";
    $res = db_query ($q);
    $num = db_num_rows ($res);
    if ($num != 0) {
        $results = true;
        echo '<h3>', str(STR_CONSTANTS_RESULT, 'num', $num), '</h3>';

        $alt = false;
        echo '<div class="list">';
        while ($row = db_fetch_assoc ($res)) {
            $row['name'] = htmlspecialchars($row['name']);
            $row['value'] = htmlspecialchars($row['value']);

            $class = 'item';
            if ($alt) $class .= '-alt';

            echo "<div class=\"{$class}\">";
            echo "<img src=\"assets/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
            echo "<p><strong>{$row['name']}</strong>";
            echo " = <strong>{$row['value']}</strong>";

            echo "<div class=\"content\">";
            echo delink_inline($row['description']);
            echo "</div>";
            echo "</div>";

            $alt = ! $alt;
        }
        echo '</div>';
    }
}


// documents
if (@$_GET['advanced'] == 0 or @$_GET['documents'] == 'y') {
    $q = "SELECT documents.name,
            IF(BINARY documents.name = {$query}, 1, 0) +
            IF(documents.name LIKE {$query}, 1, 0) +
            IF(documents.name LIKE CONCAT({$query}, '%'), 1, 0) +
        0 AS relevancy
    FROM documents
    WHERE documents.name LIKE CONCAT('%', {$query}, '%')
      AND documents.projectid = {$project['id']}
    ORDER BY relevancy DESC, documents.name";
    $res = db_query ($q);
    $num = db_num_rows ($res);
    if ($num != 0) {
        $results = true;
        echo '<h3>', str(STR_DOCUMENTS_RESULT, 'num', $num), '</h3>';

        $alt = false;
        echo '<div class="list">';
        while ($row = db_fetch_assoc ($res)) {
            $row['name'] = htmlspecialchars($row['name']);
            $url = htmlspecialchars(urlencode($row['name']));

            $class = 'item';
            if ($alt) $class .= '-alt';

            echo "<div class=\"{$class}\">";
            echo "<a href=\"document?name={$url}\">{$row['name']}</a>";
            echo "</div>";

            $alt = ! $alt;
        }
        echo '</div>';
    }
}


// source
if (@$_GET['advanced'] == 0 or @$_GET['source'] == 'y') {
    $source_results = search_source(@$_GET['q'], false, @$_GET['path']);
    if ($source_results) {
        $results = true;
    }
}


// no results
if (! $results) {
    echo "<p>Nothing was found!</p>";
}


require_once 'foot.php';
?>
