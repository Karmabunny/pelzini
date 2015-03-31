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

    public function setUp() {
        if (!function_exists('sqlite_open')) {
            $this->markTestSkipped('SQLite not available');
        }
        parent::setUp();
        @unlink(self::TEMP);
    }

    public function tearDown() {
        parent::tearDown();
        @unlink(self::TEMP);
    }


    /**
    * Just output some content
    **/
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


    /**
    * Do an update (i.e. the same ProjectCode)
    **/
    public function testUpdating() {
        $parser_model = $this->completeModel();
        $config = new Mock_Config();

        // First run
        $outputter = new SqliteOutputter(self::TEMP);
        ob_start();
        $outputter->check_layout(__DIR__ . '/../src/processor/database.layout');
        ob_end_clean();
        $outputter->output($parser_model, $config);
        $this->assertTrue(file_exists(self::TEMP));

        // Second run
        $outputter = new SqliteOutputter(self::TEMP);
        ob_start();
        $outputter->check_layout(__DIR__ . '/../src/processor/database.layout');
        ob_end_clean();
        $outputter->output($parser_model, $config);
        $this->assertTrue(file_exists(self::TEMP));
    }

}
