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
 * Links to the main processor and the database sync tool
 *
 * @package Processor
 * @author Josh
 * @since 0.1
 **/
?>
<html>
<head>
  <title>Pelzini: processor</title>

  <style>
  body {
    font-family: sans-serif;
  }
  b {
    font-size: larger;
  }
  </style>
</head>
<body>

<h1>Pelzini</h1>

<p>This is the Pelzini processor. The following tasks are available:</p>

<p><b><a href="main.php">Regenerate documentation</a></b>
<br>Recreates the documentation by scanning the source files and creating the database and other output files.</p>

<p><b><a href="database_layout_sync.php">Update database structure</a></b>
<br>Ensures the actual database layout matches the database layout required by Pelzini.</p>

</body>
</html>
