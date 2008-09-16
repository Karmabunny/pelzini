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


require_once 'head.php';


$id = (int) $_GET['id'];
$q = "SELECT Name FROM Packages WHERE ID = {$id} LIMIT 1";
$res = execute_query($q);
if (mysql_num_rows($res) == 0) {
  echo '<p>Invalid package specified.</p>';
}
$row = mysql_fetch_assoc($res);
$row['Name'] = htmlspecialchars($row['Name']);
?>


<h2><?=$row['Name'];?></h2>


<?php
$q = "SELECT Files.ID, Files.Name, Files.Description
  FROM Files
  WHERE Files.PackageID = {$id}";
$res = execute_query ($q);
while ($row = mysql_fetch_assoc ($res)) {
  // encode for output
  $row['Name'] = htmlspecialchars($row['Name']);
  $row['Description'] = htmlspecialchars($row['Description']);
  
  // output	
	echo "<p><a href=\"file.php?id={$row['ID']}\">{$row['Name']}</a> {$row['Description']}</p>";
}
?>

<?php
require_once 'foot.php';
?>
