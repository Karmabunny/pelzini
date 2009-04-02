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
* The home page of the viewer
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
**/
require_once 'head.php';

?>


<h2><?= $project['name']; ?></h2>
<p>This is the documentation for <?= $project['name']; ?>.</p>


<h3>Seach code</h3>
<form action="search.php" method="get">
  <input type="hidden" name="advanced" value="0">
  <input type="text" name="q">
  <input type="submit" value="Search">
</form>
<p><a href="advanced_search.php">Advanced search</a></p>



<?php
require_once 'foot.php';
?>
