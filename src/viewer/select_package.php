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
**/

require_once 'functions.php';


$_GET['id'] = (int) $_GET['id'];

if ($_GET['id'] == 0) {
  unset ($_SESSION['current_package']);
  header('Location: index.php');
  
} else {
  $_SESSION['current_package'] = $_GET['id'];
  header('Location: package.php?id=' . $_GET['id']);
}
?>
