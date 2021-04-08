<?php
namespace Admin\Components;

use Common\Models\Menu;

class ControllerAuth extends ControllerBase {

	public $basicUrl;
    public $breadcrumbs = [];

	public function initialize()
    {
    	parent::initialize();
     
        $controller = $this->dispatcher->getControllerName();

        $action = $this->dispatcher->getActionName();

    	$this->basicUrl = $controller;

    	if(!$this->auth->getUser()){
    		$this->response->redirect('/index/index')->sendHeaders();
            exit;
    	}

        if( !$this->acl->isAllowed($this->auth->getUser()->aclRole->name, $controller, $action)){
            $this->response->redirect($this->url->get('admin/dashboard/error',['msg'=>'抱歉，您没有操作权限哦']))->sendHeaders();
            //var_dump($this->auth->getUser()->aclRole->name,$controller,$action);exit;
            /*$this->dispatcher->forward(
                    array(
                        'module'=>'admin',
                        'controller' => 'dashboard',
                        'action'     => 'error',
                        'params'=>array(
                            'msg'=>"抱歉，您没有操作权限哦"
                        ),
                    )
                ); */  
        }

        //导航菜单展开参数
        $menu = $this->request->getQuery('menu');
        if(!empty($menu)){
            if($this->cookies->get('menu')!==$menu){
                $this->cookies->set('menu',$menu);
            }

            //用于标记导航菜单中，那个菜单的状态是active
            $menu_key = $controller.'-'.$action;
            $this->cookies->set('menu_key'.$this->auth->getRole(),$menu_key);
        }
        
        $shop_id = $this->auth->getShopId();
        $this->view->setVar('shop_id',$shop_id ? $shop_id : 0);
    	$this->view->setVar('basicUrl',$this->basicUrl);
        
        $this->view->setTemplateAfter('default');
        
    }

    
    public function cacheFormData(){
        $token = $this->request->get('form_token');
        $this->session->set('form_token',$token);
        $this->session->set('form_'.$token,$this->request->getPost());
    }
    
    public function getCachedFormData(){

        if($this->session->has('form_token')){
            $token = $this->session->get('form_token');
            $data = $this->session->get('form_'.$token);

            $this->session->remove('form_token');
            $this->session->remove('form_'.$token);
            return $data;
        }
    }

    public function clearCachedFormData(){

        $token = $this->request->get('form_token');
        $this->session->remove('form_token');
        $this->session->remove('form_'.$token);
 
    }
}