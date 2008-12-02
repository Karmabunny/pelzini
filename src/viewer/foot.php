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

$generated = strtotime($project['dategenerated']);
$generated = date('l, jS F, Y', $generated);

$colours = array ('red', 'blue', 'green', 'orange');
$colour = $colours[array_rand ($colours)];
?>
</td>
</tr>
</table>

<table class="footer">
  <tr>
    <td align="left" style="width: 20em;">
      Powered by <a href="http://docu.sourceforge.net">docu</a>, version <?= DOCU_VERSION; ?>
    </td>
    
    <td align="center">
      <?= $project['license']; ?>
      <br>
      Generated: <?= $generated; ?>
    </td>
    
    <td align="right" style="width: 20em;">
      <a href="http://docu.sourceforge.net">
        <img src="images/docs_by_docu_<?= $colour; ?>.png" width="80" height="15">
      </a>
    </td>
  </tr>
</table>

</body>
</html>
