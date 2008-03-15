<?php

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
