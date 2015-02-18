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
 * Shows information about a specific function
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @see ParserFunction
 * @tag i18n-done
 **/

require_once 'functions.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
    $name = trim($_GET['name']);
    $name = db_escape ($name);
    $where = "functions.name LIKE '{$name}'";
} else {
    $where = "functions.id = {$id}";
}


$q = new SelectQuery();
$q->addFields('functions.id, functions.name, functions.description, files.name AS filename, functions.classid,
  classes.name AS class, functions.static, functions.final, functions.sinceid,
  functions.returntype, functions.returndescription');
$q->setFrom('functions');
$q->addInnerJoin('files ON functions.fileid = files.id');
$q->addLeftJoin('classes ON functions.classid = classes.id');
$q->addWhere($where);
$q->addSinceVersionWhere();

$q = $q->buildQuery();
$res = db_query ($q);

if (! $function = db_fetch_assoc ($res)) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo '<p>', str(STR_FUNC_INVALID), '</p>';
    require_once 'foot.php';
}

$skin['page_name'] = str(STR_FUNC_BROWSER_TITLE, 'name', $function['name']);
require_once 'head.php';


echo '<h2>', str(STR_FUNC_PAGE_TITLE, 'name', $function['name']), '</h2>';

echo process_inline($function['description']);


echo "<ul>";
echo '<li>', str(STR_FILE, 'filename', $function['filename']), '</li>';

if ($function['classid']) {
    echo '<li>', str(STR_FUNC_CLASS, 'name', $function['class']);

    if ($function['static']) echo ', ', str(STR_METHOD_STATIC);
    if ($function['final']) echo ', ', str(STR_METHOD_FINAL);

    echo '</li>';

} else if ($function['static']) {
    echo '<li>', str(STR_FUNC_STATIC), '</li>';
}

if ($function['sinceid']) {
    echo '<li>', str(STR_AVAIL_SINCE, 'version', get_since_version($function['sinceid'])), '</li>';
}
echo "</ul>";


// Usage
echo '<h3>', str(STR_FUNC_USAGE), '</h3>';
show_function_usage ($function['id']);


show_authors ($function['id'], LINK_TYPE_FUNCTION);
show_tables ($function['id'], LINK_TYPE_FUNCTION);


// Show Arguments
$q = "SELECT id, name, type, defaultvalue, description FROM arguments WHERE functionid = {$function['id']}";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<h3>', str(STR_FUNC_ARGUMENTS), '</h3>';

    echo "<ol class=\"spaced-list\">";
    while ($row = db_fetch_assoc ($res)) {
        $row['name'] = htmlspecialchars($row['name']);
        $row['type'] = htmlspecialchars($row['type']);
        $row['defaultvalue'] = htmlspecialchars($row['defaultvalue']);

        echo '<li>', get_object_link ($row['type']), " <strong>{$row['name']}</strong>";
        if ($row['defaultvalue']) echo " = {$row['defaultvalue']}";
        echo '<br>', process_inline ($row['description']);
        echo "</li>";
    }
    echo "</ol>\n";
}


// Return value
if ($function['returntype'] or $function['returndescription']) {
    $function['returntype'] = htmlspecialchars($function['returntype']);

    echo '<h3>', str(STR_FUNC_RETURN_VALUE), '</h3>';

    echo "<ul><li>";
    if ($function['returntype']) {
        echo get_object_link ($function['returntype']), '<br>';
    }

    echo process_inline ($function['returndescription']);
    echo "</li></ul>";
}


show_see_also ($function['id'], LINK_TYPE_FUNCTION);
show_tags ($function['id'], LINK_TYPE_FUNCTION);


require_once 'foot.php';
?>
