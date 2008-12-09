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
define ('OUTPUTTER_SQLITE',     4);

// The transformers - these alter and mutate the parser model before outptting
define ('TRANSFORMER_QUALITY_CHECK',    1);

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


// These are all of the valid tokens for all languages
// Some of these tokens represent specific strings, others represent language-specific
// concepts. These tokens are grouped by type, with each type begining on a 50-number boundry

// Punctuation
define ('TOKEN_OPEN_NORMAL_BRACKET',   1);    // A normal bracket, i.e. '('
define ('TOKEN_OPEN_CURLY_BRACKET',    2);    // A curly bracket, i.e. '{'
define ('TOKEN_OPEN_SQUARE_BRACKET',   3);    // A square bracket, i.e. '['

define ('TOKEN_CLOSE_NORMAL_BRACKET',  4);    // A normal bracket, i.e. ')'
define ('TOKEN_CLOSE_CURLY_BRACKET',   5);    // A curly bracket, i.e. '}'
define ('TOKEN_CLOSE_SQUARE_BRACKET',  6);    // A square bracket, i.e. ']'

define ('TOKEN_EQUALS',                7);    // An equals sign, i.e. '='
define ('TOKEN_PERIOD',                8);    // A period, i.e. '.'
define ('TOKEN_COMMA',                 9);    // A comma, i.e. ','

// Keywords
define ('TOKEN_FUNCTION',              50);   // A function definition, e.g. 'function'
define ('TOKEN_CLASS',                 51);   // A class definition, e.g. 'class'

// Comments
define ('TOKEN_DOCBLOCK',              100);  // A docblock comment, e.g. '/** whee */'
define ('TOKEN_COMMENT',               101);  // A docblock comment, e.g. '/* whee */' or '// whee'

// Identifiers, strings, numbers
define ('TOKEN_IDENTIFIER',            150);  // An identifier, e.g. 'whee'
define ('TOKEN_STRING',                151);  // A quoted string, e.g. '"hello"'

?>
