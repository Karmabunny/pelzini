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
* Does something
* @param string $whee A param
* @param thingie $whoo Another param
**/
function thingo ($whee, thingie $whoo) {
	echo $whoot;
}

/**
* Is something
**/
class thingie extends stupid implements person,fing {
  
  /**
  * Stores some information about something
  **/
	private $foo;
  
  private $bar;     /// stores some more information
  
    
	/**
	 * Does soemthing else
	*/
	public function bar () {
		foreach ($whee as $whoo) {
			if ($whoo) {
				echo this->$foo;
			}
		}
	}

	/** Whee */
	public function baz (stupid $argh, $crazy, whee $whoo) {
		if ($argh) {
			echo $pee;
		}
	}
}

/**
* A person
**/
interface person {
	public function bar();
}

/**
* A thing
**/
interface fing {
  /**
  * somehintg
  **/
  function aaa();
  
  /** @param string $ccc does whatever **/
  function bbb($ccc);
  
  /** @param $eee hmm
  * @param $fff nothing param **/
  function ddd($eee);
}

/**
* Something that is stupid
**/
abstract class stupid {
	private $abc;

	abstract function no_more_names();
}

?>
