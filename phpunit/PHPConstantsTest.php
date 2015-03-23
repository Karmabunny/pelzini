<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';


/**
* Test PHP class parsing
**/
class PHPConstantsTest extends PHPUnit_ParserTestCase {

    /**
    * Just confirm it generally works
    **/
    public function testBasicParse() {
        $file = $this->parse('
            <?php
            define("AAA", true);
        ');
        $this->assertCount(1, $file->constants);
        $this->assertEquals('AAA', $file->constants[0]->name);
    }

}

