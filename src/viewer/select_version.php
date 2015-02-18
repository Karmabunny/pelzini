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
 * Selects a version as the 'current version'
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 **/

require_once 'functions.php';


$_GET['id'] = (int) $_GET['id'];

if ($_GET['id'] == 0) {
    unset ($_SESSION['current_version']);

} else {
    $_SESSION['current_version'] = $_GET['id'];
}

header('Location: ' . $_GET['redirect']);
?>
