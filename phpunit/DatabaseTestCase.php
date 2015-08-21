<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'PHPUnit_ParserTestCase.php';
require_once 'Mock_Config.php';


abstract class DatabaseTestCase extends PHPUnit_ParserTestCase {
    private $outputter;
    private $config;


    /**
    * Create outputter and config
    **/
    public function setUp()
    {
        parent::setUp();

        $this->outputter = $this->getOutputter();
        $this->config = new Mock_Config();

        ob_start();
        $this->outputter->check_layout(__DIR__ . '/../src/processor/database.layout');
        ob_end_clean();
    }

    public function tearDown()
    {
        parent::tearDown();
    }


    /**
    * @return DatabaseOutputter
    **/
    protected abstract function getOutputter();

    /**
    * Complain if a given table does not exist
    *
    * @param string $table The table to look for
    **/
    protected abstract function assertTableExists($table);

    /**
    * Complain if a given record in a given table does not exist
    *
    * @param int $expected_count The number of records to expect to find
    * @param string $table The table to look for the record in
    * @param array $where Where clauses, as key => val pairs
    **/
    protected abstract function assertNumRecords($expected_count, $table, array $where = null);

    /**
    * Get a record, as key-value pairs
    *
    * @param string $table The table to look for the record in
    * @param array $where Where clauses, as key => val pairs
    **/
    protected abstract function getRecord($table, array $where = null);


    /**
    * Test the layout sync creates tables
    * @medium
    **/
    public function testLayoutSync()
    {
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
        $this->assertTableExists('versions');
        $this->assertTableExists('item_authors');
        $this->assertTableExists('item_tables');
        $this->assertTableExists('item_see');
        $this->assertTableExists('item_info_tags');
    }

    /**
    * Test a layout resync works
    * @medium
    **/
    public function testLayoutReSync()
    {
        ob_start();
        $this->outputter->check_layout(__DIR__ . '/../src/processor/database.layout');
        ob_end_clean();

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
        $this->assertTableExists('versions');
        $this->assertTableExists('item_authors');
        $this->assertTableExists('item_tables');
        $this->assertTableExists('item_see');
        $this->assertTableExists('item_info_tags');
    }

    /**
    * Parse a file with a single function
    * @depends testLayoutSync
    * @medium
    **/
    public function testEmpty()
    {
        $parser_model = array();
        $this->outputter->output($parser_model, $this->config);

        $this->assertNumRecords(1, 'projects');
        $row = $this->getRecord('projects');
        $this->assertEquals(1, $row['id']);

        $this->assertNumRecords(0, 'files');
        $this->assertNumRecords(0, 'functions');
        $this->assertNumRecords(0, 'classes');
        $this->assertNumRecords(0, 'interfaces');
        $this->assertNumRecords(0, 'arguments');
    }

    /**
    * Parse a file with a single function
    * @depends testEmpty
    * @medium
    **/
    public function testEmptyFile()
    {
        $parser_model = array();
        $parser_model[] = $this->parse('<?php');
        $this->outputter->output($parser_model, $this->config);

        $this->assertNumRecords(1, 'files');
        $this->assertNumRecords(0, 'functions');
        $this->assertNumRecords(0, 'functions');
        $this->assertNumRecords(0, 'classes');
        $this->assertNumRecords(0, 'interfaces');
        $this->assertNumRecords(0, 'arguments');

        $this->assertNumRecords(1, 'files');
        $row = $this->getRecord('files');
        $this->assertEquals(1, $row['id']);
        $this->assertEquals(1, $row['projectid']);
    }

