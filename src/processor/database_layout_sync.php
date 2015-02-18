<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of Pelzini.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * This tool syncs the database layout specified in <i>database.layout</i> to the actual database
 *
 * @since 0.1
 * @author Josh
 * @package Processor
 **/

require_once 'functions.php';
require_once 'constants.php';
require_once 'load_config.php';



foreach ($dpgOutputters as $outputter) {
    switch ($outputter) {
    case OUTPUTTER_MYSQL:
        echo "<h1>MySQL</h1>";
        $outputter = new MysqlOutputter(
            $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_username'],
            $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_password'],
            $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_server'],
            $dpgOutputterSettings[OUTPUTTER_MYSQL]['database_name']
        );

        $result = $outputter->check_layout('database.layout');
        break;


    case OUTPUTTER_PGSQL:
        echo "<h1>PostgreSQL</h1>";
        $outputter = new PostgresqlOutputter(
            $dpgOutputterSettings[OUTPUTTER_PGSQL]['database_username'],
            $dpgOutputterSettings[OUTPUTTER_PGSQL]['database_password'],
            $dpgOutputterSettings[OUTPUTTER_PGSQL]['database_server'],
            $dpgOutputterSettings[OUTPUTTER_PGSQL]['database_name']
        );

        $result = $outputter->check_layout('database.layout');
        break;


    case OUTPUTTER_SQLITE:
        echo "<h1>SQLite</h1>";
        $outputter = new SqliteOutputter(
            $dpgOutputterSettings[OUTPUTTER_SQLITE]['filename']
        );

        $result = $outputter->check_layout('database.layout');
        break;


    }
}
?>
