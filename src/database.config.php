<?php
/*
 * This is the example Pelzini database configuration file
 *
 * This configuration is used by the viewer, and if the processor config does
 * not specify any outputters then it will be loaded in that case as well
 *
 * For more information about configuration, see
 * https://github.com/Karmabunny/pelzini
 */

/* The database engine to use. Supported values are 'mysql', 'postgresql' and 'sqlite' */
$dvgDatabaseEngine = 'mysql';

/* This should contain the database settings
   The following are used for typical database engines (MySQL and PostgreSQL) */
$dvgDatabaseSettings['server'] = 'localhost';
$dvgDatabaseSettings['username'] = 'pelzini';
$dvgDatabaseSettings['password'] = 'password';
$dvgDatabaseSettings['name'] = 'pelzini';

/* This setting is used by SQLite */
$dvgDatabaseSettings['filename'] = '../output/pelzini.sqlite';

