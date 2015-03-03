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


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
    $name = trim($_GET['name']);
    $name = db_escape ($name);
    $where = "name LIKE '{$name}'";
} else {
    $where = "id = {$id}";
}


// Get the details of this file
$q = "SELECT name, description, source FROM files WHERE {$where} LIMIT 1";
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


// Line highlighting
if ($_GET['highlight']) {
    $parts = explode('-', $_GET['highlight']);

    if (count($parts) == 1) {
        $highlight_begin = $parts[0];
        $highlight_end = $parts[0];

    } else if (count($parts) == 2) {
        $highlight_begin = $parts[0];
        $highlight_end = $parts[1];
    }
}

// Keyword highlighting
if ($_GET['keyword']) {
    $keyword_search = htmlspecialchars($_GET['keyword']);
    $keyword_search = '/(' . preg_quote($keyword_search, '/'). ')/i';
}


// Prepare source for display
$source = highlight_string($file['source'], true);
$source = trim($source);
$source = str_replace(["\n", "\r"], '', $source);
$source = preg_split('/<br\s*\/?>/', '<br />' . $source);
unset($source[0]);

$num = count($source);
$cols = strlen($num);


echo "<table><tr>";

echo '<td><pre>';
foreach ($source as $num => $line) {
    echo "<a name=\"line{$num}\"></a>", str_pad($num, $cols, ' ', STR_PAD_LEFT) . "\n";

    if ($num == $highlight_begin) {
        $lines .= '<em class="highlight">';
    }

    if ($keyword_search) {
        $line = preg_replace($keyword_search, "<strong class=\"highlight\">\$1</strong>", $line);
    }

    $lines .= $line;

    if ($num == $highlight_end) {
        $lines .= '</em>';
    }

    $lines .= "\n";
}
echo '</pre></td>';

echo '<td><pre class="source">', $lines, '</pre></td>';

echo '</tr></table>';


require_once 'foot.php';
?>
