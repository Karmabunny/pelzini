<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';


/**
* Test PHP function parsing
**/
class JSFunctionTest extends PHPUnit_ParserTestCase {
    protected $lang = 'js';


    /**
    * Just confirm it generally works
    **/
    public function testBasicParse() {
        $file = $this->parse('
            function aaa() {
                return 0;
            }
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(0, $file->functions[0]->args);
        $this->assertCount(0, $file->classes);
        $this->assertCount(0, $file->constants);
        $this->assertCount(0, $file->enumerations);
    }

    /**
    * Just confirm it generally works
    **/
    public function testDocblock() {
        $file = $this->parse('
            /** Does something */
            function aaa() {
                return 0;
            }
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('Does something', trim(strip_tags($file->functions[0]->description)));
    }

}

