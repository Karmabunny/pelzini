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
* Tests the PHP parsing system
*
* @package Test suite
* @author Josh
* @since 0.1
**/


/*
ENABLE THIS TO VALIDATE THE FILE

class php_missing_base_class {}
interface php_missing_interface {}
*/


/**
* A constant
* @author josh
* @since 0.1
**/
define ('PHP_DOCUMENTED_CONST', 42);

define ('PHP_STRING_CONST', 'blah');
define ('PHP_NUMBER_CONST', 42);


/**
* Does not contain arguments
*/
function php_documented_function () {
  return;
}

/**
* Contains arguments
* @param string $variable The argument
* @author Monkey Man <monkey.man@example.com>
* @since r567
**/
function php_documented_arguments_function ($arg1, $arg2) {
  return;
}

/**
* Contains arguments with defaults
* @param string $arg1 Default is 'detault1'
* @param string $arg2 Default is 100
* @param string $arg3 Default is 15.2
* @param string $arg4 Default is null
* @author Josh <josh@example.com>
* @author Bob <bob@example.com>
**/
function php_documented_default_arguments_function ($arg1 = 'default1', $arg2 = 100, $arg3 = 15.2, $arg4 = null) {
  return;
}

/**
* Contatins typehinted arguments
* @param php_documented_super_class $arg1 Argument 1
* @param php_documented_base_class $arg2 Argument 2
* @param php_missing_class $arg3 Argument 3 (class is not defined in this code)
* @author Josh, initial work
* @author bob, additional work
**/
function php_documented_typehinting_function (php_documented_super_class $arg1, php_documented_base_class $arg2, php_missing_class $arg3) {
  return;
}

/**
* Has something.
*   @param $arg1 string something weird
* @param $arg2 something else @param
 * @param $arg2 Redefined.
 * @param string $arg3 defined as one thing in code and something else in comment
* @param integer $arg100 who knows? **/
function php_badly_documented_function ($arg1, $arg2 = 100, php_documented_super_class $arg3) {
  return;
}

/**
* Parent class is not defined in this code
* @author josh <josh@example.com> did some work
* @author bob <bob@example.com> did some more work
* @since 0.1
**/
abstract class php_documented_base_class extends php_missing_base_class {
  private $base_private;
  protected $base_protected;
  public $base_public;
  
  /**
  * This method is final. It is in an abstract class.
  **/
  final public function php_documented_final_method () {
    return 1337;
  }
}

/**
* A super class
* - extends php_documented_base_class.
* - implements php_documented_interface
* - implements php_missing_interface (not defined in this code)
**/
final class php_documented_super_class extends php_documented_base_class implements php_documented_interface, php_missing_interface {
  private $super_private;
  protected $super_protected;
  public $super_public;
  
  /**
  * Stores some information about something
  * @author josh, 2008-10-10
  * @since 0.1
  **/
  private $php_var1;
  
  private $php_var2;     /// stores some more information
  
  static public $php_var3;
  
  
  /**
  *** Does soemthing else
  * @author josh, somehting
  * @since 0.1
  **/
  public function php_documented_method () {
    foreach ($foo as $bar) {
      if ($baz) {
        echo $this->php_var2;
      }
    }                                                                  
  }

  /**
  * Contains arguments
  * @param string $arg1 The first argument
  * @param string $arg2 The second argument
  * @author
  **/
  public function php_documented_arguments_method ($arg1, $arg2) {
    static $random_variable;
    if ($arg1) {
      echo $this->php_var1;
    }
  }
  
  /** A static method */
  static public function php_documented_static_method () {
    return 42;
  }
  
  
  function aaa() {}
  function bbb() {}
  function ccc($foo) {}
  function ddd($foo) {}
  function eee(php_documented_base_class $foo) {}
  function fff($foo = 100) {}
}

/**
* An interface
* @author peter
* @since 0.1
**/
interface php_documented_interface {
  
  function aaa();
  
  /**
  * Something
  * @author josh
  **/
  function bbb();
  
  /** @param string $foo does whatever **/
  function ccc($foo);
  
  /** @param $foo hmm
  * @param $foo nothing param **/
  function ddd($foo);
  
  function eee(php_documented_base_class $foo);
  
  function fff($foo = 100);
}
?>
