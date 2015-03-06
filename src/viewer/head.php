<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of Pelzini.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * The viewer header
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @tag i18n-done
 **/

require_once 'functions.php';

$browser_title = 'Documentation for ' . $project['name'];
if ($skin['page_name']) {
	$browser_title = $skin['page_name'] . ' - ' . $browser_title;
}

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title><?php echo $browser_title; ?></title>
  <link href="style.css" rel="stylesheet" type="text/css">

  <script type="text/javascript" src="ajax/ajax.js"></script>
  <script type="text/javascript" src="functions.js"></script>

  <link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico">
</head>
<body>

<div class="header">
  <h1><?php echo str (STR_MAIN_TITLE, 'project', $project['name']); ?></h1>
  <p><?php echo str (STR_INTRO_PARAGRAPH, 'project', $project['name']); ?></p>
</div>

<div class="navigation">
  <span style="float: right">
    <a href="more_info.php"><?php echo str(STR_MORE_INFO); ?></a>

    <?php if (!isset($dvgProjectCode)): ?>
    <form action="select_project.php" method="get">
    <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <b><?php echo str(STR_PROJECT); ?>:</b>
    <select name="id" onchange="this.form.submit();">
      <?php
      $q = "SELECT id, name FROM projects WHERE name != '' AND code != '' ORDER BY id";
      $res = db_query($q);
      while ($row = db_fetch_assoc ($res)) {
          if ($_SESSION['current_project'] == $row['id']) {
              echo "<option value=\"{$row['id']}\" selected>{$row['name']}</option>\n";
          } else {
              echo "<option value=\"{$row['id']}\">{$row['name']}</option>\n";
          }
      }
      ?>
    </select>
    </form>
    <?php endif; ?>
  </span>

  <a href="index.php"><?php echo str(STR_HOME); ?></a>
  <a href="select_package.php"><?php echo str(STR_ALL_PACKAGES); ?></a>
  &nbsp;

  <?php
$q = new SelectQuery();
$q->addFields('packages.id, packages.name');
$q->setFrom('files');
$q->addInnerJoin('packages ON files.packageid = packages.id');
$q->setGroupBy('packages.id');
$q->setOrderBy('packages.name');
$q->addSinceVersionWhere();
$q->addProjectWhere();

$q = $q->buildQuery();
$res = db_query($q);
while ($row = db_fetch_assoc($res)) {
    $row['name'] = htmlspecialchars($row['name']);

    if ($_SESSION['current_package'] == $row['id']) {
        echo "<a href=\"select_package.php?id={$row['id']}\" class=\"on\">{$row['name']}</a> ";
    } else {
        echo "<a href=\"select_package.php?id={$row['id']}\">{$row['name']}</a> ";
    }
}
?>
</div>

<table class="main">
<tr>
<td class="sidebar">
  <div class="box">
    <h2><?php echo str(STR_SEARCH_TITLE); ?></h2>
    <div div class="content">
    <form action="search.php" method="get">
      <input type="hidden" name="advanced" value="1">

      <p>
        <b><?php echo str(STR_SEARCH_TERM); ?></b>
        <br><input type="text" name="q" style="width: 200px;" value="<?php echo htmlspecialchars($_GET['q']); ?>">
      </p>

      <p>
        <b><?php echo str(STR_WHAT_SEARCH); ?></b>
        <br><label><input type="checkbox" name="classes" value="y" <?php if (!isset($_GET['advanced']) or @$_GET['classes'] == 'y') echo 'checked'; ?>> <?php echo str(STR_CLASSES); ?></label>
        <br><label><input type="checkbox" name="functions" value="y" <?php if (!isset($_GET['advanced']) or @$_GET['functions'] == 'y') echo 'checked'; ?>> <?php echo str(STR_FUNCTIONS); ?></label>
        <br><label><input type="checkbox" name="constants" value="y" <?php if (!isset($_GET['advanced']) or @$_GET['constants'] == 'y') echo 'checked'; ?>> <?php echo str(STR_CONSTANTS); ?></label>
        <br><label><input type="checkbox" name="source" value="y" <?php if (!isset($_GET['advanced']) or @$_GET['source'] == 'y') echo 'checked'; ?>> <?php echo str(STR_SOURCE_CODE); ?></label>
      </p>

      <p style="text-align: right;">
        <input type="submit" value="<?php echo str (STR_SEARCH_GO_BTN); ?>">
      </p>
    </form>
    </div>
  </div>


