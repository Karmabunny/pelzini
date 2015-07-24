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

$browser_title = 'Documentation for ' . $project['name'];
if (!empty($skin['page_name'])) {
	$browser_title = $skin['page_name'] . ' - ' . $browser_title;
}

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title><?php echo $browser_title; ?></title>

    <base href="<?php echo htmlspecialchars($base_path); ?>">

    <link href="../assets/style.css" rel="stylesheet" type="text/css">
    <script src="../assets/functions.js" type="text/javascript"></script>
    <link href="../assets/favicon.ico" rel="icon" type="image/vnd.microsoft.icon" >
</head>
<body>

<div class="header">
  <h1><?php echo str (STR_MAIN_TITLE, 'project', $project['name']); ?></h1>
  <p><?php echo str (STR_INTRO_PARAGRAPH, 'project', $project['name']); ?></p>
</div>

<div class="navigation">
  <span style="float: right">
    <a href="more_info"><?php echo str(STR_MORE_INFO); ?></a>

    <form action="select_project" method="post">
    <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <b><?php echo str(STR_PROJECT); ?>:</b>
    <select name="code" onchange="this.form.submit();">
      <?php
      $q = "SELECT id, code, name FROM projects WHERE name != '' AND code != '' ORDER BY name";
      $_res = db_query($q);
      while ($row = db_fetch_assoc($_res)) {
          if ($project['id'] == $row['id']) {
              echo "<option value=\"{$row['code']}\" selected>{$row['name']}</option>\n";
          } else {
              echo "<option value=\"{$row['code']}\">{$row['name']}</option>\n";
          }
      }
      ?>
    </select>
    </form>
  </span>

  <a href="index"><?php echo str(STR_HOME); ?></a>
  &nbsp;

  <?php
$q = new SelectQuery();
$q->addFields('packages.id, packages.name');
$q->setFrom('files');
$q->addInnerJoin('packages ON files.packageid = packages.id');
$q->setGroupBy('packages.id');
$q->setOrderBy('packages.name');
$q->addProjectWhere();

$q = $q->buildQuery();
$_res = db_query($q);
while ($row = db_fetch_assoc($_res)) {
    echo '<a href="package?name=', urlencode($row['name']), '">', htmlspecialchars($row['name']), '</a> ';
}
?>
</div>

<table class="main">
<tr>
<td class="sidebar">
  <div class="box">
    <h2><?php echo str(STR_SEARCH_TITLE); ?></h2>
    <div div class="content">
    <form action="search" method="get">
      <input type="hidden" name="advanced" value="1">

      <p>
        <b><?php echo str(STR_SEARCH_TERM); ?></b>
        <br><input type="text" name="q" style="width: 200px;" value="<?php echo htmlspecialchars(@$_GET['q']); ?>">
      </p>

      <p>
        <b><?php echo str(STR_WHAT_SEARCH); ?></b>
        <br><label><input type="checkbox" name="classes" value="y" <?php if (!isset($_GET['advanced']) or @$_GET['classes'] == 'y') echo 'checked'; ?>> <?php echo str(STR_CLASSES); ?></label>
        <br><label><input type="checkbox" name="functions" value="y" <?php if (!isset($_GET['advanced']) or @$_GET['functions'] == 'y') echo 'checked'; ?>> <?php echo str(STR_FUNCTIONS); ?></label>
        <br><label><input type="checkbox" name="constants" value="y" <?php if (!isset($_GET['advanced']) or @$_GET['constants'] == 'y') echo 'checked'; ?>> <?php echo str(STR_CONSTANTS); ?></label>
        <br><label><input type="checkbox" name="source" value="y" <?php if (!isset($_GET['advanced']) or @$_GET['source'] == 'y') echo 'checked'; ?>> <?php echo str(STR_SOURCE_CODE); ?></label>
      </p>

      <p>
        <b><?php echo str(STR_SEARCH_PATH); ?></b>
        <br><input type="text" name="path" style="width: 200px;" value="<?php echo htmlspecialchars(@$_GET['path']); ?>">
      </p>

      <p style="text-align: right;">
        <input type="submit" value="<?php echo str (STR_SEARCH_GO_BTN); ?>">
      </p>
    </form>
    </div>
  </div>
</td>

<td class="main">

<!-- Main content begins here -->
