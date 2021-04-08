<?php
namespace Admin\Controllers;
use \Common\Components\Assets;
use Admin\Components\ControllerBase;
use Common\Models\Admin;

/**
 * @aclDesc 首页模块
 * @aclCustom false
 * @acl *
 */
class IndexController extends ControllerBase {

	/**
     * @aclDesc 首页     
     */
	public function superAction(){

		if(!$this->conf['enable_multi_shop']){
			// return $this->response->redirect($module."/index/index");
			echo 'No page found';
			exit;
		}
		if ($this->auth->getUser()) {
			return $this->response->redirect($module.'/dashboard');
			exit;
		}

		if ($this->request->isPost()) {
			try{
				$this->auth->check($this->request->getPost());
				return $this->response->redirect($module."/dashboard/index");	
			} catch (\Exception $e){
				$this->flashSession->error($e->getMessage());
			}
					
		}
	
	}

	/**
     * @aclDesc 首页     
     */
	public function indexAction(){
		if ($this->auth->getUser()) {
			$this->response->redirect($module.'/dashboard');
			$this->view->disable();
		}

		if ($this->request->isPost()) {
			try{
				$this->auth->check($this->request->getPost());
				return $this->response->redirect($module."/dashboard/index");	
			} catch (\Exception $e){
				$this->flashSession->error($e->getMessage());
			}
					
		}

		if(strlen($this->conf['super_domain_keyword']) && strpos($this->request->getHttpHost(),$this->conf['super_domain_keyword'])!==false){
			$this->response->redirect($this->url->get('/super'));
		}
		
	}

	/**
	 * @aclDesc 登录
	 * @return [type] [description]
	 */
	public function loginAction(){

	}

	/**
	 * @aclCustom false
	 * @return [type] [description]
	 */
	public function initAction(){
		$admin = Admin::findFirst(1);
        // $admin->password = $this->security->hash('111111');
        $admin->password = Admin::hash('111111');
		if(!$admin->save()){
			$messages = $Log->getMessages();
            throw new \Exception($messages[0]);
		}
	}

	/**
	 * @aclDesc 登出
	 * @return [type] [description]
	 */
	public function logoutAction() {
		$shop_id = $this->auth->getShopId();
		if ($this->auth->getUser()) {
			$this->auth->remove();			
		}
		if($shop_id){
			return $this->response->redirect($module.'/index/index');
		}
		else{
			return $this->response->redirect('/super');
		}
	}


}