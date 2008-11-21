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


echo "<h2>Tables list</h2>";


$q = "SELECT name
  FROM item_tables
  GROUP BY name
  ORDER BY name";
$res = db_query ($q);

if (mysql_num_rows ($res) > 0) {
  echo "<p>These are the known tables used by this project.</p>";
  
  echo "<ul>";
  while ($row = mysql_fetch_assoc ($res)) {
    echo "<li><a href=\"table.php?name={$row['name']}\">{$row['name']}</a></li>";
  }
  echo "</ul>";
  
  
} else {
  echo "<p>No tables are known for this project.</p>";
}


require_once 'foot.php';
?>