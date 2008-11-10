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


/**
* The current version of docu.
**/
define ('DOCU_VERSION', '0.2-pre');

// The output engines
define ('OUTPUTTER_MYSQL',      1);
define ('OUTPUTTER_DEBUG',      2);
define ('OUTPUTTER_PGSQL',      3);

// The link types
// These are used when linking from tables such as Authors
// which can potentinally link to multiple tables
// NOTE: These link types must match the ones defined in viewer/constants.php
define ('LINK_TYPE_FILE',       1);
define ('LINK_TYPE_CLASS',      2);
define ('LINK_TYPE_INTERFACE',  3);
define ('LINK_TYPE_CONSTANT',   4);
define ('LINK_TYPE_FUNCTION',   5);
define ('LINK_TYPE_VARIABLE',   6);
?>
