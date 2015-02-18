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
 * Contains the {@link Transformer} class
 *
 * @package Transformers
 * @author Josh
 * @since 0.2
 **/

/**
 * The top-level class of all transformers.
 * Transformers alter the parser model before it is outputted, for creating reports, etc.
 **/
abstract class Transformer
{

    /**
     * Transforms the data model before outputting
     *
     * @param array $parser_model The data model to transform
     * @return array The new data model, or null if there was an error
     **/
    abstract public function transform($parser_model);

}


?>
