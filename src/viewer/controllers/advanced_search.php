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
 * @tag i18n-done
 **/

require_once 'functions.php';

$skin['page_name'] = str(STR_ADV_SEARCH_TITLE);
require_once 'head.php';
?>


<h2><?php echo str(STR_ADV_SEARCH_TITLE); ?></h2>
<p>&nbsp;</p>

<form action="search" method="get">
  <input type="hidden" name="advanced" value="1">

  <p>
    <b><?php echo str(STR_SEARCH_TERM); ?></b>
    <br><input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q']); ?>">
  </p>

  <p>&nbsp;</p>

  <p>
    <b><?php echo str(STR_WHAT_SEARCH); ?></b>
    <br><label><input type="checkbox" name="classes" value="y" checked> <?php echo str(STR_CLASSES); ?></label>
    <br><label><input type="checkbox" name="functions" value="y" checked> <?php echo str(STR_FUNCTIONS); ?></label>
    <br><label><input type="checkbox" name="constants" value="y" checked> <?php echo str(STR_CONSTANTS); ?></label>
    <br><label><input type="checkbox" name="source" value="y" checked> <?php echo str(STR_SOURCE_CODE); ?></label>
  </p>

  <p>&nbsp;</p>

  <p>
    <b><?php echo str(STR_SEARCH_OPTIONS); ?></b>
    <br><label><input type="checkbox" name="case_sensitive" value="y"> <?php echo str(STR_CASE_SENSITIVE); ?></label>
  </p>

  <p>&nbsp;</p>

  <p><input type="submit" value="<?php echo str(STR_SEARCH_GO_BTN); ?>"></p>
</form>


<?php
require_once 'foot.php';
?>
