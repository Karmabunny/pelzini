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
* Contains the {@link JavascriptParser} class
*
* @package Parsers
* @author Josh
* @since 0.2
**/

/**
* Does the complete parsing of a javascript file.
**/
class JavascriptParser {
  private $lexer;
  private $analyser;
  
  public function __construct () {
    $this->lexer = new JavascriptLexer();
    $this->analyser = new JavascriptAnalyser();
  }
  
  /**
  * Parses a file
  *
  * @param string $filename The file to parse
  * @return A ParserFile object, or null if there was an error
  **/
  public function parseFile ($filename) {
    global $dpgBaseDirectory;
    
    $this->lexer->resetState();
    $this->analyser->resetState();
    
    $source = @file_get_contents($dpgBaseDirectory . $filename);
    if ($source == null) return null;
    
    $tokens = $this->lexer->process($source);
    if ($tokens === null) return null;
    
    //echo "<style>i {color: #777;}</style>";
    //echo '<pre>Tokens for file ', $filename, "\n";
    //foreach ($tokens as $i => $t) echo "<b>{$i}</b> {$t->getTypeName()} <i>{$t->getValue()}</i>\n";
    //echo '</pre>';
    
    $file = new ParserFile();
    $file->name = $filename;
    $file->source = $source;
    
    $result = $this->analyser->process($tokens, $file);
    if ($result === false) return null;
    
    return $file;
  }
}

?>
