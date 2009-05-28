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
* Shows a list of all authors
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.2
* @tag i18n-done
**/

require_once 'head.php';


echo '<h2>', str(STR_AUTHOR_LIST_TITLE), '</h2>';


$q = "SELECT name
  FROM item_authors
  GROUP BY name
  ORDER BY name";
$res = db_query ($q);

if (db_num_rows ($res) > 0) {
  echo '<p>', str(STR_AUTHOR_LIST_INTRO), '</p>';
  
  echo "<ul>";
  while ($row = db_fetch_assoc ($res)) {
    echo "<li><a href=\"author.php?name={$row['name']}\">{$row['name']}</a></li>";
  }
  echo "</ul>";
  
  
} else {
  echo '<p>', str(STR_AUTHOR_LIST_NONE), '</p>';
}


require_once 'foot.php';
?>
