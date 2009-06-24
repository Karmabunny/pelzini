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
* @tag i18n-done
**/

require_once 'head.php';


echo '<h2>', str(STR_MORE_INFO), '</h2>';


$q = "SELECT name FROM documents ORDER BY name";
$res = db_query ($q);

if (db_num_rows ($res) > 0) {
  echo '<h3>', str(STR_PROJECT_DOCS), '</h3>';
  echo "<ul>\n";
  
  while ($row = db_fetch_assoc ($res)) {
    $url = htmlspecialchars(urlencode($row['name']));
    $html = htmlspecialchars($row['name']);
    
    echo "<li><a href=\"document.php?name={$url}\">{$html}</a></li>\n";;
  }
  
  echo "</ul>\n";
}
?>


<h3><?= str(STR_ADDITIONAL_DOCS); ?></h3>
<p><b><a href="class_tree.php"><?= str(STR_CLASS_TREE_TITLE); ?></a></b>
<br><?= str(STR_CLASS_TREE_DESC); ?></p>

<br>

<p><b><a href="authors_list.php"><?= str(STR_AUTHOR_LIST_TITLE); ?></a></b>
<br><?= str(STR_AUTHOR_LIST_DESC); ?></p>

<br>

<p><b><a href="tables_list.php"><?= str(STR_TABLE_LIST_TITLE); ?></a></b>
<br><?= str(STR_TABLE_LIST_DESC); ?></p>

<br>

<p><b><a href="tags_list.php"><?= str(STR_TAG_LIST_TITLE); ?></a></b>
<br><?= str(STR_TAG_LIST_DESC); ?></p>

<?php
require_once 'foot.php';
?>
