<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'DatabaseTestCase.php';


class SQLiteOutputterTest extends DatabaseTestCase {
    const TEMP = '/tmp/pelzini-unit-test-result';
    private $db = null;

    public function setUp() {
        @unlink(self::TEMP);
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
        if ($this->db) sqlite_close($this->db);
        @unlink(self::TEMP);
    }

    /**
    * Connect to the db
    **/
    private function connect()
    {
        $this->db = @sqlite_open(self::TEMP);
    }


    /**
    * @return DatabaseOutputter
    **/
    protected function getOutputter()
    {
        if (!function_exists('sqlite_open')) {
            $this->markTestSkipped('SQLite not available');
        }
        return new SqliteOutputter(self::TEMP);
    }

    /**
    * Complain if a given table does not exist
    *
    * @param string $table The table to look for
    **/
    protected function assertTableExists($table)
    {
        $this->db || $this->connect();
        sqlite_query('SELECT 1 FROM ' . sqlite_escape_string($table), $this->db);
    }

    /**
    * Complain if a given record in a given table does not exist
    *
    * @param int $expected_count The number of records to expect to find
    * @param string $table The table to look for the record in
    * @param array $where Where clauses, as key => val pairs
    **/
    protected function assertNumRecords($expected_count, $table, array $where = null)
    {
        $this->db || $this->connect();

        if (@count($where) == 0) {
            $where = array(1 => 1);
        }

        $q = 'SELECT 1 FROM ' . sqlite_escape_string($table) . ' WHERE ';
        $j = 0;
        foreach ($where as $key => $val) {
            if ($j++) $q .= ', ';
            $q .= $key . " = '" . sqlite_escape_string($val) . "'";
        }

        $res = sqlite_query($q, $this->db);
        if (!$res) throw new Exception('Query error');

        $this->assertGreaterThanOrEqual($expected_count, sqlite_num_rows($res));
    }

    /**
    * Get a record, as key-value pairs
    *
    * @param string $table The table to look for the record in
    * @param array $where Where clauses, as key => val pairs
    **/
    protected function getRecord($table, array $where = null)
    {
        $this->db || $this->connect();

        if (@count($where) == 0) {
            $where = array(1 => 1);
        }

        $q = 'SELECT * FROM ' . sqlite_escape_string($table) . ' WHERE ';
        $j = 0;
        foreach ($where as $key => $val) {
            if ($j++) $q .= ', ';
            $q .= $key . " = '" . sqlite_escape_string($val) . "'";
        }
        $q .= ' LIMIT 1';

        $res = sqlite_query($q, $this->db);
        if (!$res) throw new Exception('Query error');

        $this->assertEquals(1, sqlite_num_rows($res));

        return sqlite_fetch_array($res, SQLITE_ASSOC);
    }

}

