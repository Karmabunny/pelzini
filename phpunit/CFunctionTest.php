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
class CFunctionTest extends PHPUnit_ParserTestCase {
    protected $lang = 'c';


    /**
    * Just confirm it generally works
    **/
    public function testBasicParse() {
        $file = $this->parse('
            int aaa() {
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
    * Arguments
    **/
    public function testArguments1() {
        $file = $this->parse('
            const char** aaa(int bbb, char* ccc, ddd, const char** eee, fff) {
                return NULL;
            }
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('const char**', $file->functions[0]->return_type);
        $this->assertCount(5, $file->functions[0]->args);

        $args = $file->functions[0]->args;
        $this->assertEquals('int', $args[0]->type);
        $this->assertEquals('bbb', $args[0]->name);
        $this->assertEquals('char*', $args[1]->type);
        $this->assertEquals('ccc', $args[1]->name);
        $this->assertEquals('int', $args[2]->type);
        $this->assertEquals('ddd', $args[2]->name);
        $this->assertEquals('const char**', $args[3]->type);
        $this->assertEquals('eee', $args[3]->name);
        $this->assertEquals('int', $args[4]->type);
        $this->assertEquals('fff', $args[4]->name);
    }

    /**
    * Arguments
    **/
    public function testArguments2() {
        $file = $this->parse('
            static aaa(const char** eee) {
                return NULL;
            }
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('int', $file->functions[0]->return_type);
        $this->assertCount(1, $file->functions[0]->args);

        $args = $file->functions[0]->args;
        $this->assertEquals('const char**', $args[0]->type);
        $this->assertEquals('eee', $args[0]->name);
    }

}

