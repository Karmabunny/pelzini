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
class PHPTagsTest extends PHPUnit_ParserTestCase {

    public function testSince() {
        $file = $this->parse('
            <?php
            /**
            * @since 1.0
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('1.0', $file->functions[0]->since);
    }


    public function testSee() {
        $file = $this->parse('
            <?php
            /**
            * @see bbb
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->see);
        $this->assertEquals('bbb', $file->functions[0]->see[0]);
    }


    public function testTable() {
        $file = $this->parse('
            <?php
            /**
            * @table select bbb get records
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->tables);
        $this->assertEquals('bbb', $file->functions[0]->tables[0]->name);
        $this->assertEquals('SELECT', $file->functions[0]->tables[0]->action);
        $this->assertEquals('get records', $file->functions[0]->tables[0]->description);
    }


    public function testTableNoAction() {
        $file = $this->parse('
            <?php
            /**
            * @table bbb get records
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->tables);
        $this->assertEquals('bbb', $file->functions[0]->tables[0]->name);
        $this->assertEquals('get records', $file->functions[0]->tables[0]->description);
    }


    public function testTag() {
        $file = $this->parse('
            <?php
            /**
            * @tag bbb
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->info_tags);
        $this->assertEquals('bbb', $file->functions[0]->info_tags[0]);
    }


    public function testPackage() {
        $file = $this->parse('
            <?php
            /**
            * @package bbb
            **/
            
            /** test */
            function aaa() {}
        ');
        $this->assertEquals('bbb', $file->package);
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
    }

}

