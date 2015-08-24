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


$sql_name = db_quote($_GET['name']);
$q = new SelectQuery();
$q->addFields('functions.id, functions.name, functions.namespace, functions.description,
  files.name AS filename, functions.linenum, functions.classid,
  classes.name AS class, interfaces.name AS interface, functions.static, functions.final, functions.sinceid,
  functions.returntype, functions.returndescription');
$q->setFrom('functions');
$q->addInnerJoin('files ON functions.fileid = files.id');
$q->addLeftJoin('classes ON functions.classid = classes.id');
$q->addLeftJoin('interfaces ON functions.interfaceid = interfaces.id');
$q->addWhere("functions.name = {$sql_name}");
$q->addProjectWhere();

if (isset($_GET['memberof'])) {
    $sql_name = db_quote($_GET['memberof']);
    $q->addWhere("(classes.name = {$sql_name} OR interfaces.name = {$sql_name})");
}

if (isset($_GET['file'])) {
    $sql_name = db_quote($_GET['file']);
    $q->addWhere("files.name = {$sql_name}");
}

$q = $q->buildQuery();
$res = db_query ($q);

if (db_num_rows($res) == 0) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo '<p>', str(STR_FUNC_INVALID), '</p>';
    require_once 'foot.php';
    
} else if (db_num_rows($res) > 1) {
	require_once 'head.php';
    echo '<h2>', str(STR_MULTIPLE_TITLE, 'NUM', db_num_rows($res), 'TYPE', strtolower(str(STR_FUNCTIONS))), '</h2>';
    
    echo '<div class="list">';
    while ($row = db_fetch_assoc($res)) {
    	$name_parts = array();
    	$name_parts[] = str(STR_IN_FILE, 'VAL', $row['filename']);
    	
    	$url = 'function?name=' . htmlspecialchars($_GET['name']) . '&file=' . urlencode($row['filename']);
    	
    	if ($row['class']) {
    		$url .= '&memberof=' . urlencode($row['class']);
    		$name_parts[] = str(STR_IN_CLASS, 'VAL', $row['class']);
    	} else if ($row['interface']) {
    		$url .= '&memberof=' . urlencode($row['interface']);
    		$name_parts[] = str(STR_IN_INTERFACE, 'VAL', $row['class']);
    	}
    	
    	echo '<div class="item">';
		echo '<p><strong><a href="', htmlspecialchars($url), '">', htmlspecialchars($row['name']), '</a></strong></p>';
		echo '<pre>', ucfirst(implode(', ', $name_parts)), '</pre>';
		echo '</div>';
    }
    echo '</div>';
    
    require_once 'foot.php';
    
} else {
	$function = db_fetch_assoc($res);
}

$skin['page_name'] = str(STR_FUNC_BROWSER_TITLE, 'name', $function['name']);
require_once 'head.php';

echo '<h2>', str(STR_FUNC_PAGE_TITLE, 'name', $function['name']), '</h2>';

echo process_inline($function['description']);


echo "<ul>";
$line = $function['linenum'];
$start = '';
if ($line > 5) {
	$start = '#src-lines-' . ($line - 5);
}
echo '<li>File: <a href="file?name=', urlencode($function['filename']), '">', htmlspecialchars($function['filename']), '</a>, line <a href="file_source?name=', urlencode($function['filename']), "&amp;highlight={$line}{$start}\">{$line}</a></li>";

if ($function['namespace'] != null) {
    echo '<li>', str(STR_NAMESPACE, 'name', $function['namespace']), '</li>';
}

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
$q = "SELECT id, name, type, defaultvalue, description FROM arguments WHERE functionid = {$function['id']} ORDER BY id";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<h3>', str(STR_FUNC_ARGUMENTS), '</h3>';

    echo "<ol class=\"spaced-list\">";
    while ($row = db_fetch_assoc ($res)) {
        $row['name'] = htmlspecialchars($row['name']);
        $row['type'] = htmlspecialchars($row['type']);

        echo '<li>', get_object_link ($row['type']), " <strong>{$row['name']}</strong>";
        if ($row['defaultvalue'] !== null) {
            if ($row['defaultvalue'] == '') $row['defaultvalue'] = "''";
            $row['defaultvalue'] = htmlspecialchars($row['defaultvalue']);
            echo " = {$row['defaultvalue']}";
        }
        echo '<br>', process_inline ($row['description']);
        echo "</li>";
    }
    echo "</ol>\n";
}


// Show throws
$q = "SELECT exception, description FROM throws WHERE functionid = {$function['id']} ORDER BY id";
$res = db_query($q);
if (db_num_rows($res) > 0) {
    echo '<h3>', str(STR_FUNC_THROWS), '</h3>';

    echo "<ol class=\"spaced-list\">";
    while ($row = db_fetch_assoc ($res)) {
        $row['exception'] = htmlspecialchars($row['exception']);

        echo '<li>', get_object_link($row['exception']);
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
