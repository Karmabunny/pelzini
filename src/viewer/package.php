<?php
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
