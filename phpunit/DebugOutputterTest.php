<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';


class DebugOutputterTest extends PHPUnit_ParserTestCase {

    public function testDebugOutputter() {
        $parser_model = $this->completeModel();

        $config = new Config();
        $outputter = new DebugOutputter();

        ob_start();
        $outputter->output($parser_model, $config);
        $result = ob_get_clean();

        $this->assertNotEquals('', $result);
    }

}
