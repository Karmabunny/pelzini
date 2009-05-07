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
* @package Viewer
* @author Josh Heidenreich
* @since 0.2
**/

$skin['page_name'] = 'Advanced search';
require_once 'head.php';
?>


<h2>Advanced search</h2>
<p>&nbsp;</p>

<form action="search.php" method="get">
  <input type="hidden" name="advanced" value="1">
  
  <p>
    <b>Search term:</b>
    <br><input type="text" name="q" value="<?= htmlspecialchars ($_GET['q']); ?>">
  </p>
  
  <p>&nbsp;</p>
  
  <p>
    <b>What to search:</b>
    <br><label><input type="checkbox" name="classes" value="y" checked> Classes</label>
    <br><label><input type="checkbox" name="functions" value="y" checked> Functions</label>
    <br><label><input type="checkbox" name="constants" value="y" checked> Constants</label>
    <br><label><input type="checkbox" name="source" value="y" checked> Source code</label>
  </p>
  
  <p>&nbsp;</p>
  
  <p><input type="submit" value="Search"></p>
</form>


<?php
require_once 'foot.php';
?>
