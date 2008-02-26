<?php
require_once 'head.php';
?>

<p>This is the documentation for this project</p>

<?php
	$q = "SELECT ID, Name, Description FROM Files";
	$res = execute_query ($q);
	while ($row = mysql_fetch_assoc ($res)) {
    // encode for output
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) {
      $row['Description'] = '&nbsp;';
    } else {
      $row['Description'] = htmlspecialchars($row['Description']);
    }
    
    // output	
		echo "<p><a href=\"file.php?id={$row['ID']}\">{$row['Name']}</a> {$row['Description']}</p>";
	}
?>

<?php
require_once 'foot.php';
?>
