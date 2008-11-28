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
$result = $outputter->check_layout('../processor/database.layout');
?>
<p>Done!</p>


<h3>Creating config files</h3>
<?php
$_POST['project_exclude'] = trim ($_POST['project_exclude']);

foreach ($_POST as $key => $val) {
  if ($key == 'project_exclude') continue;
  
  $_POST[$key] = addslashes($val);
}

echo '<p>Generating config files</p>';

$processor  = "<?php\n";
$processor .= "/*\n";
$processor .= " * This is the example docu processor configuration file\n";
$processor .= " * For more information about configuration, see\n";
$processor .= " * http://docu.sourceforge.net\n";
$processor .= " */\n";
$processor .= "\n";
$processor .= "ini_set('memory_limit', '-1');\n";
$processor .= "\n";
$processor .= "\n";
$processor .= "/* This should be the name of your project */\n";
$processor .= "\$dpgProjectName = '{$_POST['project_name']}';\n";
$processor .= "\n";
$processor .= "/* The project ID. Nessasary for multiple docs per database */\n";
$processor .= "\$dpqProjectID = 1;\n";
$processor .= "\n";
$processor .= "/* This should be the terms that your documentation is made available under\n";
$processor .= "   It will be shown in the footer of the viewer */\n";
$processor .= "\$dpgLicenseText = 'Documentation is made available under the \n";
$processor .= "  <a href=\"http://www.gnu.org/copyleft/fdl.html\">GNU Free Documentation License 1.2</a>.';\n";
$processor .= "\n";
$processor .= "/* List the outputters here.\n";
$processor .= "   Currently you can only have one instance of each outputter.\n";
$processor .= "   Use the outputter constants defined in the constants.php file. */\n";
$processor .= "\$dpgOutputters[] = OUTPUTTER_MYSQL;\n";
$processor .= "//\$dpgOutputters[] = OUTPUTTER_PGSQL;\n";
$processor .= "//\$dpgOutputters[] = OUTPUTTER_SQLITE;\n";
$processor .= "//\$dpgOutputters[] = OUTPUTTER_DEBUG;\n";
$processor .= "\n";
$processor .= "/* This should contain the outputter settings\n";
$processor .= "   The settings are an array, with one array for each outputter */\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_server'] = '{$_POST['database_server']}';\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_username'] = '{$_POST['database_user']}';\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_password'] = '{$_POST['database_password']}';\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_name'] = '{$_POST['database_name']}';\n";
$processor .= "\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_PGSQL]['database_server'] = '{$_POST['database_server']}';\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_PGSQL]['database_username'] = '{$_POST['database_user']}';\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_PGSQL]['database_password'] = '{$_POST['database_password']}';\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_PGSQL]['database_name'] = '{$_POST['database_name']}';\n";
$processor .= "\n";
$processor .= "\$dpgOutputterSettings[OUTPUTTER_SQLITE]['filename'] = '../output/docu.sqlite';\n";
$processor .= "\n";
$processor .= "/* This is the base directory that the parsing of your application should take place */\n";
$processor .= "\$dpgBaseDirectory = '{$_POST['project_base_dir']}';\n";
$processor .= "\n";
$processor .= "/* These are directories that should be excluded from the processing. */\n";
if (! $_POST['project_exclude']) $processor .= '//';
$processor .= "\$dpgExcludeDirectories = array('" . str_replace("\n", "', '", $_POST['project_exclude']) . "');\n";
$processor .= "\n";
$processor .= "/* These are the Javadoc tags that should cascade from their parent */\n";
$processor .= "\$dpgCascaseDocblockTags[] = '@author';\n";
$processor .= "\$dpgCascaseDocblockTags[] = '@since';\n";
$processor .= "?>\n";

$viewer  = "<?php\n";
$viewer .= "/*\n";
$viewer .= " * This is the example docu viewer configuration file\n";
$viewer .= " * For more information about configuration, see\n";
$viewer .= " * http://docu.sourceforge.net\n";
$viewer .= " */\n";
$viewer .= "\n";
$viewer .= "/* The project ID. Nessasary for multiple docs per database */\n";
$viewer .= "\$dvgProjectID = 1;\n";
$viewer .= "\n";
$viewer .= "/* The database engine to use in the viewer. Supported values are 'mysql', 'postgresql' and 'sqlite' */\n";
$viewer .= "\$dvgDatabaseEngine = 'mysql';\n";
$viewer .= "\n";
$viewer .= "/* This should contain the database settings\n";
$viewer .= "   The following are used for typical database engines (MySQL and PostgreSQL) */\n";
$viewer .= "\$dvgDatabaseSettings['server'] = '{$_POST['database_server']}';\n";
$viewer .= "\$dvgDatabaseSettings['username'] = '{$_POST['database_user']}';\n";
$viewer .= "\$dvgDatabaseSettings['password'] = '{$_POST['database_password']}';\n";
$viewer .= "\$dvgDatabaseSettings['name'] = '{$_POST['database_name']}';\n";
$viewer .= "\n";
$viewer .= "/* This setting is used by SQLite */\n";
$viewer .= "\$dvgDatabaseSettings['filename'] = '../output/docu.sqlite';\n";
$viewer .= "?>\n";

if (is_writable ('.')) {
  echo '<p>Saving config files</p>';
  file_put_contents('config.processor.php', $processor);
  file_put_contents('config.viewer.php', $viewer);
  
} else {
  echo '<p>Cant save files, outputting to the screen.</p>';
  
  echo '<p><strong>processor/config.processor.php</strong></p>';
  echo '<pre class="source">', htmlspecialchars ($processor), '</pre>';
  
  echo '<p><strong>viewer/config.viewer.php</strong></p>';
  echo '<pre class="source">', htmlspecialchars ($viewer), '</pre>';
}
?>
<p>Copy these files into the processor and viewer directories, and you are done!</p>

<?php
if (! is_writable ('.')) {
  echo '<p>Make sure to not include any whitespace before the &lt;?php or after the ?&gt; in the config files!</p>';
}
?>
Once you have the files config.processor.php and config.viewer.php ready, you can <a href="../processor/main.php">generate your documentation</a>.<p>

</div>
</body>
</html>
