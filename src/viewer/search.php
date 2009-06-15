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
* Does a search of the database
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
* @tag i18n-done
**/

require_once 'functions.php';

$skin['page_name'] = str(STR_SEARCH_TITLE);
require_once 'head.php';

$query = db_escape ($_GET['q']);
$_GET['advanced'] = (int) $_GET['advanced'];
$results = false;

// Determine the match string
// #ITEM# will be replaced in the specific search query
// for for classes, #ITEM# will become classes.name
$match_string = "#ITEM# LIKE '%{$query}%'";
if ($_GET['case_sensitive']) $match_string = "BINARY #ITEM# LIKE '%{$query}%'";


echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
echo "<span style=\"float: right;\">", str(STR_SHOW_HIDE_ALL), " &nbsp;</span>";

echo '<h2>', str(STR_SEARCH_TITLE), '</h2>';
echo '<p>', str(
    $_GET['case_sensitive'] ? STR_YOU_SEARCHED_FOR_CASE : STR_YOU_SEARCHED_FOR,
    'term', htmlspecialchars($_GET['q'])
  ), '</p>';

// classes
if ($_GET['advanced'] == 0 or $_GET['classes'] == 'y') {
  $this_match_string = str_replace ('#ITEM#', 'classes.name', $match_string);
  $q = "SELECT classes.id, classes.name, classes.description, classes.extends, classes.abstract, files.name as filename, classes.fileid
    FROM classes
    INNER JOIN files ON classes.fileid = files.id
    WHERE {$this_match_string} ORDER BY classes.name";
  $res = db_query ($q);
  $num = db_num_rows ($res);
  if ($num != 0) {
    $results = true;
    echo '<h3>', str(STR_CLASSES_RESULT, 'num', $num), '</h3>';
    
    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
      $row['name'] = htmlspecialchars ($row['name']);
      $row['filename'] = htmlspecialchars ($row['filename']);
      
      $class = 'item';
      if ($alt) $class .= '-alt';
      
      echo "<div class=\"{$class}\">";
      echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
      echo "<p><strong><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></strong>";
      
      if ($row['extends'] != null) {
        $row['extends'] = htmlspecialchars($row['extends']);
        echo " <small>extends <a href=\"class.php?name={$row['extends']}\">{$row['extends']}</a></small>";
      }
      
      if ($row['abstract'] == 1) {
        echo " <small>(abstract)</small>";
      }
      
      echo "<div class=\"content\">";
      echo delink_inline($row['description']);
      echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small></div>";
      echo "</div>";
      
      $alt = ! $alt;
    }
    echo '</div>';
  }
}


// functions
if ($_GET['advanced'] == 0 or $_GET['functions'] == 'y') {
  $this_match_string = str_replace ('#ITEM#', 'functions.name', $match_string);
  $q = "SELECT functions.id, functions.name, functions.description, functions.classid, files.name as filename, functions.fileid, classes.name as class
    FROM functions
    INNER JOIN files ON functions.fileid = files.id
    LEFT JOIN classes ON functions.classid = classes.id
    WHERE {$this_match_string} ORDER BY functions.name";
  $res = db_query ($q);
  $num = db_num_rows ($res);
  if ($num != 0) {
    $results = true;
    echo '<h3>', str(STR_FUNCTIONS_RESULT, 'num', $num), '</h3>';
    
    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
      $row['name'] = htmlspecialchars ($row['name']);
      $row['filename'] = htmlspecialchars ($row['filename']);
      
      $class = 'item';
      if ($alt) $class .= '-alt';
      
      echo "<div class=\"{$class}\">";
      echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
      echo "<p><strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong>";
      
      if ($row['class'] != null) {
        $row['class'] = htmlspecialchars($row['class']);
        echo " <small>from class <a href=\"class.php?id={$row['classid']}\">{$row['class']}</a></small>";
      }
      
      echo "<div class=\"content\">";
      echo delink_inline($row['description']);
      echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small></div>";
      echo "</div>";
      
      $alt = ! $alt;
    }
    echo '</div>';
  }
}


// constants
if ($_GET['advanced'] == 0 or $_GET['constants'] == 'y') {
  $this_match_string = str_replace ('#ITEM#', 'constants.name', $match_string);
  $q = "SELECT constants.name, constants.description, files.name as filename, constants.fileid, constants.value
    FROM constants
    INNER JOIN files ON constants.fileid = files.id
    WHERE {$this_match_string} ORDER BY constants.name";
  $res = db_query ($q);
  $num = db_num_rows ($res);
  if ($num != 0) {
    $results = true;
    echo '<h3>', str(STR_CONSTANTS_RESULT, 'num', $num), '</h3>';
    
    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
      $row['name'] = htmlspecialchars ($row['name']);
      $row['filename'] = htmlspecialchars ($row['filename']);
      $row['value'] = htmlspecialchars ($row['value']);
      
      $class = 'item';
      if ($alt) $class .= '-alt';
      
      echo "<div class=\"{$class}\">";
      echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
      echo "<p><strong><a href=\"file.php?id={$row['fileid']}\">{$row['name']}</a></strong>";
      echo " = <strong>{$row['value']}</strong>";
      
      echo "<div class=\"content\">";
      echo delink_inline($row['description']);
      echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small></div>";
      echo "</div>";
      
      $alt = ! $alt;
    }
    echo '</div>';
  }
}


// source
if ($_GET['advanced'] == 0 or $_GET['source'] == 'y') {
  $this_match_string = str_replace ('#ITEM#', 'files.source', $match_string);
  $q = "SELECT files.id, files.name AS filename, files.source
    FROM files
    WHERE {$this_match_string} ORDER BY files.name";
  $res = db_query ($q);
  $num = db_num_rows ($res);
  if ($num != 0) {
    $results = true;
    echo '<h3>', str(STR_SOURCE_CODE_RESULT, 'num', $num), '</h3>';
    
    $regex_search = htmlspecialchars($_GET['q']);
    $regex_search = '/(' . preg_quote ($regex_search, '/'). ')/i';
    $url_keyword = urlencode($_GET['q']);
    
    $alt = false;
    echo '<div class="list">';
    while ($row = db_fetch_assoc ($res)) {
      $row['filename'] = htmlspecialchars ($row['filename']);
      
      $class = 'item';
      if ($alt) $class .= '-alt';
      
      echo "<div class=\"{$class}\">";
      echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
      echo "<p>";
      echo "<strong><a href=\"file.php?id={$row['id']}\">{$row['filename']}</a></strong> &nbsp; ";
      echo "<small><a href=\"file_source.php?id={$row['id']}&keyword={$url_keyword}\">Highlighted file source</a></small>";
      echo "</p>\n";
      
      // Finds the lines, and highlights the term
      echo "<p class=\"content\">";
      $lines = explode("\n", $row['source']);
      foreach ($lines as $num => $line) {
        if (stripos($line, $_GET['q']) !== false) {
          $num++;
          $line = htmlspecialchars($line);
          $line = preg_replace($regex_search, "<span class=\"highlight\">\$1</span>", $line);
          
          $source_url = "file_source.php?id={$row['id']}&highlight={$num}";
          if ($num > 5) $source_url .= '#line' . ($num - 5);
          
          echo "Line <a href=\"{$source_url}\">{$num}</a>: <code>{$line}</code><br>";
        }
      }
      echo "</p>";
      echo "</div>";
      
      $alt = ! $alt;
    }
    echo '</div>';
  }
}


// no results
if (! $results) {
  echo "<p>Nothing was found!</p>";
}


require_once 'foot.php';
?>
