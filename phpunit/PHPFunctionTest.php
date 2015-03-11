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

class PHPFunctionTest extends PHPUnit_Framework_TestCase {
    private $parser;

    protected function setUp() {
        $this->parser = new PhpParser();
    }

    private function parse($code) {
        return $this->parser->parseFile('', 'data://text/plain;base64,' . base64_encode($code));
    }

    public function testBasicParse() {
        $file = $this->parse('<?php function aaa() {}');
        $this->assertCount(1, $file->functions);
        $this->assertCount(0, $file->classes);
        $this->assertCount(0, $file->constants);
        $this->assertCount(0, $file->enumerations);
    }
}

