<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of docu.

Docu is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Docu is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with docu.  If not, see <http://www.gnu.org/licenses/>.
*/


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
      
      
  }
}
?>
