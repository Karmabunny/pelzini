<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';
require_once 'Mock_Config.php';


abstract class DatabaseTestCase extends PHPUnit_ParserTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }


    /**
    * @return DatabaseOutputter
    **/
    protected abstract function getOutputter();

    /**
    * Connect to the db
    **/
    protected abstract function connect();

    /**
    * Complain if a given table does not exist
    **/
    protected abstract function assertTableExists($name);


    /**
    * Just check it fundamentally works
    * @medium
    **/
    public function testBasic() {
        $outputter = $this->getOutputter();
        $parser_model = $this->completeModel();
        $config = new Mock_Config();

        ob_start();
        $outputter->check_layout(__DIR__ . '/../src/processor/database.layout');
        ob_end_clean();
        $outputter->output($parser_model, $config);

        $this->connect();
        $this->assertTableExists('projects');
        $this->assertTableExists('files');
        $this->assertTableExists('classes');
        $this->assertTableExists('interfaces');
        $this->assertTableExists('class_implements');
        $this->assertTableExists('functions');
        $this->assertTableExists('arguments');
        $this->assertTableExists('variables');
        $this->assertTableExists('constants');
        $this->assertTableExists('documents');
        $this->assertTableExists('enumerations');
        $this->assertTableExists('packages');
        $this->assertTableExists('versions');
        $this->assertTableExists('item_authors');
        $this->assertTableExists('item_tables');
        $this->assertTableExists('item_see');
        $this->assertTableExists('item_info_tags');
    }

}
