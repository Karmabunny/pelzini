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
 * Contains the {@link QualityCheckTransformer} class
 *
 * @package Transformers
 * @author Josh
 * @since 0.2
 **/

/**
 * This is a transformer that does quality checks on the codebase
 *
 * It checks that the documentation has the required tags. Currently the 'required tags' are only the summary
 *
 * The documentation is created in a report called the {@link Quality check report}.
 **/
class FakeNamespaceDirsTransformer extends Transformer {

    /**
    * Set up the quality check transformer
    *
    * @param array $required_tags Docblock tags which will be reported if missing
    **/
    public function __construct()
    {
    }


    /**
     * Transforms the data model before outputting.
     *
     * This transformer generates a report of objects that do not have good enough documentation
     *
     * @param array $parser_model The data model to transform
     * @return array The new data model, or null if there was an error
     **/
    public function transform(&$parser_model)
    {
        foreach ($parser_model as $item) {
            if ($item instanceof ParserFile) {
                $parts = explode('/', dirname($item->name));
                $parts = array_filter($parts);
                $item->namespace = $parts;
            }
        }

        return $parser_model;
    }

}

