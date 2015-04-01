<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';


class XMLOutputterTest extends PHPUnit_ParserTestCase {
    const TEMP = '/tmp/pelzini-unit-test-result';

    /**
    * @medium
    **/
    public function testXMLOutputter() {
        $parser_model = $this->completeModel();

        $config = new Config();
        $outputter = new XmlOutputter(self::TEMP);
        $outputter->output($parser_model, $config);

        $this->assertTrue(file_exists(self::TEMP));

        // TODO: Check XML matches what we expect
    }

    public function tearDown() {
        @unlink(self::TEMP);
    }

}
