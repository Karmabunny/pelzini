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
* Runs a set of tests that check that the {@link htmlify_text()} function, which turns docu-text into HTML makes sence
*
* @since 0.2
* @author Josh
* @package Test suite
**/

class HtmlificationTest extends PHPUnit_Framework_TestCase {

    public function files() {
        $out = array();
        $tests = glob(dirname(__FILE__) . '/htmlification/*.txt');
        foreach ($tests as $test_filename) {
            $result_filename = str_replace ('.txt', '.htm', $test_filename);
            $out[] = array($test_filename, $result_filename);
        }
        return $out;
    }

    /**
    * @dataProvider files
    **/
    public function testHtmlification($test_filename, $result_filename) {
        $test = file_get_contents($test_filename);
        $real_result = trim(htmlify_text($test));

        $expected_result = trim(file_get_contents($result_filename));

        $this->assertEquals($expected_result, $real_result);
    }

}

