<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of docu.

Docu is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Docu is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with docu.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @package Viewer
* @author Josh Heidenreich
* @since 0.2
**/

require_once 'head.php';


$_GET['name'] = trim($_GET['name']);
if ($_GET['name'] == '') {
  echo "Invalid document specified";
  exit;
}

$name_sql = db_quote ($_GET['name']);
$q = "SELECT name, description FROM documents WHERE name LIKE {$name_sql}";
$res = db_query ($q);

if (! $row = db_fetch_assoc ($res)) {
  echo "Invalid document specified";
  exit;
}

$row['name'] = htmlspecialchars($row['name']);


echo "<h2>{$row['name']}</h2>";
echo '<br>';
echo process_inline($row['description']);


require_once 'foot.php';
?>
