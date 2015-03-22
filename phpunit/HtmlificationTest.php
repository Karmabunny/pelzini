<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/


/**
* Tests that check that the {@link htmlify_text()} function, which turns docu-text into HTML makes sence
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

