<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/


/**
* Test dump() methods which are on various classes
**/
class PHPUnit_ParserTestCase extends PHPUnit_Framework_TestCase {
    protected $lang = 'php';
    private $parser;

    protected function setUp() {
        if ($this->lang == 'js') {
            $this->parser = new JavascriptParser();
        } else if ($this->lang == 'c') {
            $this->parser = new CParser();
        } else if ($this->lang == 'php') {
            $this->parser = new PhpParser();
        } else {
            throw new Exception('Invalid language');
        }
    }

    protected function parse($code) {
        $file = $this->parser->parseFile('', 'data://text/plain;base64,' . base64_encode($code));
        $file->treeWalk('process_javadoc_tags');
        return $file;
    }
}

