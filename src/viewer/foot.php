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
* The viewer footer
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @tag i18n-done
**/

$generated = strtotime($project['dategenerated']);
$generated = date('l, jS F, Y', $generated) . ' at ' . date('h:i a', $generated);

?>



<!-- Main content ends here -->

</td>
</tr>
</table>

<table class="footer">
  <tr>
    <td align="left" style="width: 20em;">
      <?= str (POWERED_BY, 'version', DOCU_VERSION); ?>
    </td>
    
    <td align="center">
      <?= $project['license']; ?>
      <br>
      <?= str (DATE_GENERATED, 'date', $generated); ?>
    </td>
    
    <td align="right" style="width: 20em;">
      <a href="http://docu.sourceforge.net">
        <img src="images/docs_pelzini.png" width="80" height="15">
      </a>
    </td>
  </tr>
</table>

</body>
</html>
