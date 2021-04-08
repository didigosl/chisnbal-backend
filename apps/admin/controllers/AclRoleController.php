<?php
namespace Admin\Controllers;

use Common\Models\SAclRole;
use Common\Models\SAclResource;
use Common\Models\SAclAccess;
use Common\Models\SAclAction;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Db;

/**
 * @aclDesc 用户角色
 * @acl shopadmin
 * @aclCustom super,single_shop,multi_shop
 */
class AclRoleController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		
		$this->controller_name = '管理员角色';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 * @return [type] [description]
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$parameters = $this->persistent->parameters;
		if (!is_array($parameters)) {
			$parameters = array();
		}
		$parameters['conditions'] = 'shop_id=:shop_id: AND custom_flag=1';
		$parameters['bind'] = [
			'shop_id'=>$this->auth->getShopId()
		];
		$parameters["order"] = "id asc";

		$list = SAclRole::find($parameters);
		if (empty($list)) {
			$this->flash->notice("没有找到符合条件的数据");
		}

		$paginator = new Paginator(array(
			"data" => $list,
			"limit" => 10,
			"page" => $page,
		));

		$this->view->page = $paginator->getPaginate();
	}

	/**
	 * @aclCustom false
	 * @return [type] [description]
	 */
	public function getAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id');
			$Model = SAclRole::findFirst($id);
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$Model->toArray()
			];
			$this->sendJSON($data);
		}
		
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

		$M = SAclRole::findFirst($id);
		if(!$M){
			$M = new SAclRole;
		}
		
		$this->view->setVars([
			'M'=>$M
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
			$formData = $this->request->getPost();
			
			$id = $this->request->getPost('id','int');
			// $name = $this->request->getPost('name','string');
			$intro = $this->request->getPost('intro','string');
			$school_flag = $this->request->getPost('school_flag','int');

			if($id){
				$Model = SAclRole::findFirst($id);
				if(!$Model){
					throw new \Exception("您操作的对象不存在", 1);
					
				}
			}	
			else{
				$Model = new SAclRole;
			}

			$Model->assign([
				// 'name'=>$name,
				'intro'=>$intro,
				'shop_id'=>$this->auth->getShopId()
				]);
			try{
				if($Model->save()){
					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->id,$Model->intro);
					$data = [
						'status'=>'1',
						'code'=>'',
					];
					$this->sendJSON($data);
				}
				else{
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e){
				throw new \Exception($e->getMessage(), 1);
			}
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
			$Model = SAclRole::findFirst($id);

			$this->db->begin();
			$check_user = $this->db->fetchColumn('SELECT count(1) FROM s_admin WHERE acl_role_id=:role_id',['role_id'=>$id]);
			if($check_user){
				$data = [
					'status'=>'0',
					'msg'=>'此角色存在帐号，不可删除！',
				];
				$this->sendJSON($data);
			}
			
			if($Model->delete()){
				SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->id,$Model->intro);
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'msg'=>'删除失败',
				];
			}
			
			$this->sendJSON($data);

		}
	}

	/**
	 * @aclDesc 权限设置
	 * @return [type] [description]
	 */
	public function settingAction(){
		if($this->request->isPost()){
			$role_id = $this->request->getPost('role_id','int');
			$setting_actions = $this->request->getPost('actions');
			$setting_resources = $this->request->getPost('resources');

			$setting_resources = is_array($setting_resources) ? $setting_resources : [];
			$setting_actions = is_array($setting_actions) ? $setting_actions : [];

			$this->db->begin();
			try{
				$Role = SAclRole::findFirst($role_id);
				if(!$Role){
					throw new \Exception($this->controller_name."不存在", 1);
					
				}

				$this->db->delete('s_acl_access',"role_id=:role_id",['role_id'=>$Role->id]);

				$tmp = $this->db->fetchAll("select id,name from s_acl_resource");
				$resources = [];
				foreach($tmp as $v){
					$resources[$v['id']] = $v['name'];
				}
				$tmp = $this->db->fetchAll("select id,name from s_acl_action");
				$actions = [];
				foreach($tmp as $v){
					$actions[$v['id']] = $v['name'];
				}

				foreach ($setting_resources as $resource_id) {
					$data = [
						'role_id'=>$role_id,
						'role_name'=>$Role->name,
						'resource_id'=>$resource_id,
						'resource_name'=>$resources[$resource_id],
						'action_id'=>0,
						'action_name'=>'*'
					];

					$AclAccess = new SAclAccess;
					$AclAccess->assign($data);
					$AclAccess->save();
				}
				
				foreach ($setting_actions as $v) {
					$access = explode('-', $v);
				
					if(!in_array($access[0],$setting_resources)){
						$data = [
							'role_id'=>$role_id,
							'role_name'=>$Role->name,
							'resource_id'=>$access[0],
							'resource_name'=>$resources[$access[0]],
							'action_id'=>$access[1],
							'action_name'=>$actions[$access[1]]
						];

						$AclAccess = new SAclAccess;
						$AclAccess->assign($data);
						$AclAccess->save();

					}
					
				}
				$this->db->commit();
				$this->flashSession->success("权限设置完成！");
				$this->jump($this->request->getHTTPReferer());
			} catch (\Exception $e){
				$this->db->rollback();
				throw new \Exception($e->getMessage(), 1);
				
			}
			
		}
		else{
			$role_id = $this->request->getQuery('id','int');		
			$Role = SAclRole::findFirst($role_id);
			
			$tmpAccesses = $this->db->fetchAll("select concat(resource_id,'-',action_id) as c from s_acl_access where role_id=:id order by resource_id asc,id asc",\Phalcon\Db::FETCH_ASSOC,['id'=>$role_id]);
			
			$accesses = [];	
			foreach ($tmpAccesses as $v) {
				$accesses[] = $v['c'];
			}
			unset($tmpAccesses);

			if($this->auth->getShopId()){
				if($this->conf['enable_multi_shop']){
					$conditions = 'multi_shop_custom_flag=1';
				}
				else{
					$conditions = 'single_shop_custom_flag=1';
				}
			}
			else{
				$conditions = 'super_custom_flag=1';
			}

			$actions = SAclAction::find($conditions);
			$resources = [];
			foreach ($actions as $v) {
				if(!isset($resources[$v->resource_id])){
					$resources[$v->resource_id] = [
						'resource_name' => $v->resource_name,
						'desc'=>$v->resource->desc,
						'actions' => []
					];
					
				}	
				$resources[$v->resource_id]['actions'][] = [
					'id' => $v->id,
					'name' => $v->name,
					'desc'=>$v->desc,
				];			
			}

			$this->view->resources = $resources;
			$this->view->Role = $Role;
			$this->view->accesses = $accesses;
		}
		
	}

}