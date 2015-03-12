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
 * Shows the source of a specific file
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @see ParserFile
 * @tag i18n-done
 **/


require_once 'functions.php';
require_once 'geshi/geshi.php';


// Get the details of this file
$sql_name = db_quote($_GET['name']);
$q = "SELECT name, description, source FROM files WHERE name = {$sql_name} AND projectid = {$project['id']} LIMIT 1";
$res = db_query ($q);
$file = db_fetch_assoc ($res);


if ($file == null) {
    require_once 'head.php';
    echo '<h2>', str(STR_ERROR_TITLE), '</h2>';
    echo '<p>', str(STR_FILE_INVALID), '</p>';
    require_once 'foot.php';
    return;
}


$skin['page_name'] = str(STR_FILE_SOURCE_BROWSER_TITLE, 'name', $file['name']);
require_once 'head.php';

echo '<h2>', str(STR_FILE_SOURCE_PAGE_TITLE, 'name', $file['name']), '</h2>';
echo process_inline($file['description']);

// Set up code highlight settings
$geshi = new GeSHi($file['source'], 'php');
$geshi->enable_classes();
$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
$geshi->set_line_style('background: #f8f8f8;');

// Line highlighting
if ($_GET['highlight']) {
    $parts = explode('-', $_GET['highlight']);
    if (count($parts) == 1) {
        $geshi->highlight_lines_extra($parts);
    } else if (count($parts) == 2) {
        $geshi->highlight_lines_extra(range($parts[0], $parts[1]));
    }
}

// Output highlighted code
echo '<style type="text/css">', $geshi->get_stylesheet(), '</style>';
echo $geshi->parse_code();

require_once 'foot.php';

