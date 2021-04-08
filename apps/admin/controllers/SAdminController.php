<?php
namespace Admin\Controllers;

use Phalcon\Mvc\View;
use Common\Models\SAdmin;
use Common\Models\SAclRole;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Paginator\Adapter\Model as Paginator;

/**
 * @aclDesc 管理员账号
 * @acl shopadmin,superadmin
 * @aclCustom super,single_shop,multi_shop
 */
class SAdminController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		
		$this->controller_name = '管理员';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 * @return [type] [description]
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$conditions = [];
		$params = [];

		$shop_id = $this->auth->getShopId();
		if($shop_id){
			
			$this->controller_name = '商铺'.$this->controller_name;
		}
		else{
			$this->controller_name = '系统'.$this->controller_name;
		}

		$conditions[] = 'shop_id=:shop_id:';
		$params['shop_id'] = $shop_id;

		$conditions[] = 'id>1 and flag=1';
		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		$orderSql = '';
		// $list = SAdmin::find([$conditionSql,'bind'=>$params]);
		$list = $this->modelsManager->executeQuery("select * from Common\Models\SAdmin WHERE " . $conditionSql . $orderSql . $limitSql, $params);

		if (empty($list)) {
			$this->flash->notice("没有找到符合条件的数据");
		}

		$paginator = new Paginator(array(
			"data" => $list,
			"limit" => 10,
			"page" => $page,
		));

		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
				'controller_name'=>$this->controller_name,
				'action_name'=>'列表',
				'page' => $paginator->getPaginate(),
			]);
		
	}


	/**
	 * @aclDesc 新增
	 * @return [type] [description]
	 */
	public function createAction(){
		$this->modify();
	}

	/**
	 * @aclDesc 修改
	 * @return [type] [description]
	 */
	public function updateAction(){
		$this->modify();
	}

	protected function form(){

		$id = $this->request->getQuery('id','int');

		$M = SAdmin::findFirst($id);
		if(!$M){
			$M = new SAdmin;
		}

		
		$this->view->setVars([
			'M'=>$M,
		]);		
		
		if($this->request->isAjax()){	

			$this->view->disable();
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),'form');
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);
		}
	}

	protected function modify(){
		$this->view->disable();
		if($this->request->isPost()){
			
			$id = $this->request->getPost('id','int');
			$acl_role_id = $this->request->getPost('acl_role_id');
			$username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $name = $this->request->getPost('name');
			// $school_id = $this->request->getPost('school_id');

			if($id){
				$Model = SAdmin::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的用户'
					];
				}
			}
			else{
                $Model = new SAdmin;
                
                if(empty($username) or empty($password)){
                    $data = [
                            'status'=>'0',
                            'code'=>'',
                            'msg'=>'必须填写帐号和密码'
                        ];
                    $this->sendJSON($data);
                    exit;
                }
			}

			

			try{
				
				$Role = SAclRole::findFirst($acl_role_id);
				if(!$Role){
					throw new \Exception("选择了错误的用户角色", 1);
					
				}
				$data = [
					'username'=>$username,
					'acl_role_id'=>$acl_role_id,
                    'shop_id'=> $Role->shop_id,
                    'name'=>$name
				];
				if(!empty($password)){
                    // $data['password'] = $this->di->getSecurity()->hash($password);
                    $data['password'] = SAdmin::hash($password);
				}

				$Model->assign($data);
				if($Model->save()){
					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->id,$Model->username);
					$data = [
						'status'=>'1',
						'code'=>'',
					];
				}
				else{
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e){
				throw new \Exception($e->getMessage(), 1);
			}

			$this->sendJSON($data);

		}
		else{
			$this->form();
		}
	}

	/**
	 * @aclDesc 删除
	 * @return [type] [description]
	 */
	public function deleteAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$Model = SAdmin::findFirst($id);
			
			$Model->flag = 2;
			$Model->username = $Model->username.'[停用]';
			if($Model->save()){
				SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->id,$Model->username);
				$data = [
					'status'=>'1',
					'code'=>'',
				];
			}
			else{
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}
	}

	/**
	 * @aclDesc 修改密码
	 * @acl *
	 * @aclCustom false
	 * @return [type] [description]
	 */
	public function changePasswordAction(){

		$M = $this->auth->getUser();
		if(!$M){
			throw new \Exception("无效的用户", 1);
			
		}
		$this->view->setVars([
			'M'=>$M
		]);
	
		if($this->request->isAjax()){	

			$this->view->disable();
				
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),$this->dispatcher->getActionName());
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);
			exit;
		}
		elseif($this->request->isPost()){

			$password = $this->request->getPost('password');
			$newpassword = $this->request->getPost('newpassword');
			$repassword = $this->request->getPost('repassword');

			// if (!$this->security->checkHash($password, $M->password)) {
                if (!SAdmin::checkHash($password, $M->password)) {
				throw new \Exception('原密码不正确');
			}

			if($M->changePassword($newpassword,$repassword)){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->id,$M->username);
				$this->flashSession->success('密码修改成功');
			}			


		}
	}


	

	
	

}