    /**
    * Parse a file with a single function
    * @depends testEmpty
    * @medium
    **/
    public function testFunction()
    {
        $parser_model = array();
        $parser_model[] = $this->parse('
            <?php
            function aaa() {}
        ');
        $this->outputter->output($parser_model, $this->config);

        $this->assertNumRecords(1, 'functions');
        $row = $this->getRecord('functions');
        $this->assertEquals(1, $row['id']);
        $this->assertEquals(1, $row['projectid']);
        $this->assertEquals(1, $row['fileid']);
        $this->assertEquals(2, $row['linenum']);
        $this->assertEquals(0, $row['classid']);
        $this->assertEquals(0, $row['interfaceid']);
        $this->assertEquals('aaa', $row['name']);
        $this->assertEquals('', $row['description']);
        $this->assertEquals('', $row['visibility']);
        $this->assertEquals(null, $row['returntype']);
        $this->assertEquals('', $row['returndescription']);
        $this->assertEquals(0, $row['static']);
        $this->assertEquals(0, $row['abstract']);
        $this->assertEquals(0, $row['final']);

        $this->assertNumRecords(0, 'arguments');
    }

    /**
    * Parse a file with a function with some docs
    * @depends testEmpty
    * @medium
    **/
    public function testFunctionDocs()
    {
        $parser_model = array();
        $parser_model[] = $this->parse('
            <?php
            /**
            * Does something
            *
            * @return string Something else
            **/
            function aaa() {}
        ');
        $this->outputter->output($parser_model, $this->config);

        $this->assertNumRecords(1, 'functions');
        $row = $this->getRecord('functions');
        $this->assertEquals(1, $row['id']);
        $this->assertEquals(1, $row['projectid']);
        $this->assertEquals(1, $row['fileid']);
        $this->assertEquals(7, $row['linenum']);
        $this->assertEquals(0, $row['classid']);
        $this->assertEquals(0, $row['interfaceid']);
        $this->assertEquals('aaa', $row['name']);
        $this->assertEquals('Does something', trim(strip_tags($row['description'])));
        $this->assertEquals('', $row['visibility']);
        $this->assertEquals('string', $row['returntype']);
        $this->assertEquals('Something else', trim(strip_tags($row['returndescription'])));
        $this->assertEquals(0, $row['static']);
        $this->assertEquals(0, $row['abstract']);
        $this->assertEquals(0, $row['final']);

        $this->assertNumRecords(0, 'arguments');
    }

    /**
    * Parse a file function which has arguments
    * @depends testEmpty
    * @medium
    **/
    public function testFunctionArgs()
    {
        $parser_model = array();
        $parser_model[] = $this->parse('
            <?php
            /**
            * @param string $bbb Yay
            * @param int $ccc Test desc
            **/
            function aaa($bbb, $ccc = null) {}
        ');
        $this->outputter->output($parser_model, $this->config);

        $this->assertNumRecords(1, 'functions');
        $row = $this->getRecord('functions');
        $this->assertEquals(1, $row['id']);
        $this->assertEquals(1, $row['projectid']);
        $this->assertEquals(1, $row['fileid']);
        $this->assertEquals(6, $row['linenum']);
        $this->assertEquals(0, $row['classid']);
        $this->assertEquals(0, $row['interfaceid']);
        $this->assertEquals('aaa', $row['name']);
        $this->assertEquals('', trim(strip_tags($row['description'])));
        $this->assertEquals('', $row['visibility']);
        $this->assertEquals('', $row['returntype']);
        $this->assertEquals('', trim(strip_tags($row['returndescription'])));
        $this->assertEquals(0, $row['static']);
        $this->assertEquals(0, $row['abstract']);
        $this->assertEquals(0, $row['final']);

        $this->assertNumRecords(2, 'arguments');

        $row = $this->getRecord('arguments', array('name' => '$bbb'));
        $this->assertEquals(1, $row['projectid']);
        $this->assertEquals(1, $row['functionid']);
        $this->assertEquals('$bbb', $row['name']);
        $this->assertEquals('string', $row['type']);
        $this->assertEquals('', $row['defaultvalue']);
        $this->assertEquals('Yay', trim(strip_tags($row['description'])));

        $row = $this->getRecord('arguments', array('name' => '$ccc'));
        $this->assertEquals(1, $row['projectid']);
        $this->assertEquals(1, $row['functionid']);
        $this->assertEquals('$ccc', $row['name']);
        $this->assertEquals('int', $row['type']);
        $this->assertEquals('NULL', $row['defaultvalue']);
        $this->assertEquals('Test desc', trim(strip_tags($row['description'])));
    }

}
