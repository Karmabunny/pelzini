<?php
header('Content-type: text/xml; charset=UTF-8');
require_once '../functions.php';


$where = array ();

switch ($_GET['type']) {
  case 'classes':
    $table = 'Classes';
    break;
    
  case 'functions':
    $table = 'Functions';
    $where[] = 'ClassID IS NULL';
    break;
    
  case 'files':
    $table = 'Files';
    break;
    
  default:
    echo '<error>Invalid parameters.</error>';
    unset ($_SESSION['last_selected_type']);
    exit;
    
}

$_SESSION['last_selected_type'] = $_GET['type'];

if ($_SESSION['current_package'] != null) {
  $where[] = 'Files.PackageID = ' . $_SESSION['current_package'];
}


$where = implode(' AND ', $where);

if ($table == 'Files') {
  // files don't need a join
  $q = "SELECT ID, Name
    FROM Files";
  if ($where != '') $q .= ' WHERE ' . $where;
  $q .= ' ORDER BY Name';
  
  
} else {
  // other tables do
  $q = "SELECT {$table}.ID, {$table}.Name
    FROM {$table}
    INNER JOIN Files ON {$table}.FileID = Files.ID";
  if ($where != '') $q .= ' WHERE ' . $where;
  $q .= " ORDER BY {$table}.Name";
  
}


// return the items
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
