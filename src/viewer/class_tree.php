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
* Shows a list of all authors
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.2
* @tag i18n-done
**/

require_once 'head.php';


echo '<h2>', str(STR_CLASS_TREE_TITLE), '</h2>';

$root = create_classes_tree ();
$top_nodes = $root->getChildren();
usort($top_nodes, 'nodenamesort');

echo "<ul class=\"tree\">\n";
foreach ($top_nodes as $node) {
  draw_class_tree($node);
}
echo "</ul>\n";

require_once 'foot.php';


/**
* Draws the tree from this node and below as unordered lists within unordered lists
**/
function draw_class_tree($node) {
  // Draw this item
  echo '<li>', get_object_link($node['name']);
  
  // Draw its children if it has any
  $children = $node->getChildren();
  usort($children, 'nodenamesort');
  
  if (count($children) > 0) {
    echo "\n<ul>\n";
    foreach ($children as $child) {
      draw_class_tree($child);
    }
    echo "</ul>\n";
  }
  
  echo "</li>\n";
}

function nodenamesort($a, $b) {
  return strcasecmp($a['name'], $b['name']);
}
?>
