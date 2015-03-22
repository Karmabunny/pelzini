<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';


/**
* Test dump() methods which are on various classes
**/
class PHPDumpTest extends PHPUnit_ParserTestCase {

    public function testClassDump() {
        $file = $this->parse('
            <?php
            class aaa { private $aa; }
        ');
        $this->assertCount(1, $file->classes);
        ob_start();
        $file->classes[0]->dump();
        $result = ob_get_clean();
        $this->assertNotEquals('', $result);
    }

    public function testFunctionDump() {
        $file = $this->parse('
            <?php
            function aaa (array $aa) {}
        ');
        $this->assertCount(1, $file->functions);
        ob_start();
        $file->functions[0]->dump();
        $result = ob_get_clean();
        $this->assertNotEquals('', $result);
    }

}

