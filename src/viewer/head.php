<?php
require_once 'functions.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>Documentation for <?=CONFIG::Title;?></title>
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
  <h1>Documentation</h1>
</div>

<div class="navigation">
  <a href="index.php">Home</a>
  
  <?php
  $q = "SELECT ID, Name FROM Packages ORDER BY Name";
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
  <script>
  function change_sidebar_type(type) {
    if (type == '') return;
    ajax_request('ajax/get_items.php?type=' + type, change_sidebar_type_process)
  }
  
  function change_sidebar_type_process(top_node) {
    var items = top_node.getElementsByTagName('item');
    var type = top_node.firstChild.getAttribute('type');
    
    switch (type) {
      case 'classes':   var base_url = 'class.php'; break;
      case 'functions': var base_url = 'function.php'; break;
      case 'files':     var base_url = 'file.php'; break;
      case 'packages':  var base_url = 'package.php'; break;
    }
    
    var select = document.getElementById('sidebar_type_select');
    select.value = type;
    
    var box = document.getElementById('sidebar_items');
    
    while (box.firstChild) {
      box.removeChild(box.firstChild);
    }

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
  }
  </script>
  
  <div class="box">
    <h2><select onchange="change_sidebar_type(this.value)" id="sidebar_type_select">
      <option value="">-- Select below --</option>
      <option value="classes">Classes</option>
      <option value="functions">Functions</option>
      <option value="files">Files</option>
      <option value="packages">Packages</option>
    </select></h2>
    
    <div id="sidebar_items"></div>
  </div>
</td>

<td class="main">
