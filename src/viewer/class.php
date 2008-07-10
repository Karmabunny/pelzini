<?php
require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
	$name = trim($_GET['name']);
	if ($name == '') {
		fatal ("<p>Invalid class name!</p>");
	}
	$name = mysql_escape ($name);
	$where = "Classes.Name LIKE '{$name}'";
} else {
	$where = "Classes.ID = {$id}";
}


// Get the details of this class
$q = "SELECT Classes.ID, Classes.Name, Classes.Description, Classes.Extends, Files.Name AS Filename
	FROM Classes
	INNER JOIN Files ON Classes.FileID = Files.ID
	WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
$filename_clean = htmlentities(urlencode($row['Filename']));
echo "<p>File: <a href=\"file.php?name={$filename_clean}\">" . htmlentities($row['Filename']) . "</a></p>\n";
echo "<p>{$row['Description']}</p>";
$id = $row['ID'];
$class_name = $row['Name'];

if ($row['Extends'] != null) {
  $row['Extends'] = htmlspecialchars($row['Extends']);
  echo "<p>Extends <a href=\"class.php?name={$row['Extends']}\">{$row['Extends']}</a>";
  
  if ($_GET['complete'] == 1) {
    echo " | <a href=\"class.php?id={$id}\">Hide inherited members</a></p>";
  } else {
    echo " | <a href=\"class.php?id={$id}&complete=1\">Show inherited members</a></p>";
  }
}


$functions = array();
$variables = array();
$class = $row['Name'];

if ($_GET['complete'] == 1) {
  do {
    list ($funcs, $vars, $parent) =  load_class($class);
    
    $functions = array_merge($funcs, $functions);
    $variables = array_merge($vars, $variables);
      
    $class = $parent;
  } while ($class != null);
  
} else {
  list ($functions, $variables) =  load_class($class);
}

ksort($functions);
ksort($variables);


// Show variables
if (count($variables) > 0) {
  echo "<h3>Variables</h3>";
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  foreach ($variables as $row) {
    // encode for output
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) {
      $row['Description'] = '&nbsp;';
    } else {
      $row['Description'] = htmlspecialchars($row['Description']);
    }
      
    // display
	  echo "<tr>";
	  echo "<td><code><a href=\"function.php?id={$row['ID']}\">";
	  echo "{$row['Name']}</a></code></td>";
	  echo "<td>{$row['Description']}</td>";
	  echo "</tr>\n";
  }
  echo "</table>\n";
}


// Show functions
if (count($functions) > 0) {
  foreach ($functions as $row) {
    // encode for output
    if ($row['Description'] == null) {
      $row['Description'] = '<em>This function does not have a description</em>';
    }
    
    // display
	  echo "<h3>{$row['Visibility']} {$row['Name']}";
	  if ($row['ClassName'] != $class_name) {
	    echo " <small>(from <a href=\"class.php?name={$row['ClassName']}\">{$row['ClassName']}</a>)</small>";
	  }
	  echo "</h3>";
	  
	  echo $row['Description'];
	  
	  // show return value
	  if ($row['ReturnType'] != null) {
	    $link = get_object_link($row['ReturnType']);
	    echo "<p>Returns <b>{$link}</b>";
	    
	    if ($row['ReturnDescription'] != null) {
	      echo ': ', $row['ReturnDescription'];
	    }
	    echo '</p>';
	  }
	  
	  // Show parameters
    $q = "SELECT Name, Type, Description FROM Parameters WHERE FunctionID = {$row['ID']}";
    $res = execute_query($q);
    if (mysql_num_rows($res) > 0) {
      echo "<ul>\n";
      while ($param = mysql_fetch_assoc ($res)) {
        if ($param['Description'] != null) {
          $param['Description'] = ': ' . str_replace("\n", '<br>', $param['Description']);      
        }
        
        $link = get_object_link($param['Type']);
	      echo "<li>{$link} <b>{$param['Name']}</b>{$param['Description']}</li>";
      }
      echo "</ul>\n";
    }
  }
}


require_once 'foot.php';


/**
* Returns an array of
* [0] => functions
* [1] => variables
* [2] => parent class
**/
function load_class($name) {
  // determine parent class
  $name = mysql_escape ($name);
  $q = "SELECT ID, Extends FROM Classes WHERE Name LIKE '{$name}'";
  $res = execute_query($q);
  $row = mysql_fetch_assoc($res);
  $id = $row['ID'];
  $parent = $row['Extends'];
  
  // determine functions
  $functions = array();
  $q = "SELECT *, '{$name}' AS ClassName FROM Functions WHERE ClassID = {$id}";
  $res = execute_query($q);
  while ($row = mysql_fetch_assoc($res)) {
    $functions[$row['Name']] = $row;
  }
  
  // determine variables
  $variables = array();
  $q = "SELECT *, '{$name}' AS ClassName FROM Variables WHERE ClassID = {$id}";
  $res = execute_query($q);
  while ($row = mysql_fetch_assoc($res)) {
    $variables[$row['Name']] = $row;
  }
  
  return array($functions, $variables, $parent);
}
?>
