/*
Copyright 2008 Josh Heidenreich

This file is part of docu.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with docu.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* Various useful functions
*
* @package Viewer
* @author Josh Heidenreich
* @since 0.1
**/


/**
* This is used by the "-" buttons to hide content areas
**/
function hide_content(event) {
  var elem, nodes, i;
  
  // Finds the IMG element in a cross-browser way
  if (typeof (event) != 'undefined') {
    elem = event.currentTarget;
  } else if (typeof (window.event) != 'undefined') {
    elem = window.event.srcElement;
  } else {
    return;
  }
  
  // Looks for nodes that are children of this parents node
  // and if they are of the class 'content', turn them off
  nodes = elem.parentNode.getElementsByTagName('*');
  for (i = 0; i < nodes.length; i++) {
    if (nodes[i].className.indexOf('content') != -1) {
      nodes[i].style.display = 'none';
    }
  }
  
  // Looks for nodes that are + or - buttons, and turns them into + buttons
  nodes = elem.parentNode.getElementsByTagName('img');
  for (i = 0; i < nodes.length; i++) {
    if (nodes[i].className.indexOf('showhide') != -1) {
      nodes[i].src = 'images/icon_add.png';
      nodes[i].title = 'Show this result';
      nodes[i].onclick = show_content;
    }
  }
  
  // Changes the button the a +
  elem.src = 'images/icon_add.png';
  elem.title = 'Show this result';
  elem.onclick = show_content;
}


/**
* This is used by the "+" buttons to show content areas
**/
function show_content(event) {
  var elem;
  
  // Finds the IMG element in a cross-browser way
  if (typeof (event) != 'undefined') {
    elem = event.currentTarget;
  } else if (typeof (window.event) != 'undefined') {
    elem = window.event.srcElement;
  } else {
    return;
  }
  
  // Looks for nodes that are children of this parents node
  // and if they are of the class 'content', turn them on
  var nodes = elem.parentNode.getElementsByTagName('*');
  for (var i = 0; i < nodes.length; i++) {
    if (nodes[i].className.indexOf('content') != -1) {
      nodes[i].style.display = '';
    }
  }
  
  // Looks for nodes that are + or - buttons, and turns them into - buttons
  nodes = elem.parentNode.getElementsByTagName('img');
  for (i = 0; i < nodes.length; i++) {
    if (nodes[i].className.indexOf('showhide') != -1) {
      nodes[i].src = 'images/icon_remove.png';
      nodes[i].title = 'Hide this result';
      nodes[i].onclick = hide_content;
    }
  }
  
  // Changes the button the a +
  elem.src = 'images/icon_remove.png';
  elem.title = 'Hide this result';
  elem.onclick = hide_content;
}
