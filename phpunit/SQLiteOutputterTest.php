<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';
require_once 'Mock_Config.php';


class SQLiteOutputterTest extends PHPUnit_ParserTestCase {
    const TEMP = '/tmp/pelzini-unit-test-result';

    public function testSQLiteOutputter() {
        $parser_model = $this->completeModel();

        $config = new Mock_Config();
        $outputter = new SqliteOutputter(self::TEMP);

        ob_start();
        $outputter->check_layout(__DIR__ . '/../src/processor/database.layout');
        ob_end_clean();

        $outputter->output($parser_model, $config);
        $this->assertTrue(file_exists(self::TEMP));

        // TODO: Check XML matches what we expect
    }

    public function tearDown() {
        @unlink(self::TEMP);
    }

}
