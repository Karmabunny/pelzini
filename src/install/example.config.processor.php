<?php
/*
 * This is the example docu processor configuration file
 * For more information about configuration, see
 * http://docu.sourceforge.net
 */

ini_set('memory_limit', '-1');


/* This should be the name of your project */
$dpgProjectName = '{{project_name}}';

/* The project ID. Nessasary for multiple docs per database */
$dpqProjectID = 1;

/* This should be the terms that your documentation is made available under
   It will be shown in the footer of the viewer */
$dpgLicenseText = 'Documentation is made available under the 
  <a href="http://www.gnu.org/copyleft/fdl.html">GNU Free Documentation License 1.2</a>.';

/* List the outputters here.
   Currently you can only have one instance of each outputter.
   Use the outputter constants defined in the constants.php file. */
$dpgOutputters[] = OUTPUTTER_MYSQL;
//$dpgOutputters[] = OUTPUTTER_DEBUG;

/* This should contain the outputter settings
   The settings are an array, with one array for each outputter */
$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_server'] = '{{database_server}}';
$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_username'] = '{{database_user}}';
$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_password'] = '{{database_password}}';
$dpgOutputterSettings[OUTPUTTER_MYSQL]['database_name'] = '{{database_name}}';

/* This is the base directory that the parsing of your application should take place */
$dpgBaseDirectory = '{{project_base_dir}}';

/* These are directories that should be excluded from the processing. */
$dpgExcludeDirectories = {{project_exclude}};

/* These are the Javadoc tags that should cascade from their parent */
$dpgCascaseDocblockTags[] = '@author';
$dpgCascaseDocblockTags[] = '@since';
?>
