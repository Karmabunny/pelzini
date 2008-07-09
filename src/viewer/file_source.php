<?php
require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
	$name = trim($_GET['name']);
	if ($name == '') {
		fatal ("<p>Invalid filename!</p>");
	}
	$name = mysql_escape ($name);
	$where = "Name LIKE '{$name}'";
} else {
	$where = "ID = {$id}";
}


// Get the details of this file
$q = "SELECT Name, Description, Source FROM Files WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
echo "<p>{$row['Description']}</p>";

$source = trim($row['Source']);
$source = explode("\n", $source);

$num = count($source);
$cols = strlen($num);


echo "<table><tr>";

echo '<td><pre>';
foreach ($source as $num => $line) {
  echo str_pad(($num + 1), $cols, ' ', STR_PAD_LEFT) . "\n";
  $lines .= htmlspecialchars ($line) . "\n";
}
echo '</pre></td>';

echo '<td><pre class="source">', $lines, '</pre></td>';

echo '</tr></table>';


require_once 'foot.php';
?>

