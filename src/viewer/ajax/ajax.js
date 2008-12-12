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
* @since 0.1
* @author Josh
* @package Viewer
**/

var request;

/**
* Sends an AJAX request
*
* @param string url The URL to request
* @param function response_func The function to call when the result is returned.
*   The function is called with a single argument, request.responseXML
* @returns boolean True on success, false on failure
**/
function ajax_request(url, response_func) {
  if (request != null) {
    return false;
  }
  
  try {
    request = new XMLHttpRequest();
 
  } catch (e) {
    try {
      request = new ActiveXObject("Msxml2.XMLHTTP");
      
    } catch (e) {
      try {
        request = new ActiveXObject("Microsoft.XMLHTTP");
        
      } catch (e) {
        return false;
      }
    }
  }
 
  request.onreadystatechange = function() {
    if (request.readyState == 4) {
      if (request.responseXML != null) {
        response_func(request.responseXML);
      }
      request = null;
    }  
  }
  
  request.open("GET", url, true);
  request.send(null);
  
  return true;
}
