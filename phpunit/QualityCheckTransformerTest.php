<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/


/**
* Tests the quality check transformer
**/
class QualityCheckTransformerTest extends PHPUnit_ParserTestCase {

    public function runTestOnCode($code, $required_tags = null)
    {
        $parser_model = array($this->parse($code));
        $xform = new QualityCheckTransformer($required_tags);
        $xform->transform($parser_model);
        $this->assertCount(2, $parser_model);
        $this->assertInstanceOf('ParserDocument', $parser_model[1]);
        $this->assertEquals('Quality check report', $parser_model[1]->name);
        return $parser_model[1];
    }


    public function testPerfect()
    {
        $parser_model = array($this->parse('<?php /** perfect */'));
        $xform = new QualityCheckTransformer();
        $xform->transform($parser_model);
        $this->assertCount(1, $parser_model);
    }


    public function testBadFunction()
    {
        $doc = $this->runTestOnCode('
            <?php
            function aaa() {}
        ');
        $this->assertRegExp('!{@link aaa}.+?No summary!', $doc->description);
    }

    public function testGoodFunction()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** Good function */
            function aaa() {}
        ');
        $this->assertNotRegExp('!{@link aaa}.+?No summary!', $doc->description);
    }

    public function testBadFunctionSince()
    {
        $doc = $this->runTestOnCode('
            <?php
            function aaa() {}
        ', array('@since'));
        $this->assertRegExp('!{@link aaa}.+?No @since tag!', $doc->description);
    }

    public function testGoodFunctionSince()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** Good function
            @since v1 */
            function aaa() {}
        ', array('@since'));
        $this->assertNotRegExp('!{@link aaa}.+?No @since tag!', $doc->description);
    }

    public function testGoodFunctionBadArg()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** Good function */
            function aaa($aaa) {}
        ');
        $this->assertContains(' - {@link aaa}: <i>No description for $aaa', $doc->description);
    }

    public function testGoodFunctionGoodArg()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** Good function
            @param $aaa thing It is a thing */
            function aaa($aaa) {}
        ');
        $this->assertNotContains(' - {@link aaa}: <i>No description for $aaa', $doc->description);
    }


    public function testBadClass()
    {
        $doc = $this->runTestOnCode('
            <?php
            class aaa {}
        ');
        $this->assertRegExp('!{@link aaa}.+?No summary!', $doc->description);
    }

    public function testGoodClass()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** Good function */
            class aaa {}
        ');
        $this->assertNotRegExp('!{@link aaa}.+?No summary!', $doc->description);
    }

    public function testBadClassSince()
    {
        $doc = $this->runTestOnCode('
            <?php
            class aaa {}
        ', array('@since'));
        $this->assertRegExp('!{@link aaa}.+?No @since tag!', $doc->description);
    }

    public function testGoodClassSince()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** Good function
            @since v1 */
            class aaa {}
        ', array('@since'));
        $this->assertNotRegExp('!{@link aaa}.+?No @since tag!', $doc->description);
    }

    public function testBadClassBadFunction()
    {
        $doc = $this->runTestOnCode('
            <?php
            class aaa {
                function bbb() {}
            }
        ');
        $this->assertContains(' - {@link aaa}: <i>No summary', $doc->description);
        $this->assertContains(' - {@link bbb} from class {@link aaa}: <i>No summary', $doc->description);
    }

    public function testBadClassGoodFunction()
    {
        $doc = $this->runTestOnCode('
            <?php
            class aaa {
                /** summary */
                function bbb() {}
            }
        ');
        $this->assertContains(' - {@link aaa}: <i>No summary', $doc->description);
        $this->assertNotContains(' - {@link bbb} from class {@link aaa}: <i>No summary', $doc->description);
    }

    public function testGoodClassBadFunction()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** summary */
            class aaa {
                function bbb() {}
            }
        ');
        $this->assertNotContains(' - {@link aaa}: <i>No summary', $doc->description);
        $this->assertContains(' - {@link bbb} from class {@link aaa}: <i>No summary', $doc->description);
    }

    public function testGoodClassGoodFunction()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** summary */
            class aaa {
                /** summary */
                function bbb() {}
            }
        ');
        $this->assertNotContains(' - {@link aaa}: <i>No summary', $doc->description);
        $this->assertNotContains(' - {@link bbb} from class {@link aaa}: <i>No summary', $doc->description);
    }



    public function testBadInterface()
    {
        $doc = $this->runTestOnCode('
            <?php
            interface aaa {}
        ');
        $this->assertRegExp('!{@link aaa}.+?No summary!', $doc->description);
    }

    public function testGoodInterface()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** Good function */
            interface aaa {}
        ');
        $this->assertNotRegExp('!{@link aaa}.+?No summary!', $doc->description);
    }

    public function testBadInterfaceSince()
    {
        $doc = $this->runTestOnCode('
            <?php
            interface aaa {}
        ', array('@since'));
        $this->assertRegExp('!{@link aaa}.+?No @since tag!', $doc->description);
    }

    public function testGoodInterfaceSince()
    {
        $doc = $this->runTestOnCode('
            <?php
            /** @since v1 */
            interface aaa {}
        ', array('@since'));
        $this->assertNotRegExp('!{@link aaa}.+?No @since tag!', $doc->description);
    }

    public function testBadInterfaceBadFunction()
    {
        $doc = $this->runTestOnCode('
            <?php
            interface aaa {
                function bbb();
            }
        ');
        $this->assertContains(' - {@link aaa}: <i>No summary', $doc->description);
        $this->assertContains(' - {@link bbb} from interface {@link aaa}: <i>No summary', $doc->description);
    }
    
}

