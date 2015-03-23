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
class PHPInterfaceTest extends PHPUnit_ParserTestCase {

    /**
    * Just confirm it generally works
    **/
    public function testBasicParse() {
        $file = $this->parse('
            <?php
            interface aaa {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertCount(0, $file->classes[0]->functions);
        $this->assertCount(0, $file->functions);
        $this->assertCount(0, $file->constants);
        $this->assertCount(0, $file->enumerations);
    }


    /**
    * Just confirm it generally works
    **/
    public function testFunction() {
        $file = $this->parse('
            <?php
            interface aaa {
                function bbb();
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
    }

}

