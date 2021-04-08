<?php

class TESTTask extends \Phalcon\Cli\Task
{

    public function initialize(){

    }

    public function testAction(){
    		echo APP_PATH.PHP_EOL;
    }

}