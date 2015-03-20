<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of Pelzini.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
*/

class PHPFunctionTest extends PHPUnit_Framework_TestCase {
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
            function aaa() {}
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
    public function testArgument() {
        $file = $this->parse('
            <?php
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
    }


    /**
    * Typehinted arguments - array
    **/
    public function testArgumentTypehintArray() {
        $file = $this->parse('
            <?php
            function aaa(array $aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('array', $file->functions[0]->args[0]->type);
    }


    /**
    * Typehinted arguments - inbuilt classes
    **/
    public function testArgumentTypehintInbuiltClass() {
        $file = $this->parse('
            <?php
            function aaa(DOMDocument $aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('DOMDocument', $file->functions[0]->args[0]->type);
    }


    /**
    * Typehinted arguments - user classes
    **/
    public function testArgumentTypehintUserClass() {
        $file = $this->parse('
            <?php
            class user{}
            function aaa(user $aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('user', $file->functions[0]->args[0]->type);
    }


    /**
    * Argument - name type desc
    **/
    public function testArgumentDescForward() {
        $file = $this->parse('
            <?php
            /**
            * @param $aa user Test desc
            **/
            function aaa(user $aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('user', $file->functions[0]->args[0]->type);
        $this->assertEquals('Test desc', trim(strip_tags($file->functions[0]->args[0]->description)));
    }


    /**
    * Argument - type name desc
    **/
    public function testArgumentDescReverse() {
        $file = $this->parse('
            <?php
            /**
            * @param user $aa Test desc
            **/
            function aaa(user $aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('user', $file->functions[0]->args[0]->type);
        $this->assertEquals('Test desc', trim(strip_tags($file->functions[0]->args[0]->description)));
    }


    /**
    * No typehint but still a type in the tag - name type desc
    **/
    public function testArgumentNoTypehintForward() {
        $file = $this->parse('
            <?php
            /**
            * @param $aa user Test desc
            **/
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('user', $file->functions[0]->args[0]->type);
        $this->assertEquals('Test desc', trim(strip_tags($file->functions[0]->args[0]->description)));
    }


    /**
    * No typehint but still a type in the tag - type name desc
    **/
    public function testArgumentNoTypehintReverse() {
        $file = $this->parse('
            <?php
            /**
            * @param user $aa Test desc
            **/
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('user', $file->functions[0]->args[0]->type);
        $this->assertEquals('Test desc', trim(strip_tags($file->functions[0]->args[0]->description)));
    }


    /**
    * Type only in tag
    **/
    public function testArgumentTypeOnlyTag() {
        $file = $this->parse('
            <?php
            /**
            * @param user Test desc
            **/
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->args);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('user', $file->functions[0]->args[0]->type);
        $this->assertEquals('Test desc', trim(strip_tags($file->functions[0]->args[0]->description)));
    }


    /**
    * Return type only
    **/
    public function testReturnType() {
        $file = $this->parse('
            <?php
            /**
            * @return user
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('user', $file->functions[0]->return_type);
        $this->assertEquals('', trim(strip_tags($file->functions[0]->return_description)));
    }


    /**
    * Retrun type and description
    **/
    public function testReturnTypeDesc() {
        $file = $this->parse('
            <?php
            /**
            * @return user A new user
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('user', $file->functions[0]->return_type);
        $this->assertEquals('A new user', trim(strip_tags($file->functions[0]->return_description)));
    }


    /**
    * A real-life code sample which wasn't working
    **/
    public function testReal1() {
        $file = $this->parse('
            <?php
            class Database {
            /**
            * Runs a query into the driver and returns the result.
            *
            * @param string SQL query to execute
            * @return Database_Result
            */
            public function query($sql = \'\')
            {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertCount(1, $file->classes[0]->functions);
        $func = $file->classes[0]->functions[0];
        $this->assertEquals('query', $func->name);
        $this->assertEquals('Database_Result', $func->return_type);
        $this->assertEquals('Runs a query into the driver and returns the result.', trim(strip_tags($func->description)));
        $this->assertCount(1, $func->args);
        $this->assertEquals('$sql', $func->args[0]->name);
        $this->assertEquals('string', $func->args[0]->type);
        $this->assertEquals('SQL query to execute', trim(strip_tags($func->args[0]->description)));
    }
}

