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
class PHPFunctionTest extends PHPUnit_ParserTestCase {

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
        $this->assertCount(0, $file->functions[0]->throws);
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
        $this->assertCount(0, $file->functions[0]->throws);
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
        $this->assertCount(0, $file->functions[0]->throws);
        $this->assertEquals('$aa', $file->functions[0]->args[0]->name);
        $this->assertEquals('user', $file->functions[0]->args[0]->type);
        $this->assertEquals('Test desc', trim(strip_tags($file->functions[0]->args[0]->description)));
    }


    public function testThrow() {
        $file = $this->parse('
            <?php
            /**
            * @throw Exception Just for fun
            **/
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->throws);
        $this->assertEquals('Exception', $file->functions[0]->throws[0]->exception);
        $this->assertEquals('Just for fun', $file->functions[0]->throws[0]->description);
    }

    public function testThrows() {
        $file = $this->parse('
            <?php
            /**
            * @throws Exception Just for fun
            **/
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->throws);
        $this->assertEquals('Exception', $file->functions[0]->throws[0]->exception);
        $this->assertEquals('Just for fun', $file->functions[0]->throws[0]->description);
    }

    public function testThrowThrows() {
        $file = $this->parse('
            <?php
            /**
            * @throw Exception Just for fun
            * @throws DatabaseException When something is broken
            **/
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(2, $file->functions[0]->throws);
        $this->assertEquals('Exception', $file->functions[0]->throws[0]->exception);
        $this->assertEquals('Just for fun', $file->functions[0]->throws[0]->description);
        $this->assertEquals('DatabaseException', $file->functions[0]->throws[1]->exception);
        $this->assertEquals('When something is broken', $file->functions[0]->throws[1]->description);
    }

    public function testThrowNoDesc() {
        $file = $this->parse('
            <?php
            /**
            * @throws Exception
            **/
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->throws);
        $this->assertEquals('Exception', $file->functions[0]->throws[0]->exception);
        $this->assertEquals('', $file->functions[0]->throws[0]->description);
    }

    public function testThrowEmptyImplied() {
        $file = $this->parse('
            <?php
            /**
            * @throws
            **/
            function aaa($aa) {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->throws);
        $this->assertEquals('Exception', $file->functions[0]->throws[0]->exception);
        $this->assertEquals('', $file->functions[0]->throws[0]->description);
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
    * Extra spaces around the terms
    * Taken from a real-life example which wasn't working
    **/
    public function testWithSpaces() {
        $file = $this->parse('
            <?php
            class Database {
            /**
             * Runs a query into the driver and returns the result.
             *
             * @param   string   SQL query to execute
             * @return  Database_Result
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


    /**
    * Return type only
    **/
    public function testUnicodeDescription() {
        $file = $this->parse('
            <?php
            /**
            * ÞèêéÜåäÇÉ½¼ これは何だろう
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('ÞèêéÜåäÇÉ½¼ これは何だろう', trim(strip_tags($file->functions[0]->description)));
    }


    /**
    * Code which contains braces
    **/
    public function testCodeWithBraces() {
        $file = $this->parse('
            <?php
            function aaa() {
                while (true) {
                    for ($i = 0; $i < 100; $i++) {}
                }
            }
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
    }

}
