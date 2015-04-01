<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/

require_once 'DatabaseTestCase.php';


class SQLiteOutputterTest extends DatabaseTestCase {
    const TEMP = '/tmp/pelzini-unit-test-result';
    private $db;

    public function setUp() {
        parent::setUp();
        @unlink(self::TEMP);
    }

    public function tearDown() {
        parent::tearDown();
        if ($this->db) sqlite_close($this->db);
        @unlink(self::TEMP);
    }


    /**
    * @return DatabaseOutputter
    **/
    protected function getOutputter() {
        if (!function_exists('sqlite_open')) {
            $this->markTestSkipped('SQLite not available');
        }
        return new SqliteOutputter(self::TEMP);
    }

    /**
    * Connect to the db
    **/
    protected function connect()
    {
        $this->db = @sqlite_open(self::TEMP);
    }

    /**
    * Complain if a given table does not exist
    **/
    protected function assertTableExists($name)
    {
        sqlite_query('SELECT 1 FROM ' . sqlite_escape_string($name), $this->db);
    }
}

