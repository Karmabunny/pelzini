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


function err ($msg) {
  global $has_errors;
  $has_errors = true;
  
  echo "<p class=\"error\">ERROR: {$msg}</p>";
}

?>
<html>
<head>
  <link type="text/css" rel="stylesheet" href="install.css">
  <title>Docu installer</title>
</head>
<body>
<div class="main">

<h1>Docu installer</h1>


<h3>Checking input is alright</h3>
<?php
if (! $_POST['database_type']) err ('No database creation type specified');
if (! $_POST['database_server']) err ('No database server specified');
if (! $_POST['database_user']) err ('No database user specified');
if (! $_POST['database_password']) err ('No database password specified');
if (! $_POST['database_name']) err ('No database name specified');

if ($_POST['database_type'] == 'root') {
  if (! $_POST['admin_user']) err ('No admin user specified');
  if (! $_POST['admin_password']) err ('No admin password specified');
}

if (! $_POST['project_name']) err ('No project name specified');
if (! $_POST['project_base_dir']) err ('No project base directory specified');

if ($has_errors) {
  echo '<p>Please click "back" on your browser.<p>';
  exit;
}
?>
<p>Everything seems alright</p>


<h3>Creating database</h3>
<?php
switch ($_POST['database_type']) {
  case 'created':
    echo '<p>Database already exists. Nothing to do.</p>';
    break;
    
    
  case 'user':
    echo '<p>Database needs creation.</p>';
    break;
    
    
  case 'root':
    echo '<p>Database needs creation by admin user.</p>';
    break;
}
?>
<p>Done!</p>


<h3>Populating database</h3>
<?php
// todo
?>
<p>Done!</p>


<h3>Creating config files</h3>
<?php
// todo
?>
<p>Done!</p>


</div>
</body>
</html>
