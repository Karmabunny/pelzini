<?php
/*
Copyright 2015 Josh Heidenreich

This file is part of Pelzini, released under GPL3; see LICENSE file for more information.
For full authorship information, refer to the Git log at https://github.com/Karmabunny/pelzini
*/


/**
* Hardcodes some config settings
**/
class Mock_Config extends Config {

    public function __construct() {
        $this->project_name = 'Unit Test';
        $this->project_code = 'Test';
    }

}
