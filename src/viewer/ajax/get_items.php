<?php
header('Content-type: text/xml; charset=UTF-8');
require_once '../functions.php';


switch ($_GET['type']) {
  case 'classes':
    $table = 'Classes';
    break;
    
  case 'functions':
    $table = 'Functions';
    $where = 'ClassID IS NULL';
    break;
    
  case 'files':
    $table = 'Files';
    break;
    
  case 'packages':
    $table = 'Packages';
    break;
    
  default:
    echo '<error>Invalid parameters.</error>';
    exit;
    
}


$q = 'SELECT ID, Name FROM ' . $table;
if ($where != null) $q .= ' WHERE ' . $where;
$q .= ' ORDER BY Name';

$res = execute_query($q);
echo "<items type=\"{$_GET['type']}\">";
while ($row = mysql_fetch_assoc ($res)) {

  if ($_GET['type'] == 'files') {
    $row['Name'] = basename($row['Name']);
  }

  $row['Name'] = htmlspecialchars($row['Name']);
  echo "<item id=\"{$row['ID']}\">{$row['Name']}</item>";
}
echo '</items>';
?>
