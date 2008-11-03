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

/**
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
**/

require_once 'functions.php';

$q = "SELECT Name, License FROM Projects WHERE ID = " . CONFIG::ProjectID;
$res = execute_query($q);
$project = mysql_fetch_assoc($res);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>Documentation for <?= $project['Name']; ?></title>
  <link href="style.css" rel="stylesheet" type="text/css">
  <script language="javascript" src="ajax/ajax.js"></script>
  
<?php
$body = '<body>';

if (isset($_SESSION['last_selected_type'])) {
  $body = '<body onload="load();">';
  
  echo "<script>
    function load() {
      change_sidebar_type('{$_SESSION['last_selected_type']}');
    }
  </script>\n";
}
?>

</head>
<?= $body; ?>

<div class="header">
  <h1>Documentation for <?= $project['Name']; ?></h1>
</div>

<div class="navigation">
  <a href="index.php">Home</a>
  <a href="select_package.php">All packages</a>
  &nbsp;
  
  <?php
  $q = "SELECT ID, Name FROM Packages ORDER BY Name";
  $res = execute_query($q);
  while ($row = mysql_fetch_assoc($res)) {
    $row['Name'] = htmlspecialchars($row['Name']);
    
    if ($_SESSION['current_package'] == $row['ID']) {
      echo "<a href=\"select_package.php?id={$row['ID']}\" class=\"on\">{$row['Name']}</a> ";
    } else {
      echo "<a href=\"select_package.php?id={$row['ID']}\">{$row['Name']}</a> ";
    }
  }
  ?>
</div>

<table class="main">
<tr>
<td class="sidebar">
  <div class="box-nohead">
    <form action="search.php" method="get">
      <input type="text" name="q" style="width: 135px;" value="<?= htmlspecialchars ($_GET['q']); ?>">
      <input type="submit" value="Search">
    </form>
  </div>
  
  <script>
  function change_sidebar_type(type) {
    ajax_request('ajax/get_items.php?type=' + type, change_sidebar_type_process)
  }
  
  function change_sidebar_type_process(top_node) {
    var items = top_node.getElementsByTagName('item');
    var type = top_node.firstChild.getAttribute('type');
    
    var box = document.getElementById('sidebar_items');
    
    while (box.firstChild) {
      box.removeChild(box.firstChild);
    }
    
    switch (type) {
      case 'classes':   var base_url = 'class.php'; break;
      case 'functions': var base_url = 'function.php'; break;
      case 'files':     var base_url = 'file.php'; break;
      default:
        box.appendChild(document.createTextNode('Select something from the drop-down list above'));
        return;
    }
    
    var select = document.getElementById('sidebar_type_select');
    select.value = type;
    
    // populate the items
    for (var i = 0; i < items.length; i++) {
      var item_name = items[i].firstChild.data;
      var item_id = items[i].getAttribute('id');
   
      item_name = item_name.replace(/&/, "%26");
      var item_url = base_url + '?id=' + item_id;
   
      a = document.createElement('a');
      a.setAttribute('href', item_url);
      a.appendChild(document.createTextNode(item_name));
   
      var p = document.createElement('p');
      p.appendChild(a);
      box.appendChild(p);
    }
    
    // show a 'nothing was found' message
    if (! box.firstChild) {
      box.appendChild(document.createTextNode('Nothing was found for this package'));
    }
  }
  </script>
  
  <div class="box">
    <h2><select onchange="change_sidebar_type(this.value)" id="sidebar_type_select">
      <option value="">-- Select below --</option>
      <option value="classes">Classes</option>
      <option value="functions">Functions</option>
      <option value="files">Files</option>
    </select></h2>
    
    <div id="sidebar_items">
      Select something from the drop-down list above
    </div>
  </div>
</td>

<td class="main">
