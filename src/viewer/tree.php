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
* A simple tree system
*
* @author Josh
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
  
  
  /**
  * Adds a child node to this node
  **/
  public function addChild (TreeNode $child) {
    $this->children[] = $child;
    $child->parent = $this;
  }
  
  /**
  * Returns a list of all the child nodes of this node
  **/
  public function getChildren () {
    return $this->children;
  }
  
  /**
  * Returns all of the data of this node
  **/
  public function getData() {
    return $this->data;
  }
  
  
  /**
  * Returns true if a specific data field exists, and false otherwise
  **/
  public function offsetExists ($index) {
    return isset ($this->data[$index]);
  }
  
  /**
  * Returns the value of a specific data field
  **/
  public function offsetGet ($index) {
    return $this->data[$index];
  }
  
  /**
  * Sets the value of a specific data field
  **/
  public function offsetSet ($index, $value) {
    $this->data[$index] = $value;
  }
  
  /**
  * Removes a specific data field
  **/
  public function offsetUnset ($index) {
    unset ($this->data[$index]);
  }
  
  
  /**
  * Finds a node in the database
  *
  * @param TreeNodeMatcher $matcher The class which is used to determine if a node should be found or not
  **/
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
  
  /**
  * Used for debugging only
  **/
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
  
  /**
  * Creates a tree based on a set of nodes in the database
  *
  * @param resource $res A database resource created with db_query
  * @param string $id_col The name of the identification column in the database query
  * @param string $parent_col The name of the column in the database that references the id column of another record
  * @returns The loaded tree
  **/
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


/**
* The generic interface for classes which look for nodes that match specific conditions
**/
interface TreeNodeMatcher {
  /**
  * Will return true if the node matches the specified condition, or false otherwise
  *
  * @param TreeNode $node The node to check
  * @return boolean True if the node matches, false otherwise
  **/
  public function match ($node);
}


/**
* Finds nodes in the tree which have a specified field which matches a specified value
**/
class FieldTreeNodeMatcher implements TreeNodeMatcher {
  private $field;
  private $value;
  
  public function __construct ($field, $value) {
    $this->field = $field;
    $this->value = $value;
  }
  
  /**
  * Will return true if the node matches the specified condition, or false otherwise
  *
  * @param TreeNode $node The node to check
  * @return boolean True if the node matches, false otherwise
  **/
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
