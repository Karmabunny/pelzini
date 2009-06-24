<?php
require_once 'head.php';
?>


<h2><?= $_GET['name']; ?></h2>

<p>These are the strings for this language</p>
<?php
$eng = get_strings('english');
$lang = get_strings($_GET['name']);

echo "<table>";
foreach ($eng as $name => $eng_str) {
  $lang_str = $lang[$name];
  
  $eng_str = htmlspecialchars ($eng_str);
  $lang_str = htmlspecialchars ($lang_str);
  
  if ($lang_str == '') $lang_str = '<i>None</i>';
  
  echo "<tr>\n";
  echo "  <td>{$name}</td>\n";
  echo "  <td>{$eng_str}</td>\n";
  echo "  <td>{$lang_str}</td>\n";
  echo "</tr>\n";
}
echo "</table>";
?>



<?php
require_once 'foot.php';
?>
