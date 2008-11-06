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
  <link type="text/css" rel="stylesheet" href="install.css">
  <title>Docu installer</title>
  
  <script>
  function db_type_change (newval) {
    var node = document.getElementById ('database_admin');
    
    if (newval == 'root') {
      node.style.display = '';
    } else {
      node.style.display = 'none';
    }
  }
  </script>
</head>
<body>
<div class="main">
<form action="install_action.php" method="post">


<h1>Docu installer</h1>

<p>This is the docu installer. This installer will create a database, create all the nessasary tables,
and create config files for the viewer and the processor.</p>

<?php
if (! is_writable ('.')) {
  echo "<p class=\"error\">WARNING: The <code>install</code> directory is not writable.";
  echo "<br>Config files will be displayed on the screen rather than written to a file.</p>";
}
?>

<p>To install docu, fill in all the relevant options below.</p>

<p>You can also use this tool to upgrade docu.</p>



<h2>1. Database</h2>

<!-- Database type -->
<h3>Database creation</h3>
<p>There are three ways to set up the database.</p>

<p><b><label><input type="radio" name="database_type" value="created" onclick="db_type_change(this.value);">
Already created</label></b>
<br>Use this if you already have a database and have full permissions to use it.</p>

<p><b><label><input type="radio" name="database_type" value="user" onclick="db_type_change(this.value);">
Create a database</label></b>
<br>Use this if you need a database, but have nessasary permissions to create them yourself.</p>

<p><b><label><input type="radio" name="database_type" value="root" onclick="db_type_change(this.value);">
Admin access create a database</label></b>
<br>Use this if you need a database, and need administrator access to create the database.</p>


<!-- Database details -->
<h3>Database details</h3>
<p>You will need to enter your current database details, or the new database details</p>

<p><b>Server:</b>
<br><input type="text" name="database_server" value="localhost"></p>

<p><b>User:</b>
<br><input type="text" name="database_user" value=""></p>

<p><b>Password:</b>
<br><input type="text" name="database_password" value=""></p>

<p><b>Database name:</b>
<br><input type="text" name="database_name" value=""></p>


<!-- Root database details -->
<div id="database_admin" style="display: none;">
  <h3>Administrator account details</h3>
  <p>You will need to enter the administrator username and password</p>
  
  <p><b>User:</b>
  <br><input type="text" name="admin_user" value="root"></p>
  
  <p><b>Password:</b>
  <br><input type="text" name="admin_password" value=""></p>
</div>


<!-- Project details -->
<h2>2. Project details</h2>
<p>You now need to enter some details about your project.</p>

<p><b>Project name:</b>
<br><input type="text" name="project_name" value=""></p>

<p><b>Source directory</b>
<br><small>This can be specified as an absolute path, or as a path relative to the install location of
the processor. The default of <code>..</code> can be used if the processor will be put inside the source
directory of the project.</small>
<br><input type="text" name="project_base_dir" value=".."></p>

<p><b>Exclude directories</b>
<br><small>This should be a one-per-line listing of directories to exclude from the base directory,
relative to the base directory.</small>
<br><textarea name="project_exclude" rows="5" cols="40">
/processor
/viewer
/install
</textarea></p>


<!-- Install -->
<h2>3. Install docu!</h2>
<p>Please check the above details are correct, and then click 'Install'.</p>
<p><input type="submit" value="Install"></p>


</form>
</div>
</body>
</html>
