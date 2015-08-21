<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';


/**
* Test PHP namespace parsing
**/
class PHPNamespaceTest extends PHPUnit_ParserTestCase {

	public function testNoNamespace() {
        $file = $this->parse('
            <?php
            class aaa {}
        ');
        $this->assertNull($file->namespace);
    }
    
    
    public function testNamespace1() {
        $file = $this->parse('
            <?php
            namespace aaa;
            class aaa {}
        ');
        $this->assertCount(1, $file->namespace);
        $this->assertEquals('aaa', $file->namespace[0]);
    }


    public function testNamespace2() {
        $file = $this->parse('
            <?php
            namespace aaa\bbb;
            class aaa {}
        ');
        $this->assertCount(2, $file->namespace);
        $this->assertEquals('aaa', $file->namespace[0]);
        $this->assertEquals('bbb', $file->namespace[1]);
    }


	public function testNamespaceWithUse() {
        $file = $this->parse('
            <?php
            namespace aaa\bbb;
            use ccc\ddd;
            class aaa {}
        ');
        $this->assertCount(2, $file->namespace);
        $this->assertEquals('aaa', $file->namespace[0]);
        $this->assertEquals('bbb', $file->namespace[1]);
    }
    

	public function testNamespaceSilly1() {
        $file = $this->parse('
            <?php
            namespace aaa\bbb;
            namespace ignored;
            class aaa {}
        ');
        $this->assertCount(2, $file->namespace);
        $this->assertEquals('aaa', $file->namespace[0]);
        $this->assertEquals('bbb', $file->namespace[1]);
    }
    
    
    public function testNamespaceSilly2() {
        $file = $this->parse('
            <?php
            namespace aaa\bbb;
            namespace ignored\really;
            class aaa {}
        ');
        $this->assertCount(2, $file->namespace);
        $this->assertEquals('aaa', $file->namespace[0]);
        $this->assertEquals('bbb', $file->namespace[1]);
    }
}

