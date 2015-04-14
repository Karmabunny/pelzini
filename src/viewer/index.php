<?php
/*
Copyright 2015 Josh Heidenreich

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


require_once 'constants.php';
require_once 'tree.php';
require_once 'select_query.php';
require_once 'i18n.php';
require_once 'functions.php';


// Load configs
if (file_exists('config.viewer.php')) {
    require_once 'config.viewer.php';
} else {
    echo '<h1>Error</h1>';
    echo '<p>Unable to find required configuration file "config.viewer.php".';
    echo '<br>Please configure the Pelzini viewer. For more information, see:';
    echo '<br><a href="https://github.com/Karmabunny/pelzini">https://github.com/Karmabunny/pelzini</a></p>';
    exit;
}

// Complain if no valid db engine
if ($dvgDatabaseEngine == null or !file_exists("database_{$dvgDatabaseEngine}.php")) {
    echo '<h1>Error</h1>';
    echo '<p>Invalid or missing config option "$dvgDatabaseEngine".';
    echo '<br>Please configure the Pelzini viewer. For more information, see:';
    echo '<br><a href="https://github.com/Karmabunny/pelzini">https://github.com/Karmabunny/pelzini</a></p>';
    exit;
}


// Load the database
require_once "database_{$dvgDatabaseEngine}.php";
db_connect($dvgDatabaseSettings);
unset($dvgDatabaseEngine, $dvgDatabaseSettings);

session_start();

if (get_magic_quotes_gpc()) {
    $_POST = fix_magic_quotes($_POST);
    $_GET = fix_magic_quotes($_GET);
}

// Load language. English is always loaded because the other language only replaces the
// english strings, so if strings are missing, the english ones will be used instead.
loadLanguage('english');
if ($dvgLanguage and $dvgLanguage != 'english') {
    loadLanguage($dvgLanguage);
}
unset($dvgLanguage);

// Load project details
if (isset($_SESSION['current_project'])) {
    $q = "SELECT id, name, license, dategenerated FROM projects WHERE id = {$_SESSION['current_project']} LIMIT 1";
} else {
    $q = "SELECT id, name, license, dategenerated FROM projects WHERE name != '' AND code != '' ORDER BY id LIMIT 1";
}

$res = db_query($q);
$project = db_fetch_assoc($res);
unset($q, $res);


$parts = explode('/', trim(@$_GET['_uri'], ' /'));
$controller = array_shift($parts);
$method = array_shift($parts);

$controller = preg_replace('/[^_a-zA-Z0-9]/', '', $controller);
$method = preg_replace('/[^_a-zA-Z0-9]/', '', $method);

if ($controller == '') {
	$controller = 'index';
}
if ($method == '') {
	$method = 'index';
}

include_once 'controllers/' . $controller . '.php';


/**
* TODO: The controllers should actually be classes
**/
//$inst = new $controller;
//call_user_func_args(array($inst, $method), $args);

