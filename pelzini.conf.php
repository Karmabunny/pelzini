<?php
/*
 * This is the example Pelzini processor configuration file
 * For more information about configuration, see
 * http://docu.sourceforge.net
 */

/* Required - This should be the name of your project */
$dpgProjectName = 'Pelzini';

/* Required - A unique code per project, to allow multiple projects per database */
$dpgProjectCode = 'pelzini-src';

/* Required - This should be the terms that your documentation is made available under
   It will be shown in the footer of the viewer */
$dpgLicenseText = 'Documentation is made available under the
<a href="http://www.gnu.org/copyleft/fdl.html">GNU Free Documentation License 1.2</a>.';

/* Optional - List the transformers here. Transformers alter the parsed files before outputting */
$dpgTransformers[] = new QualityCheckTransformer();

/* Required - List the outputters here. Outputters save the parsed files to a database or an output file */
$dpgOutputters[] = new MysqlOutputter('pelzini', 'password', 'localhost', 'pelzini');

/* Multiple output targets can be specified */
//$dpgOutputters[] = new SqliteOutputter('../../pelzini.sqlite');

/* Required - This is the base directory that the parsing of your application should take place */
$dpgBaseDirectory = 'src';

/* Optional - These are directories that should be excluded from the processing. */
$dpgExcludeDirectories = array();

/* Optional - This is the directory which contains plain-text documents */
$dpgDocsDirectory = 'documentation';