<?php
// Classes list
$q = new SelectQuery();
$q->addFields('classes.id, classes.name');
$q->setFrom('classes');
$q->addInnerJoin('files ON classes.fileid = files.id');
$q->setGroupBy('classes.id');
$q->setOrderBy('classes.name');
$q->addProjectWhere();
$q = $q->buildQuery();

$res = db_query($q);
if (db_num_rows ($res) > 0) {
    echo '  <div class="box">';
    echo '    <img src="images/icon_add.png" alt="" title="Show this result" onclick="show_content(event)" class="showhide" style="margin: 3px;">';
    echo '    <h2>', str(STR_CLASSES), '</h2>';
    echo '    <div class="content" style="display: none;">';

    while ($row = db_fetch_assoc ($res)) {
        echo "<p><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></p>\n";
    }

    echo '    </div>';
    echo '  </div>';
}


// Interfaces list
$q = new SelectQuery();
$q->addFields('interfaces.id, interfaces.name');
$q->setFrom('interfaces');
$q->addInnerJoin('files ON interfaces.fileid = files.id');
$q->setGroupBy('interfaces.id');
$q->setOrderBy('interfaces.name');
$q->addProjectWhere();
$q = $q->buildQuery();

$res = db_query ($q);
if (db_num_rows ($res) > 0) {
    echo '  <div class="box">';
    echo '    <img src="images/icon_add.png" alt="" title="Show this result" onclick="show_content(event)" class="showhide" style="margin: 3px;">';
    echo '    <h2>', str(STR_INTERFACES), '</h2>';
    echo '    <div class="content" style="display: none;">';

    while ($row = db_fetch_assoc ($res)) {
        echo "<p><a href=\"interface.php?id={$row['id']}\">{$row['name']}</a></p>\n";
    }

    echo '    </div>';
    echo '  </div>';
}


// Functions list
$q = new SelectQuery();
$q->addFields('functions.id, functions.name');
$q->setFrom('functions');
$q->addInnerJoin('files ON functions.fileid = files.id');
$q->setGroupBy('functions.id');
$q->setOrderBy('functions.name');
$q->addProjectWhere();
$q = $q->buildQuery();

if (db_num_rows ($res) > 0) {
    echo '  <div class="box">';
    echo '    <img src="images/icon_add.png" alt="" title="Show this result" onclick="show_content(event)" class="showhide" style="margin: 3px;">';
    echo '    <h2>', str(STR_FUNCTIONS), '</h2>';
    echo '    <div class="content" style="display: none;">';

    while ($row = db_fetch_assoc ($res)) {
        echo "<p><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></p>\n";
    }

    echo '    </div>';
    echo '  </div>';
}


// Files list
$q = new SelectQuery();
$q->addFields('files.id, files.name');
$q->setFrom('files');
$q->setOrderBy('files.name');
$q->addProjectWhere();
$q = $q->buildQuery();

if (db_num_rows ($res) > 0) {
    echo '  <div class="box">';
    echo '    <img src="images/icon_add.png" alt="" title="Show this result" onclick="show_content(event)" class="showhide" style="margin: 3px;">';
    echo '    <h2>', str(STR_FILES), '</h2>';
    echo '    <div class="content" style="display: none;">';

    while ($row = db_fetch_assoc ($res)) {
        echo "<p><a href=\"file.php?id={$row['id']}\">{$row['name']}</a></p>\n";
    }

    echo '    </div>';
    echo '  </div>';
}
?>
</td>

<td class="main">

<!-- Main content begins here -->
