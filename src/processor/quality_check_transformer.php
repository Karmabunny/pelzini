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
class QualityCheckTransformer extends Transformer {
    private $offending_items;
    private $required_tags;


    /**
    * Set up the quality check transformer
    *
    * @param array $required_tags Docblock tags which will be reported if missing
    **/
    public function __construct(array $required_tags = null)
    {
        $this->required_tags = $required_tags;
        if (empty($this->required_tags)) {
            $this->required_tags = array('@summary');
        }
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
        $this->offending_items = array();

        foreach ($parser_model as $item) {
            if ($item instanceof CodeParserItem) {
                $this->check_files($item);
            }
        }

        if (count($this->offending_items) == 0) {
            return null;
        }
        ksort($this->offending_items);

        $report = "The following items have insufficent documentation:";
        foreach ($this->offending_items as $type => $items) {
            natcasesort($items);

            $report .= "\n\n<b>{$type}:</b>\n - ";
            $report .= implode("\n - ", $items);
        }

        $document = new ParserDocument();
        $document->name = "Quality check report";
        $document->description = htmlify_text($report);
        $parser_model[] = $document;

        return $parser_model;
    }


    /**
     * Checks that a file has high-enough quality documentation
     **/
    private function check_files($item)
    {
        $tags = $item->getDocblockTags();
        $problems = array();

        // classes and interfaces
        foreach ($item->classes as $sub) {
            if ($sub instanceof ParserClass) {
                $this->check_class($sub);

            } else if ($sub instanceof ParserInterface) {
                $this->check_interface($sub);
            }
        }

        // functions
        foreach ($item->functions as $sub) {
            $this->check_function($sub);
        }

        // the files
        foreach ($this->required_tags as $tag_name) {
            if (!isset($tags[$tag_name]) or $tags[$tag_name] == '') {
                if ($tag_name == '@summary') {
                    $tag_name = 'summary';
                } else {
                    $tag_name .= ' tag';
                }

                $problems[] = "No {$tag_name}";
            }
        }

        if (count($problems)) {
            $this->offending_items['Files'][] = '{@link ' . $item->name . '}: <i>' . implode(', ', $problems) . '</i>';
        }
    }


    /**
     * Checks that a class has high-enough quality documentation
     **/
    private function check_class($item)
    {
        $tags = $item->getDocblockTags();
        $problems = array();

        foreach ($this->required_tags as $tag_name) {
            if (!isset($tags[$tag_name]) or $tags[$tag_name] == '') {
                if ($tag_name == '@summary') {
                    $tag_name = 'summary';
                } else {
                    $tag_name .= ' tag';
                }

                $problems[] = "No {$tag_name}";
            }
        }

        if (count($problems)) {
            $this->offending_items['Classes'][] = '{@link ' . $item->name . '}: <i>' . implode(', ', $problems) . '</i>';
        }

        foreach ($item->functions as $sub) {
            $this->check_function($sub, ' from class {@link ' . $item->name . '}');
        }
    }


    /**
     * Checks that an interface has high-enough quality documentation
     **/
    private function check_interface($item)
    {
        $tags = $item->getDocblockTags();
        $problems = array();

        foreach ($this->required_tags as $tag_name) {
            if (!isset($tags[$tag_name]) or $tags[$tag_name] == '') {
                if ($tag_name == '@summary') {
                    $tag_name = 'summary';
                } else {
                    $tag_name .= ' tag';
                }

                $problems[] = "No {$tag_name}";
            }
        }

        if (count($problems)) {
            $this->offending_items['Interfaces'][] = '{@link ' . $item->name . '}: <i>' . implode(', ', $problems) . '</i>';
        }

        foreach ($item->functions as $sub) {
            $this->check_function($sub, ' from interface {@link ' . $item->name . '}');
        }
    }


    /**
     * Checks that a function has high-enough quality documentation
     **/
    private function check_function($item, $from = null)
    {
        $tags = $item->getDocblockTags();
        $problems = array();

        foreach ($this->required_tags as $tag_name) {
            if (!isset($tags[$tag_name]) or $tags[$tag_name] == '') {
                if ($tag_name == '@summary') {
                    $tag_name = 'summary';
                } else {
                    $tag_name .= ' tag';
                }

                $problems[] = "No {$tag_name}";
            }
        }

        if (count($problems)) {
            $this->offending_items['Functions'][] = '{@link ' . $item->name . '}' . $from . ': <i>' . implode(', ', $problems) . '</i>';
        }

        foreach ($item->args as $arg) {
            if ($arg->description == '') {
                $this->offending_items['Function arguments'][] = '{@link ' . $item->name . '}' . $from . ': <i>No description for ' . $arg->name . '</i>';
            }
        }
    }


}


?>
