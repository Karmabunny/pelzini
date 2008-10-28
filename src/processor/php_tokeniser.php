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
* PHP Parser
* @package Parsers
**/

/**
* This is the parser for PHP files. It converts a file from the raw PHP into a document tree
**/
class PhpTokeniser {
  static function CreateInstance () {
    return new PhpTokeniser;
  }

  function Tokenise ($filename) {
    global $dpgBaseDirectory;
    
    
    $source = @file_get_contents($dpgBaseDirectory . $filename);
    if ($source == null) return null;
    
    $tokens = token_get_all($source);
    
    
    $current_file = new ParserFile ();
    $current_file->name = $filename;
    $current_file->source = $source;
    
    unset ($source);
    
    // the vars that make it tick
    $current_function = null;
    $inside_function = null;
    $current_class = null;
    $inside_class = null;
    $current_constant = null;
    $next = null;
    $brace_count = 0;
    $abstract = false;
    $static = false;
    $final = false;
    $next_comment = null;
    
    //echo '<style>span {color: green;}</style>';
    //echo '<pre>';
    foreach ($tokens as $token) {
      // debugger
      //if (is_string($token)) {
      //  echo htmlspecialchars($token) . "\n";
      //} else {
      //  echo htmlspecialchars(token_name($token[0]) . ' ' . $token[1]) . "\n";
      //}
      
      if (is_string($token)) {
        // opening of a function or class block
        if ($token == '{') {
          // opening of function
          if ($current_function != null) {
            if ($inside_class != null) {
              if ($visibility != null) {
                $current_function->visibility = $visibility;
                $visibility = null;
              }
              $inside_class->functions[] = $current_function;
              
            } else {
              $current_file->functions[] = $current_function;
            }
            
            $current_function->post_load();
            $inside_function = $current_function;
            $current_function = null;

          // opening of class
          } else if ($current_class != null) {
            if ($visibility != null) {
              $current_class->visibility = $visibility;
              $visibility = null;
            }
            $current_file->classes[] = $current_class;
            $inside_class = $current_class;
            $current_class = null;
            $next = null;

          } else {
            $brace_count++;
          }
        

        // function in an interface
        } else if ($token == ';') {
          if ($current_function != null) {
            if ($visibility != null) {
              $current_function->visibility = $visibility;
              $visibility = null;
            }
            $current_function->post_load();
            $inside_class->functions[] = $current_function;
            $current_function = null;
          }


        // closing of a class or function block
        } else if ($token == '}') {
          if ($brace_count == 0) {
            if ($inside_function != null) {
              $inside_function = null;
            } else if ($inside_class != null) {
              $inside_class = null;
            }
            
          } else {
            $brace_count--;
          }
        }
        
      } else {
        // token array
        list($id, $text) = $token;
        
        switch ($id) {
          case T_CURLY_OPEN:
            $brace_count++;
            break;
            
            
          case T_DOC_COMMENT:
            if ($next_comment) {
              $current_file->apply_comment($next_comment);
              $next_comment = null;
            }
            $next_comment = $text;
            break;
            
            
          case T_FUNCTION:
            $current_function = new ParserFunction();
            if ($abstract) {
              $current_function->abstract = true;
              $abstract = false;
            }
            if ($static) {
              $current_function->static = true;
              $static = false;
            }
            if ($final) {
              $current_function->final = true;
              $final = false;
            }
            if ($next_comment) {
              $current_function->apply_comment($next_comment);
              $next_comment = null;
            }
            break;
            
            
          case T_CLASS:
            $current_class = new ParserClass();
            if ($abstract) {
              $current_class->abstract = true;
              $abstract = false;
            } else if ($final) {
              $current_class->final = true;
              $final = false;
            }
            if ($next_comment) {
              $current_class->apply_comment($next_comment);
              $next_comment = null;
            }
            break;
            
            
          case T_INTERFACE:
            $current_class = new ParserInterface();
            if ($next_comment) {
              $current_class->apply_comment($next_comment);
              $next_comment = null;
            }
            break;
            
            
          // variables are added according to scope
          // will become a ParserVariable or a ParserParameter
          case T_VARIABLE:
            if ($current_function != null) {
              $parameter = new ParserParameter();
              $parameter->name = $text;
              if ($param_type != null) {
                $parameter->type = $param_type;
                $param_type = null;
              }
              $current_function->params[] = $parameter;

            } else if (($inside_class != null) && ($inside_function == null)) {
              $variable = new ParserVariable();
              $variable->name = $text;
              $variable->visibility = $visibility;
              $visibility = null;
              if ($static) {
                $variable->static = true;
                $static = false;
              }
              if ($next_comment) {
                $variable->apply_comment($next_comment);
                $next_comment = null;
              }
              $inside_class->variables[] = $variable;
            }
            break;
            
            
          // A string my become an extends, implements
          // function name or class name
          case T_STRING:
            if ($next != null) {
              if ($next == T_EXTENDS) {
                $current_class->extends = $text;
                $next = null;
              } else if ($next == T_IMPLEMENTS) {
                $current_class->implements[] = $text;
              }

            } else if ($current_function != null) {
              if ($current_function->name == '') {
                $current_function->name = $text;
              } else {
                $param_type = $text;
              }

            } else if ($current_class != null) {
              $current_class->name = $text;

            } else if (strcmp ($text, 'define') == 0) {
              $current_constant = new ParserConstant();
            }
            break;
            
            
          case T_CONSTANT_ENCAPSED_STRING:
            // removes quotes, etc
            $name_search = array("\'", '\"', "'", '"');
            $name_replace = array("'", '"', '', '');
            $text = str_replace($name_search, $name_replace, $text);
            
            if ($current_constant) {
              if ($current_constant->name == null) {
                $current_constant->name = $text;
              } else {
                $current_constant->value = $text;
                $current_file->constants[] = $current_constant;
                $current_constant = null;
              }
            }
            break;
            
            
          case T_LNUMBER:
            if ($current_constant) {
              if ($current_constant->name != null) {
                $current_constant->value = $text;
                $current_file->constants[] = $current_constant;
                $current_constant = null;
              }
            }
            break;
            
            
          // visibility
          case T_PRIVATE:
            $visibility = 'private';
            break;
            
          case T_PROTECTED:
            $visibility = 'protected';
            break;
            
          case T_PUBLIC:
            $visibility = 'public';
            break;
            
            
          // the next token after one of these does the grunt work
          case T_EXTENDS:
          case T_IMPLEMENTS:
            $next = $id;
            break;
            
          case T_ABSTRACT:
            $abstract = true;
            break;
            
          case T_STATIC:
            if (! $inside_function) $static = true;
            break;
            
          case T_FINAL:
            $final = true;
            break;
            
        }
      }
    }
    //echo '</pre>';
    
    return $current_file;
  }
}

?>
