<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use Phalcon\Exception;
use Phalcon\Mvc\View\Simple as SimpleView;
// use Phalcon\Cache\Backend\File as BackFile;
// use Phalcon\Cache\Frontend\Output as FrontOutput;

class Widget extends Component {

	protected $viewFile;

	public function initilize($params=[]){
		return $params;
	}
	public function run($params){
		$params = $this->initilize($params);
		$view = new SimpleView;
		$view->partial(APP_PATH.'/common/components/Widgets/views/'.$this->viewFile,$params);
	}
}
