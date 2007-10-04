<?php

function thingo ($whee, thingie $whoo) {
	echo $whoot;
}

class thingie extends stupid implements person,fing {
	private $foo;

	public function bar () {
		foreach ($whee as $whoo) {
			if ($whoo) {
				echo this->$foo;
			}
		}
	}

	public function baz (stupid $argh, $crazy, whee $whoo) {
		if ($argh) {
			echo $pee;
		}
	}
}

interface person {
	public function bar();
}

interface fing { }

abstract class stupid {
	private $abc;

	abstract function no_more_names();
}

?>
