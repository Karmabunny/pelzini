<?php
/*
 * This is the example Pelzini viewer configuration file
 * For more information about configuration, see
 * http://docu.sourceforge.net
 */

/* The project code to show documentation for - if not set, a drop-down is offered to the user */
//$dvgProjectCode = 'pelzini-src';

/* The database engine to use in the viewer. Supported values are 'mysql', 'postgresql' and 'sqlite' */
$dvgDatabaseEngine = 'mysql';

/* This should contain the database settings
   The following are used for typical database engines (MySQL and PostgreSQL) */
$dvgDatabaseSettings['server'] = 'localhost';
$dvgDatabaseSettings['username'] = 'pelzini';
$dvgDatabaseSettings['password'] = 'password';
$dvgDatabaseSettings['name'] = 'pelzini';

/* This setting is used by SQLite */
$dvgDatabaseSettings['filename'] = '../output/pelzini.sqlite';

/* The language to display the viewer in
   Available languages are in the i18n directory */
$dvgLanguage = 'english';
