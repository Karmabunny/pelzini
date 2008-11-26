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
* @since 0.2
**/


/**
* A node in a tree
**/
class TreeNode implements ArrayAccess {
  private $data = array();
  private $children = array();
  private $parent = null;
  
  
  public function addChild (TreeNode $child) {
    $this->children[] = $child;
    $child->parent = $this;
  }
  
  public function getChildren () {
    return $this->children;
  }
  
  public function getData() {
    return $this->data;
  }
  
  
  public function offsetExists ($index) {
    return isset ($this->data[$index]);
  }
  
  public function offsetGet ($index) {
    return $this->data[$index];
  }
  
  public function offsetSet ($index, $value) {
    $this->data[$index] = $value;
  }
  
  public function offsetUnset ($index) {
    unset ($this->data[$index]);
  }
  
  
  public function findNode (TreeNodeMatcher $matcher) {
    $res = $matcher->match ($this);
    if ($res) return $this;
    
    foreach ($this->children as $node) {
      $res = $node->findNode ($matcher);
      if ($res) return $res;
    }
    
    return null;
  }
  
  /**
  * Returns an array of all the the ancestors of this node
  **/
  public function findAncestors() {
    $ancestors = array();
    
    $node = $this;
    while (! $node instanceof RootTreeNode) {
      $ancestors[] = $node;
      $node = $node->parent;
    }
    
    return $ancestors;
  }
  
  public function dump () {
    echo '<div style="border: 1px black solid; padding: 0.5em; margin: 0.5em;">';
    foreach ($this->data as $key => $val) {
      echo "<p>'{$key}' = '{$val}'</p>";
    }
    foreach ($this->children as $node) {
      $node->dump();
    }
    echo '</div>';
  }
}


/**
* The root node in a tree
**/
class RootTreeNode extends TreeNode {
  
  static function createFromDatabase ($res, $id_col, $parent_col) {
    // load query results into tree
    $root = new RootTreeNode;
    $known_nodes = array();
    $remnant_nodes = array();
    
    while ($row = db_fetch_assoc ($res)) {
      $id = $row[$id_col];
      $parent = $row[$parent_col];
      
      $node = new TreeNode ();
      foreach ($row as $key => $val) {
        $node[$key] = $val;
      }
      
      if ($parent == null or $parent == '') {
        $root->addChild ($node);
        $known_nodes[$id] = $node;
      } else {
        $remnant_nodes[] = array ('id' => $id, 'node' => $node, 'parent' => $parent);
      }
    }
    $nodes_added_this_pass = count ($root->getChildren ());
    
    while (count ($remnant_nodes) > 0 and $nodes_added_this_pass > 0) {
      $nodes_added_this_pass = 0;
      
      foreach ($remnant_nodes as $remnant_id => $remnant) {
        foreach ($known_nodes as $known_id => $known_node) {
          if ($known_id == $remnant['parent']) {
            $known_node->addChild ($remnant['node']);
            $known_nodes[$remnant['id']] = $remnant['node'];
            
            unset ($remnant_nodes[$remnant_id]);
            $nodes_added_this_pass++;
            break;
          }
        }
      }
      
      // Looks for reminant nodes that have parents that are nodes that do not exist
      // If any of these are found, the parent is created
      if ($nodes_added_this_pass == 0) {
        $remnant = array_shift ($remnant_nodes);
        $remnant_nodes[] = $remnant;
        
        $found = false;
        foreach ($known_nodes as $known_id => $known_node) {
          if ($known_id == $remnant['parent']) {
            $found = true;
          }
        }
        
        if (! $found) {
          $node = new TreeNode ();
          $node[$id_col] = $remnant['parent'];
          $root->addChild ($node);
          
          $known_nodes[$remnant['parent']] = $node;
          
          $nodes_added_this_pass++;
        }
      }
    }
    
    return $root;
  }
  
}

interface TreeNodeMatcher {
  public function match ($node);
}


class FieldTreeNodeMatcher implements TreeNodeMatcher {
  private $field;
  private $value;
  
  public function __construct ($field, $value) {
    $this->field = $field;
    $this->value = $value;
  }
  
  public function match ($node) {
    $data = $node->getData();
    foreach ($data as $field => $value) {
      if ($this->field == $field and $this->value == $value) return true;
    }
    
    return false;
  }
}


/**
* Loads a tree of the classes, based on 'extends'
**/
function create_classes_tree () {
  $q = "SELECT id, name, extends FROM classes";
  $res = db_query ($q);
  
  $root = RootTreeNode::createFromDatabase ($res, 'name', 'extends');
  
  return $root;
}
?>
