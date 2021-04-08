<?php

namespace Admin\Components;
use Phalcon\Exception as Exception;
use Phalcon\Mvc\Controller;


class ControllerBase extends Controller
{
    public $module;
    public $controller;
    public $base_url;

	public function initialize()
    {
        ini_set('display_errors',1);
        error_reporting(E_ALL^E_NOTICE);

        $this->url->setStaticBaseUri($this->request->getScheme().'://'.$this->request->getHttpHost());
        $this->url->setBaseUri('/');
        
        $this->module = $this->dispatcher->getModuleName();
        $this->controller = $this->dispatcher->getControllerName();
        $this->base_url = $this->dispatcher->getModuleName().'/'.$this->dispatcher->getControllerName();
        $this->view->setVar('module',$this->module);
        $this->view->setVar('controller',$this->controller);
        $this->view->setVar('base_url',$this->base_url);
        $this->view->setVar('conf',$this->conf);
    }

    protected function sendJSON($data){

        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setJsonContent($data,JSON_UNESCAPED_UNICODE);
        $this->response->send();
        exit;
    }

    public function afterExecuteRoute($dispatcher)
    {
        $this->view->config = $this->config;
    }

    protected function jump($url=null){
    	$jump_url = $this->request->getHTTPReferer();
    	if($_POST['referer']){
    		$jump_url = $_POST['referer'];
    	}
    	elseif($url){
    		$jump_url = $url;
    	}
    	
    	$this->response->redirect($jump_url)->sendHeaders();
    	
    }

}
