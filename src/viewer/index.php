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
* @since 0.1
**/
require_once 'head.php';

?>


<h2><?= $project['name']; ?></h2>
<p>This is the documentation for <?= $project['name']; ?>.</p>



<?php
$q = "SELECT id, name FROM packages ORDER BY name";
$res = db_query($q);
if (db_num_rows ($res) > 0) {
  echo "<h3>Packages in this project</h3>";
  
  while ($row = db_fetch_assoc($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    echo "<p><a href=\"select_package.php?id={$row['id']}\">{$row['name']}</a></p>";
  }
}
?>


<?php
require_once 'foot.php';
?>
