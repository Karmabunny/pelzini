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


?>
<html>
<head>
  <title>docu: processor</title>
  
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

<h1>Docu</h1>

<p>This is the docu processor. The following tasks are available:</p>

<p><b><a href="main.php">Regenerate documentation</a></b>
<br>Recreates the documentation by scanning the source files and creating the database and other output files.</p>

<p><b><a href="database_layout_sync.php">Update database structure</a></b>
<br>Ensures the actual database layout matches the database layout required by docu.</p>

</body>
</html>
