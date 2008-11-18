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
* This file redirects the user to the viewer, unless if a suitable config cannot be found
* in which case, the user gets redirected to the installer
**/


if (file_exists('viewer/viewer.config.php')) {
  header ('Location: viewer/');
  exit;
}

if (file_exists('viewer/config.php')) {
  header ('Location: viewer/');
  exit;
}

header ('Location: install/install.php');
exit;
?>
