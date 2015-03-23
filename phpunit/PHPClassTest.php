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
    * Docblock loading for classes
    **/
    public function testDocbloc() {
        $file = $this->parse('
            <?php
            /**
            * This is a comment
            **/
            class aaa {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertEquals('This is a comment', trim(strip_tags($file->classes[0]->description)));
    }


    /**
    * Docblock loading for classes
    **/
    public function testDocblocAuthor() {
        $file = $this->parse('
            <?php
            /**
            * @author Josh
            **/
            class aaa {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->authors);
        $this->assertEquals('Josh', $file->classes[0]->authors[0]->name);
    }


    /**
    * Docblock loading for classes
    **/
    public function testDocblocAuthorCascade() {
        $file = $this->parse('
            <?php
            /**
            * @author Josh
            **/
            class aaa {
                function bbb() {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->authors);
        $this->assertEquals('Josh', $file->classes[0]->authors[0]->name);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
        $this->assertEquals(false, $file->classes[0]->functions[0]->abstract);
        $this->assertEquals(false, $file->classes[0]->functions[0]->static);
        $this->assertEquals('public', $file->classes[0]->functions[0]->visibility);
        $this->assertCount(1, $file->classes[0]->functions[0]->authors);
        $this->assertEquals('Josh', $file->classes[0]->functions[0]->authors[0]->name);
    }


    /**
    * Class extending another
    **/
    public function testExtends() {
        $file = $this->parse('
            <?php
            class aaa extends bbb {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertEquals('bbb', $file->classes[0]->extends);
    }


    /**
    * Class implementing an interface
    **/
    public function testImplements() {
        $file = $this->parse('
            <?php
            class aaa implements bbb {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->implements);
        $this->assertEquals('bbb', $file->classes[0]->implements[0]);
    }


    /**
    * Class implementing multiple interfaces
    **/
    public function testImplementsMultiple() {
        $file = $this->parse('
            <?php
            class aaa implements bbb, ccc {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(2, $file->classes[0]->implements);
        $this->assertEquals('bbb', $file->classes[0]->implements[0]);
        $this->assertEquals('ccc', $file->classes[0]->implements[1]);
    }


    /**
    * Class extending and implementing multiple interfaces
    **/
    public function testExtendsImplements() {
        $file = $this->parse('
            <?php
            class aaa extends bbb implements ccc, ddd {}
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertEquals('bbb', $file->classes[0]->extends);
        $this->assertCount(2, $file->classes[0]->implements);
        $this->assertEquals('ccc', $file->classes[0]->implements[0]);
        $this->assertEquals('ddd', $file->classes[0]->implements[1]);
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
    * Classes with a function with a docblock
    **/
    public function testFunctionDocblock() {
        $file = $this->parse('
            <?php
            class aaa {
                /** A function */
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
        $this->assertEquals(false, $file->classes[0]->functions[0]->final);
        $this->assertEquals('public', $file->classes[0]->functions[0]->visibility);
        $this->assertEquals('A function', trim(strip_tags($file->classes[0]->functions[0]->description)));
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
        $this->assertEquals(false, $file->classes[0]->functions[0]->final);
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
        $this->assertEquals(false, $file->classes[0]->functions[0]->final);
        $this->assertEquals('public', $file->classes[0]->functions[0]->visibility);
    }


    /**
    * Classes with a final function
    **/
    public function testFinalFunction() {
        $file = $this->parse('
            <?php
            class aaa {
                final function bbb() {}
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->functions);
        $this->assertEquals('bbb', $file->classes[0]->functions[0]->name);
        $this->assertEquals(false, $file->classes[0]->functions[0]->abstract);
        $this->assertEquals(false, $file->classes[0]->functions[0]->static);
        $this->assertEquals(true, $file->classes[0]->functions[0]->final);
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
        $this->assertEquals(false, $file->classes[0]->functions[0]->final);
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


    /**
    * Classes with a variable
    **/
    public function testVariable() {
        $file = $this->parse('
            <?php
            class aaa {
                $bbb;
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->variables);
        $this->assertEquals('$bbb', $file->classes[0]->variables[0]->name);
        $this->assertEquals('private', $file->classes[0]->variables[0]->visibility);
    }


    /**
    * Classes with a variable with a dockblock
    **/
    public function testVariableDocblock() {
        $file = $this->parse('
            <?php
            class aaa {
                /** A variable */
                $bbb;
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->variables);
        $this->assertEquals('$bbb', $file->classes[0]->variables[0]->name);
        $this->assertEquals('private', $file->classes[0]->variables[0]->visibility);
        $this->assertEquals('A variable', trim(strip_tags($file->classes[0]->variables[0]->description)));
    }


    /**
    * Classes with a (explicit) private variable
    **/
    public function testPrivateVariable() {
        $file = $this->parse('
            <?php
            class aaa {
                private $bbb;
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->variables);
        $this->assertEquals('$bbb', $file->classes[0]->variables[0]->name);
        $this->assertEquals('private', $file->classes[0]->variables[0]->visibility);
    }


    /**
    * Classes with a protected variable
    **/
    public function testProtectedVariable() {
        $file = $this->parse('
            <?php
            class aaa {
                protected $bbb;
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->variables);
        $this->assertEquals('$bbb', $file->classes[0]->variables[0]->name);
        $this->assertEquals('protected', $file->classes[0]->variables[0]->visibility);
    }


    /**
    * Classes with a public variable
    **/
    public function testPublicVariable() {
        $file = $this->parse('
            <?php
            class aaa {
                public $bbb;
            }
        ');
        $this->assertCount(1, $file->classes);
        $this->assertEquals('aaa', $file->classes[0]->name);
        $this->assertEquals(false, $file->classes[0]->abstract);
        $this->assertCount(1, $file->classes[0]->variables);
        $this->assertEquals('$bbb', $file->classes[0]->variables[0]->name);
        $this->assertEquals('public', $file->classes[0]->variables[0]->visibility);
    }
    
}

