<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/


/**
* Test PHP class parsing
**/
class PHPClassTest extends PHPUnit_Framework_TestCase {
    private $parser;

    protected function setUp() {
        $this->parser = new PhpParser();
    }

    private function parse($code) {
        $file = $this->parser->parseFile('', 'data://text/plain;base64,' . base64_encode($code));
        $file->treeWalk('process_javadoc_tags');
        return $file;
    }


    /**
    * Just confirm it generally works
    **/
    public function testBasicParse() {
        $file = $this->parse('
            <?php
            class aaa {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertCount(0, $file->functions);
        $this->assertCount(0, $file->constants);
        $this->assertCount(0, $file->enumerations);
    }
}

