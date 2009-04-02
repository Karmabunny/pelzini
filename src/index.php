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
* This file redirects the user to the viewer, unless if a suitable config cannot be found
* in which case, the user gets redirected to the installer
*
* @since 0.2
* @author Josh
* @package Viewer
**/

$dir = dirname(__FILE__);
if (substr($dir, -1, 1) != '/') $dir .= '/';


if (file_exists($dir . 'viewer/config.viewer.php')) {
  header ('Location: viewer/');
  exit;
}

if (file_exists($dir . 'viewer/config.php')) {
  header ('Location: viewer/');
  exit;
}

header ('Location: install/install.php');
exit;
?>
