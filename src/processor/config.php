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



ini_set ('memory_limit', '-1');

/* This should be the name of your project */
$dpgProjectName = 'Docu';

/* This should be the terms that your documentation is made available under
   It will be shown in the footer of the viewer */
$dpgLicenseText = 'Documentation is made available under the ' .
  '<a href="http://www.gnu.org/copyleft/fdl.html">GNU Free Documentation License 1.2</a>.';

/* List the outputters here.
   Currently you can only have one instance of each outputter.
   Use the outputter constants defined in the constants.php file. */
$dpgOutputters[] = OUTPUTTER_MYSQL;
$dpgOutputters[] = OUTPUTTER_DEBUG;

/* This should contain the outputter settings
   The settings are an array, with one array for each outputter */
$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_server'] = 'localhost';
$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_username'] = 'josh';
$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_password'] = 'password';
$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_name'] = 'docu';

/* This is the base directory that the parsing of your application should take place */
$dpgBaseDirectory = '..';
?>
