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
 * This file contains the {@link MetadataOutputter} class
 *
 * @package Outputters
 * @author Josh
 * @since 0.3
 **/

/**
 * Outputs the tree to an xml file
 *
 * @author Josh, 2009-08-03
 **/
class XmlOutputter extends MetadataOutputter {
    private $dom;
    private $root;


    /**
     * Returns the file extension of the outputted file (e.g. 'xml')
     **/
    public function get_ext()
    {
        return 'xml';
    }


    /**
     * Returns the mimetype of the outputted file (e.g. 'text/xml')
     **/
    public function get_mimetype()
    {
        return 'text/xml';
    }


    /**
     * Does the actual outputting of the file objects (and their sub-objects)
     *
     * @param array $parser_items The ParserItem(s) to save
     * @return boolean True on success, false on failure
     **/
    public function output($parser_items, Config $config)
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        $this->root = $this->dom->createElement('documentation');
        $this->dom->appendChild($this->root);

        foreach ($parser_items as $item) {
            if ($item instanceof ParserFile) {
                $this->process_file($item);

            } else if ($item instanceof ParserDocument) {
                $this->process_document($item);

            }
        }

        $this->dom->save($this->filename);

        return true;
    }


    /**
     * Processes a file
     **/
    private function process_file($item)
    {
        $node = $this->dom->createElement('file');
        $this->root->appendChild($node);

        $node->setAttribute('name', $item->name);
        $node->setAttribute('package', $item->package);

        $this->create_description_node($node, $item->description);

        // sub-items
        foreach ($item->functions as $child) {
            $this->process_function ($node, $child);
        }

        foreach ($item->classes as $child) {
            $this->process_class ($node, $child);
        }
    }


    /**
     * Processes a function
     **/
    private function process_function($parent_node, $item)
    {
        $node = $this->dom->createElement('function');
        $parent_node->appendChild($node);

        $node->setAttribute('name', $item->name);
        $node->setAttribute('visibility', $item->visibility);
        if ($item->abstract) $node->setAttribute('abstract', 'abstract');
        if ($item->static) $node->setAttribute('static', 'static');
        if ($item->final) $node->setAttribute('final', 'final');

        $this->create_description_node($node, $item->description);
    }


    /**
     * Processes a class
     **/
    private function process_class($parent_node, $item)
    {
        $node = $this->dom->createElement('class');
        $parent_node->appendChild($node);

        $node->setAttribute('name', $item->name);
        if ($item->abstract) $node->setAttribute('abstract', 'abstract');
        if ($item->final) $node->setAttribute('final', 'final');

        $this->create_description_node($node, $item->description);

        // sub-items
        foreach ($item->functions as $child) {
            $this->process_function ($node, $child);
        }
    }


    /**
     * Processes a document
     **/
    private function process_document($item)
    {

    }


    /**
     * Creates a description node, and appends it to the specified node
     **/
    private function create_description_node($node, $description)
    {
        $description = trim(strip_tags($description));

        $desc = $this->dom->createElement('description');
        $desc->appendChild ($this->dom->createTextNode ($description));

        $node->appendChild ($desc);
    }


}


?>
