<?php
require_once 'head.php';
?>

<p>This is the documentation for this project</p>

<?php
	$q = "SELECT ID, Name, Description FROM Files";
	$res = execute_query ($q);
	while ($row = mysql_fetch_assoc ($res)) {
		echo "<h2><a href=\"file.php?id={$row['ID']}\">{$row['Name']}</a></h2>";
		echo "<p>{$row['Description']}</p>";
	}
?>

<?php
require_once 'foot.php';
?>
