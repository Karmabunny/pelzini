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

$q = "SELECT id, code, name FROM projects WHERE name != '' AND code != '' ORDER BY name";
$res = db_query($q);

if (db_num_rows($res) == 0) {
    die('No projects found in database');

} else if (db_num_rows($res) == 1) {
    // Only one? Redirect
    $row = db_fetch_assoc($res);
    header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/' . $row['code'] . '/');
    exit;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Select project</title>

    <base href="<?php echo htmlspecialchars(dirname($_SERVER['SCRIPT_NAME']) . '/'); ?>">

    <link href="assets/style.css" rel="stylesheet" type="text/css">
    <script src="assets/functions.js" type="text/javascript"></script>
    <link href="assets/favicon.ico" rel="icon" type="image/vnd.microsoft.icon" >
</head>
<body>

<div class="header">
  <h1 style="text-align: center;">Select project</h1>
</div>

<div class="project-selector">
    <?php
    while ($row = db_fetch_assoc($res)) {
        echo "<a href=\"{$row['code']}\">{$row['name']}</option>\n";
    }
    ?>
</div>

</body>
</html>
