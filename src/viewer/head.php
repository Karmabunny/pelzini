<?php
require_once 'functions.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>

<div class="header">
  <h1>Documentation</h1>
</div>

<div class="navigation">
  <a href="index.php">Home</a>
  
  <?php
  $q = "SELECT ID, Name FROM Packages";
  $res = execute_query($q);
  while ($row = mysql_fetch_assoc($res)) {
    $row['Name'] = htmlspecialchars($row['Name']);
    echo "<a href=\"package.php?id={$row['ID']}\">{$row['Name']}</a> ";
  }
  ?>
</div>

<table class="main">
<tr>
<td class="sidebar">
  <div class="box">
  <?php
  // show class names of all classes
  $q = "SELECT ID, Name FROM Classes";
  $res = execute_query($q);
  if (mysql_num_rows($res) > 0) {
    echo "<h2>Classes</h2>";
    echo "<div>";
    while ($row = mysql_fetch_assoc($res)) {
      $row['Name'] = htmlspecialchars($row['Name']);
      echo "<p><a href=\"class.php?id={$row['ID']}\">{$row['Name']}</a></p>";
    }
    echo '</div>';
  }
  ?>
  </div>
  
  <div class="box">
  <?php
  // show class names of all functions
  $q = "SELECT ID, Name FROM Functions WHERE ClassID IS NULL";
  $res = execute_query($q);
  if (mysql_num_rows($res) > 0) {
    echo "<h2>Functions</h2>";
    echo "<div>";
    while ($row = mysql_fetch_assoc($res)) {
      $row['Name'] = htmlspecialchars($row['Name']);
      echo "<p><a href=\"function.php?id={$row['ID']}\">{$row['Name']}</a></p>";
    }
    echo '</div>';
  }
  ?>
  </div>   
</td>

<td class="main">
