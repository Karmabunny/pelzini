<?php
/*
Copyright 2015 Josh Heidenreich

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
 * Output OpenSearch XML
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.9
 **/

$base = 'http://' . $_SERVER['HTTP_HOST'] . $base_path;
$search = 'search?advanced=1&q={searchTerms}&classes=y&functions=y&constants=y&source=y&path=';

header('Content-type: text/xml; charset="UTF-8"');
echo '<?xml version="1.0"?>';
echo '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">';
echo '<ShortName>', htmlspecialchars($project['name']), '</ShortName>';
echo '<Description>Documentation for ', htmlspecialchars($project['name']), ' in Pelzini</Description>';
echo '<Image height="32" width="32" type="image/x-icon">', htmlspecialchars($base . '../assets/favicon.ico'), '</Image>';
echo '<Url type="text/html" method="get" template="', htmlspecialchars($base . $search) . '"/>';
echo '<moz:SearchForm>', htmlspecialchars($base), '</moz:SearchForm>';
echo '</OpenSearchDescription>';
