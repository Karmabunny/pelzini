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
* Tokenises a javascript file.
**/
class JavascriptLexer {
  public function resetState() {
    
  }
  
  /**
  * Should return an array of zero or more Token objects
  **/
  public function process($source) {
    $offset = 0;
    $length = strlen($source);
    $tokens = array();
    
    while ($offset < $length) {
      
      // Firstly, look for single character tokens
      switch ($source[$offset]) {
        case '(':
          $token = new Token(TOKEN_OPEN_NORMAL_BRACKET);
          break;
          
        case ')':
          $token = new Token(TOKEN_CLOSE_NORMAL_BRACKET);
          break;
          
        case '{':
          $token = new Token(TOKEN_OPEN_CURLY_BRACKET);
          break;
          
        case '}':
          $token = new Token(TOKEN_CLOSE_CURLY_BRACKET);
          break;
      }
      
      // If a single character token was found, add it to the list and move on
      if ($token) {
        $tokens[] = $token;
        $token = null;
        $offset++;
        continue;
      }
      
      // Now use regular expressions to find various other tokens
      // If one is found, add it to the list and move on
      if (preg_match('/\G\/\*\*(.*?)\*\//s', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
        $tokens[] = new Token(TOKEN_DOCBLOCK, $matches[0][0]);
        $offset = $matches[0][1] + strlen($matches[0][0]) + 1;
        continue;
      }
      
      if (preg_match('/\G\/\*(.*?)\*\//s', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
        $tokens[] = new Token(TOKEN_COMMENT, $matches[0][0]);
        $offset = $matches[0][1] + strlen($matches[0][0]) + 1;
        continue;
      }
      
      if (preg_match('/\Gfunction/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
        $tokens[] = new Token(TOKEN_FUNCTION);
        $offset = $matches[0][1] + strlen($matches[0][0]) + 1;
        continue;
      }
      
      $offset++;
    }
    
    return $tokens;
  }
}

?>
