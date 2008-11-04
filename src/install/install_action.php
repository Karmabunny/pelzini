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

function fatal ($msg) {
  echo "<p class=\"error\">ERROR: {$msg}</p>";
  exit;
}



foreach ($_POST as $key => $value) {
  $_POST[$key] = trim ($_POST[$key]);
  $_POST[$key] = str_replace(array("\r\n", "\r"), "\n", $_POST[$key]);
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


<h3>Checking input</h3>
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
    echo '<p>Checking database exists.</p>';
    
    $db = @mysql_connect($_POST['database_server'], $_POST['database_user'], $_POST['database_password']);
    if (! $db) fatal('Unable to connect to database<br>MySQL reported: ' . mysql_error());
    echo '<p>Connection successful.</p>';
    
    $res = mysql_select_db($_POST['database_name']);
    if (! $res) fatal('Database does not exist<br>MySQL reported: ' . mysql_error());
    echo '<p>Database found.</p>';
    
    mysql_close($db);
    break;
    
    
  case 'user':
    echo '<p>Database needs creation.</p>';
    
    $db = @mysql_connect($_POST['database_server'], $_POST['database_user'], $_POST['database_password']);
    if (! $db) fatal('Unable to connect to database<br>MySQL reported: ' . mysql_error());
    echo '<p>Connection successful.</p>';
    
    $res = mysql_query("CREATE DATABASE IF NOT EXISTS {$_POST['database_name']}");
    if (! $res) fatal('Unable to create database.<br>MySQL reported: ' . mysql_error());
    echo '<p>Database created.</p>';
    
    $res = mysql_query("GRANT ALL ON {$_POST['database_name']}.* TO '{$_POST['database_user']}'");
    if (! $res) fatal('Unable to grant permissions.<br>MySQL reported: ' . mysql_error());
    echo '<p>Permissions granted.</p>';
    
    mysql_close($db);
    break;
    
    
  case 'root':
    echo '<p>Database needs creation by admin user.</p>';
    
    $db = @mysql_connect($_POST['database_server'], $_POST['admin_user'], $_POST['admin_password']);
    if (! $db) fatal('Unable to connect to database<br>MySQL reported: ' . mysql_error());
    echo '<p>Connection successful.</p>';
    
    $res = mysql_query("CREATE DATABASE IF NOT EXISTS {$_POST['database_name']}");
    if (! $res) fatal('Unable to create database.<br>MySQL reported: ' . mysql_error());
    echo '<p>Database created.</p>';
    
    $res = mysql_query("GRANT ALL ON {$_POST['database_name']}.* TO '{$_POST['database_user']}'");
    if (! $res) fatal('Unable to grant permissions.<br>MySQL reported: ' . mysql_error());
    echo '<p>Permissions granted.</p>';
    
    mysql_close($db);
    break;
}
?>
<p>Done!</p>


<h3>Populating database</h3>
<?php
$db = @mysql_connect($_POST['database_server'], $_POST['database_user'], $_POST['database_password']);
if (! $db) fatal('Unable to connect to database<br>MySQL reported: ' . mysql_error());
echo '<p>Connection successful.</p>';

$res = mysql_select_db($_POST['database_name']);
if (! $res) fatal('Database does not exist<br>MySQL reported: ' . mysql_error());
echo '<p>Database found.</p>';

require_once '../processor/functions.php';
require_once '../processor/constants.php';

$outputter = new MysqlOutputter(
  $_POST['database_user'],
  $_POST['database_password'],
  $_POST['database_server'],
  $_POST['database_name']
);

$_GET['action'] = 1;
$_GET['nopre'] = 1;
$result = $outputter->check_layout('../mysql.layout');
?>
<p>Done!</p>


<h3>Creating config files</h3>
<?php
$vars = $_POST;
$vars['project_exclude'] = 'array("' . str_replace("\n", '", "', $vars['project_exclude']) . '")';

echo '<p>Generating config files</p>';
$processor = template_file('example.config.processor.php', $vars);
$viewer = template_file('example.config.viewer.php', $vars);

if (is_writable ('.')) {
  echo '<p>Saving config files</p>';
  file_put_contents('config.processor.php', $processor);
  file_put_contents('config.viewer.php', $viewer);
  
} else {
  echo '<p>Cant save files, outputting to the screen.</p>';
  
  echo '<p><strong>processor/config.php</strong></p>';
  echo '<pre class="source">', htmlspecialchars ($processor), '</pre>';
  
  echo '<p><strong>viewer/config.php</strong></p>';
  echo '<pre class="source">', htmlspecialchars ($viewer), '</pre>';
}
?>
<p>Done!</p>


</div>
</body>
</html>
<?php
/**
* Returns a string
**/
function template_file($template, $vars) {
  $content = file_get_contents($template);
  
  foreach ($vars as $name => $value) {
    $content = str_replace ('{{' . $name . '}}', $value, $content);
  }
  
  return $content;
}
?>
