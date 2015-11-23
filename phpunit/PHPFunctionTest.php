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
        $this->assertEquals('Just for fun', trim(strip_tags($file->functions[0]->throws[0]->description)));
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
        $this->assertEquals('Just for fun', trim(strip_tags($file->functions[0]->throws[0]->description)));
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
        $this->assertEquals('Just for fun', trim(strip_tags($file->functions[0]->throws[0]->description)));
        $this->assertEquals('DatabaseException', $file->functions[0]->throws[1]->exception);
        $this->assertEquals('When something is broken', trim(strip_tags($file->functions[0]->throws[1]->description)));
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
        $this->assertCount(1, $file->functions[0]->returns);
        $this->assertEquals('user', $file->functions[0]->returns[0]->type);
        $this->assertEquals('', trim(strip_tags($file->functions[0]->returns[0]->description)));
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
        $this->assertCount(1, $file->functions[0]->returns);
        $this->assertEquals('user', $file->functions[0]->returns[0]->type);
        $this->assertEquals('A new user', trim(strip_tags($file->functions[0]->returns[0]->description)));
    }

    public function testReturnTypeMultiple() {
        $file = $this->parse('
            <?php
            /**
            * @return user A user
            * @return group A group
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(2, $file->functions[0]->returns);
        $this->assertEquals('user', $file->functions[0]->returns[0]->type);
        $this->assertEquals('A user', trim(strip_tags($file->functions[0]->returns[0]->description)));
        $this->assertEquals('group', $file->functions[0]->returns[1]->type);
        $this->assertEquals('A group', trim(strip_tags($file->functions[0]->returns[1]->description)));
    }

    public function testReturnTypeMultipleNoDesc() {
        $file = $this->parse('
            <?php
            /**
            * @return user
            * @return group
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(2, $file->functions[0]->returns);
        $this->assertEquals('user', $file->functions[0]->returns[0]->type);
        $this->assertEquals('', trim(strip_tags($file->functions[0]->returns[0]->description)));
        $this->assertEquals('group', $file->functions[0]->returns[1]->type);
        $this->assertEquals('', trim(strip_tags($file->functions[0]->returns[1]->description)));
    }

    public function testReturnTypeMultipleOneTag() {
        $file = $this->parse('
            <?php
            /**
            * @return user|group A user or group
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(2, $file->functions[0]->returns);
        $this->assertEquals('user', $file->functions[0]->returns[0]->type);
        $this->assertEquals('A user or group', trim(strip_tags($file->functions[0]->returns[0]->description)));
        $this->assertEquals('group', $file->functions[0]->returns[1]->type);
        $this->assertEquals('A user or group', trim(strip_tags($file->functions[0]->returns[1]->description)));
    }

    public function testReturnTypeMultipleTwoTags() {
        $file = $this->parse('
            <?php
            /**
            * @return user|group A user or group
            * @return null On error
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(3, $file->functions[0]->returns);
        $this->assertEquals('user', $file->functions[0]->returns[0]->type);
        $this->assertEquals('A user or group', trim(strip_tags($file->functions[0]->returns[0]->description)));
        $this->assertEquals('group', $file->functions[0]->returns[1]->type);
        $this->assertEquals('A user or group', trim(strip_tags($file->functions[0]->returns[1]->description)));
        $this->assertEquals('null', $file->functions[0]->returns[2]->type);
        $this->assertEquals('On error', trim(strip_tags($file->functions[0]->returns[2]->description)));
    }

    public function testReturnTypeOptional() {
        $file = $this->parse('
            <?php
            /**
            * @return user? A user
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(2, $file->functions[0]->returns);
        $this->assertEquals('user', $file->functions[0]->returns[0]->type);
        $this->assertEquals('A user', trim(strip_tags($file->functions[0]->returns[0]->description)));
        $this->assertEquals('null', $file->functions[0]->returns[1]->type);
        $this->assertEquals('', trim(strip_tags($file->functions[0]->returns[1]->description)));
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
        $this->assertEquals('Database_Result', $func->returns[0]->type);
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


    public function testDeprecated1() {
        $file = $this->parse('
            <?php
            /**
            * @deprecated It\'s deprecated
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('It\'s deprecated', $file->functions[0]->deprecated);
    }
    
    public function testDeprecated2() {
        $file = $this->parse('
            <?php
            /**
            * 
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals(null, $file->functions[0]->deprecated);
    }
    
    public function testDeprecated3() {
        $file = $this->parse('
            <?php
            /**
            * @deprecated
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('', $file->functions[0]->deprecated);
    }
    
    
    public function testExample1() {
        $file = $this->parse('
            <?php
            /**
            * Description
            * @example
            * Example
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('Description', trim(strip_tags($file->functions[0]->description)));
        $this->assertEquals('Example', trim(strip_tags($file->functions[0]->example[0])));
    }
    
    public function testExample2() {
        $file = $this->parse('
            <?php
            /**
            * Description
            * @example Example
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('Description', trim(strip_tags($file->functions[0]->description)));
        $this->assertEquals('Example', trim(strip_tags($file->functions[0]->example[0])));
    }
    
    public function testExample3() {
        $file = $this->parse('
            <?php
            /**
            * Description
            * @example
            *   Line 1
            *   Line 2
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('Description', trim(strip_tags($file->functions[0]->description)));
        $this->assertEquals("Line 1\nLine 2", trim(strip_tags($file->functions[0]->example[0])));
    }
    
    public function testExample4() {
        $file = $this->parse('
            <?php
            /**
            * Description
            *
            * @example
            *   Line 1
            *   Line 2
            *
            * @example
            *     Line 3
            *     Line 4
            **/
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('Description', trim(strip_tags($file->functions[0]->description)));
        $this->assertEquals("Line 1\nLine 2", strip_tags($file->functions[0]->example[0]));
        $this->assertEquals("Line 3\nLine 4", strip_tags($file->functions[0]->example[1]));
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


    public function testNested1() {
        $file = $this->parse('
            <?php
            function aaa() {
                $func = function() {};
            }
            function bbb() {}
        ');
        $this->assertCount(2, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('bbb', $file->functions[1]->name);
    }

    public function testNested2() {
        $file = $this->parse('
            <?php
            function aaa() {
                $func = function($c) {};
            }
            function bbb() {}
        ');
        $this->assertCount(2, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('bbb', $file->functions[1]->name);
    }

    public function testNested3() {
        $file = $this->parse('
            <?php
            function aaa() {
                $func = function($c){ return function(){} };
            }
            function bbb() {}
        ');
        $this->assertCount(2, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('bbb', $file->functions[1]->name);
    }

    public function testNested4() {
        $file = $this->parse('
            <?php
            function aaa() {
                if ($aa) {
                    bbb(function($c){ return function(){} });
                }
            }
            function bbb() {}
        ');
        $this->assertCount(2, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertEquals('bbb', $file->functions[1]->name);
    }


    public function testImpliedVoid1() {
        $file = $this->parse('
            <?php
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->returns);
        $this->assertEquals('void', $file->functions[0]->returns[0]->type);
    }
    
    public function testImpliedVoid2() {
        $file = $this->parse('
            <?php
            function aaa() { return 1; }
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(0, $file->functions[0]->returns);
    }
    
    public function testImpliedVoid3() {
        $file = $this->parse('
            <?php
            /** @return string */
            function aaa() { return "hey"; }
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->returns);
        $this->assertEquals('string', $file->functions[0]->returns[0]->type);
    }
    
    public function testImpliedVoid4() {
        $file = $this->parse('
            <?php
            /** @return string */
            function aaa() {}
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->returns);
        $this->assertEquals('string', $file->functions[0]->returns[0]->type);
    }
    
    public function testImpliedVoid5() {
        $file = $this->parse('
            <?php
            function aaa() { return; }
        ');
        $this->assertCount(1, $file->functions);
        $this->assertEquals('aaa', $file->functions[0]->name);
        $this->assertCount(1, $file->functions[0]->returns);
        $this->assertEquals('void', $file->functions[0]->returns[0]->type);
    }
}
