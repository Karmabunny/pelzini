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
* Shows a list of reports and parser documents
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.2
* @see ParserDocuemnt
* @see viewer/authors_list.php
* @see viewer/tables_list.php
* @tag i18n-needed
**/

require_once 'head.php';


echo '<h2>More information</h2>';


$q = "SELECT name FROM documents ORDER BY name";
$res = db_query ($q);

if (db_num_rows ($res) > 0) {
  echo '<h3>Project documents</h3>';
  echo "<ul>\n";
  
  while ($row = db_fetch_assoc ($res)) {
    $url = htmlspecialchars(urlencode($row['name']));
    $html = htmlspecialchars($row['name']);
    
    echo "<li><a href=\"document.php?name={$url}\">{$html}</a></li>\n";;
  }
  
  echo "</ul>\n";
}
?>


<h3>Additional documents</h3>
<p><b><a href="class_tree.php">Class tree</a></b>
<br>Get a tree of all of the classes in this project</p>

<br>

<p><b><a href="authors_list.php">Authors list</a></b>
<br>Get a list of all of the authors of this project</p>

<br>

<p><b><a href="tables_list.php">Tables list</a></b>
<br>Get a list of all of the tables used by this project</p>

<br>

<p><b><a href="tags_list.php">Tags list</a></b>
<br>Get a list of all of the tags used by this project</p>


<?php
require_once 'foot.php';
?>
