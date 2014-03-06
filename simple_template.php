<?php

class Simple_Template {

    private $_values;
    private $_template;
    private $_compression_flag;
    private $_compression_textarea_escape_flag;
    const LINE_BREAK = '___ST_LB___';

    public function __construct($compression_flag=true, $compression_textarea_escape_flag=false) {

        $this->remove();
        $this->_compression_flag = $compression_flag;
        $this->_compression_textarea_escape_flag = $compression_textarea_escape_flag;
    
    }

    public function read($file_path) {

    	$this->_template = file_get_contents($file_path);
    	
    }

    public function set($target, $value) {

        $this->_values[$target] = $value;

    }

    public function setArray($values) {

        foreach($values as $target => $value) {

            $this->set($target, $value);

        }

    }

    public function remove() {

        $this->_values = array();

    }

    public function get($remain_flag=false) {

        $return = '';
        $targets = $replacements = array();

        foreach($this->_values as $target => $value) {

            $targets[] = '[{'. $target .'}]';
            $replacements[] = $value;

        }

        $return = str_replace($targets, $replacements, $this->_template);

        if(!$remain_flag) {

            $return = preg_replace('/\[\{[^\}]*\}\]/', '', $return);

        }

        if($this->_compression_flag) {

            $return = $this->compression($return);

        }

        return $return;

    }

    private function compression($contents) {

        if($this->_compression_textarea_escape_flag) {

            $contents = preg_replace_callback('|<textarea[^>]*>[^<]*</textarea>|', array($this, 'replaceLB'), $contents);

        }
        
        $contents = preg_replace("|[\n\r\t\v]+[ ]*|", '', $contents);
        $targets = array('<!--', '-->');
        $replacements = array("<!--\n", "\n-->");

        if($this->_compression_textarea_escape_flag) {

            $targets[] = self::LINE_BREAK;
            $replacements[] = "\n";

        }

        return str_replace($targets, $replacements, $contents);

    }

    private function replaceLB($matches) {

        return $contents = preg_replace("|\r?\n|", self::LINE_BREAK, $matches[0]);

    }

}

/*** Sample Source

// test.php
-----------------------------------------------------

require 'simple_template.php';

$st = new Simple_Template();    // or Simple_Template($compression_flag, $compression_textarea_escape_flag): $compression_flag, $compression_textarea_escape_flag => Omittable
$st->read('test.tpl');
$st->set('target_1', 'value1');
$st->setArray(array(

    'target_2' => 'value2',
    'target_3' => 'value3'

));
echo $st->get();				// or $st->get(true);	// In this case, target will be remained if target value doesn't exist.



// test.tpl
-----------------------------------------------------

<html>
<body>

	value1 : [{target_1}]<br>
	value2 : [{target_2}]<br>
	value3 : [{target_3}]<br>

</body>
</html>

***/
