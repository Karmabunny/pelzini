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
    $class = db_escape($matches[1]);
    $method = db_escape($matches[2]);
    $q = "SELECT functions.id
          FROM functions
          INNER JOIN classes ON functions.classid = classes.id
          WHERE classes.name = '{$class}'
            AND functions.name = '{$method}'
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

$query = db_escape($_GET['q']);
$_GET['advanced'] = (int) $_GET['advanced'];
$results = false;

// Determine the match string
// #ITEM# will be replaced in the specific search query
// for for classes, #ITEM# will become classes.name
$match_string = "#ITEM# LIKE '%{$query}%'";
if (!empty($_GET['case_sensitive'])){
	$match_string = "BINARY #ITEM# LIKE '%{$query}%'";
}

$extra_where = '1';
if (!empty($_GET['path'])) {
    $path = db_escape($_GET['path']);
    $extra_where = "files.name LIKE '%{$path}%'";
}


echo "<img src=\"assets/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
echo "<span style=\"float: right;\">", str(STR_SHOW_HIDE_ALL), " &nbsp;</span>";

echo '<h2>', str(STR_SEARCH_TITLE), '</h2>';
echo '<p>', str(
    !empty($_GET['case_sensitive']) ? STR_YOU_SEARCHED_FOR_CASE : STR_YOU_SEARCHED_FOR,
    'term', htmlspecialchars($_GET['q'])
), '</p>';

// classes
if (@$_GET['advanced'] == 0 or @$_GET['classes'] == 'y') {
    $this_match_string = str_replace('#ITEM#', 'classes.name', $match_string);
    $q = "SELECT classes.id, classes.name, classes.description, classes.extends, classes.abstract, files.name as filename, classes.fileid
    FROM classes
    INNER JOIN files ON classes.fileid = files.id
    WHERE {$this_match_string}
      AND classes.projectid = {$project['id']}
      AND {$extra_where}
    ORDER BY IF(classes.name = '{$query}', 1, 2), classes.name";

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
            echo "<br><small>From ", get_file_link($row['filename']), "</small></div>";
            echo "</div>";

            $alt = ! $alt;
        }
        echo '</div>';
    }
}


// functions
if (@$_GET['advanced'] == 0 or @$_GET['functions'] == 'y') {
    $this_match_string = str_replace('#ITEM#', 'functions.name', $match_string);
    $q = "SELECT functions.id, functions.name, functions.description, functions.classid, functions.linenum, files.name as filename, functions.fileid, classes.name as class
    FROM functions
    INNER JOIN files ON functions.fileid = files.id
    LEFT JOIN classes ON functions.classid = classes.id
    WHERE {$this_match_string}
      AND functions.projectid = {$project['id']}
      AND {$extra_where}
    ORDER BY functions.name";
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
            echo "<p><strong>", get_function_link($row['class'], $row['name']), "</strong>";

            if ($row['class'] != null) {
                echo " <small>from class ", get_class_link($row['class']), "</small>";
            }

            echo "<div class=\"content\">";
            echo delink_inline($row['description']);
            echo "<br><small>From ", get_source_link($row['filename'], $row['linenum']), "</small></div>";
            echo "</div>";

            $alt = ! $alt;
        }
        echo '</div>';
    }
}


// constants
if (@$_GET['advanced'] == 0 or @$_GET['constants'] == 'y') {
    $this_match_string = str_replace('#ITEM#', 'constants.name', $match_string);
    $q = "SELECT constants.name, constants.description, files.name as filename, constants.fileid, constants.value
    FROM constants
    INNER JOIN files ON constants.fileid = files.id
    WHERE {$this_match_string}
      AND constants.projectid = {$project['id']}
      AND {$extra_where}
    ORDER BY constants.name";
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
            echo "<br><small>From ", get_file_link($row['filename']), "</small></div>";
            echo "</div>";

            $alt = ! $alt;
        }
        echo '</div>';
    }
}


// source
if (@$_GET['advanced'] == 0 or @$_GET['source'] == 'y') {
    $results = search_source(@$_GET['q'], @$_GET['case_sensitive'], @$_GET['path']);
}


// no results
if (! $results) {
    echo "<p>Nothing was found!</p>";
}


require_once 'foot.php';
?>
