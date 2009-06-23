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
* Shows a specific document
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.2
* @see ParserDocument
* @tag i18n-done
**/


require_once 'functions.php';

$_GET['name'] = trim($_GET['name']);
$name_sql = db_quote($_GET['name']);
$q = "SELECT name, description FROM documents WHERE name LIKE {$name_sql}";
$res = db_query ($q);

if (! $doc = db_fetch_assoc ($res)) {
  require_once 'head.php';
  echo str(STR_INVALID_DOCUMENT);
  require_once 'foot.php';
  exit;
}

$doc['name'] = htmlspecialchars($doc['name']);


$skin['page_name'] = $doc['name'];
require_once 'head.php';


echo "<h2>{$doc['name']}</h2>";
echo '<br>';
echo process_inline($doc['description']);


require_once 'foot.php';
?>
