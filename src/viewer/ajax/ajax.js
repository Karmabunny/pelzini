var request;
  
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
