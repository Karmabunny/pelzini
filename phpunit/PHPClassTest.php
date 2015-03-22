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
class PHPClassTest extends PHPUnit_ParserTestCase {

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
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(0, $file->classes[0]->functions);
        $this->assertCount(0, $file->functions);
        $this->assertCount(0, $file->constants);
        $this->assertCount(0, $file->enumerations);
    }


    /**
    * Abstract classes
    **/
    public function testAbstract() {
        $file = $this->parse('
            <?php
            abstract class aaa {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(true, $file->classes[0]->abstract);
        $this->assertCount(0, $file->classes[0]->functions);
    }


    /**
    * Classes with a function
    **/
    public function testFunction() {
        $file = $this->parse('
            <?php
            class aaa {
                function bbb() {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
        $this->assertEquals(false, $file->classes[0]->functions[0]->abstract);
        $this->assertEquals(false, $file->classes[0]->functions[0]->static);
        $this->assertEquals('public', $file->classes[0]->functions[0]->visibility);
    }


    /**
    * Classes with an abstract function
    **/
    public function testAbstractFunction() {
        $file = $this->parse('
            <?php
            class aaa {
                abstract function bbb() {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
        $this->assertEquals(true, $file->classes[0]->functions[0]->abstract);
        $this->assertEquals(false, $file->classes[0]->functions[0]->static);
        $this->assertEquals('public', $file->classes[0]->functions[0]->visibility);
    }


    /**
    * Classes with a static function
    **/
    public function testStaticFunction() {
        $file = $this->parse('
            <?php
            class aaa {
                static function bbb() {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
        $this->assertEquals(false, $file->classes[0]->functions[0]->abstract);
        $this->assertEquals(true, $file->classes[0]->functions[0]->static);
        $this->assertEquals('public', $file->classes[0]->functions[0]->visibility);
    }


    /**
    * Classes with a private function
    **/
    public function testPrivateFunction() {
        $file = $this->parse('
            <?php
            class aaa {
                private function bbb() {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
        $this->assertEquals(false, $file->classes[0]->functions[0]->abstract);
        $this->assertEquals(false, $file->classes[0]->functions[0]->static);
        $this->assertEquals('private', $file->classes[0]->functions[0]->visibility);
    }


    /**
    * Classes with a protected function
    **/
    public function testProtectedFunction() {
        $file = $this->parse('
            <?php
            class aaa {
                protected function bbb() {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
        $this->assertEquals(false, $file->classes[0]->functions[0]->abstract);
        $this->assertEquals(false, $file->classes[0]->functions[0]->static);
        $this->assertEquals('protected', $file->classes[0]->functions[0]->visibility);
    }


    /**
    * Classes with a (explicit) public function
    **/
    public function testPublicFunction() {
        $file = $this->parse('
            <?php
            class aaa {
                public function bbb() {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
        $this->assertEquals(false, $file->classes[0]->functions[0]->abstract);
        $this->assertEquals(false, $file->classes[0]->functions[0]->static);
        $this->assertEquals('public', $file->classes[0]->functions[0]->visibility);
    }
}

