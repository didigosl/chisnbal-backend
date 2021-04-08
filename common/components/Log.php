<?php
namespace Common\Components;

use Phalcon\Exception;
use Phalcon\Mvc\User\Component;
use Phalcon\Di;

class Log extends Component {

    public $fp = null;

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function init($file) {

        if(!$this->fp){
            $this->fp = fopen(SITE_PATH.'/logs/'.$file,'a+');
        }
		return $this;
	}

	public function write($msg){

        if(!$this->fp){
            throw new \Exception('init a log file first');
        }

        fputs($this->fp,date('Y-m-d H:i:s').$msg.PHP_EOL);
    }

    public function __destruct(){
        if($this->fp){
            fclose($this->fp);
        }
    }

}
