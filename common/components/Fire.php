<?php
namespace Common\Components;


class Fire {

	public $firephp;


	public function __construct(){
		$this->firephp = \FirePHP::getInstance(true);
	}

	public function log($msg){
		$this->firephp->fb($msg,\FirePHP::LOG);
	}

	public function info($msg){
		$this->firephp->fb($msg,\FirePHP::INFO);
	}

	public function warn($msg){
		$this->firephp->fb($msg,\FirePHP::WARN);
	}

	public function error($msg){
		$this->firephp->fb($msg,\FirePHP::ERROR);
	}

	public function trace($msg){
		$this->firephp->fb($msg,\FirePHP::TRACE);
	}

	public function table($data){
		$this->firephp->fb($data,\FirePHP::TABLE);
	}

	public function dump($data,$msg){
		$this->firephp->fb($data,$msg,\FirePHP::DUMP);
	}
}
