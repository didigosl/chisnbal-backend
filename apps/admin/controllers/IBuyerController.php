<?php
namespace Admin\Controllers;

use Common\Models\IBuyer;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 采购员
 * @acl shopadmin,superadmin
 * @aclCustom super,single_shop
 */
class IBuyerController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '采购员';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 列表
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$name = trim($this->request->getQuery('name'));
		$phone = trim($this->request->getQuery('phone'));
		$status = $this->request->getQuery('status');
		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		if($name){
			$conditions[] = 'name like :name:';
			$params['name'] = '%'.$name.'%';
		}

		if($phone){
			$conditions[] = 'phone like :phone:';
			$params['phone'] = '%'.$phone.'%';
		}

		if( is_numeric($status) ){
			$conditions[] = 'status=:status:';
			$params['status'] = $status;
		}

		if($id){
			$conditions[] = 'buyer_id=:id:';
			$params['id'] = $id;
		}

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->from('Common\Models\IBuyer')
                ->where($conditionSql,$params)
                ->orderBy('buyer_id ASC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));


		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'controller_name'=>$this->controller_name,
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'name'=>htmlspecialchars($name),
				'phone'=>htmlspecialchars($phone),
				'status'=>$status,
			],
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

		$M = IBuyer::findFirst($id);
		if(!$M){
			$M = new IBuyer;
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
		else{
			$this->view->pick($this->controller.'/form');
		}
	}

	protected function modify(){
		
		if($this->request->isPost()){
            // var_dump($_POST);exit;
			$this->view->disable();
            $id = $this->request->getPost('buyer_id','int');
            $data['username'] = $this->request->getPost('username');
            $data['password'] = $this->request->getPost('password');
            $data['phone'] = $this->request->getPost('phone');
            $data['country_code'] = $this->request->getPost('country_code');
			$data['name'] = $this->request->getPost('name');
			$data['gender'] = $this->request->getPost('gender');

			if($id){
				$Model = IBuyer::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的用户'
					];
				}
			}
			else{

				$Model = new IBuyer;
			}

			try{
				$Model->assign($data);

				if($Model->save()){

					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->buyer_id,$Model->phone.'('.$Model->name.')');
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
			$M = IBuyer::findFirst($id);
			if(!$M){
				throw new \Exception("用户不存在", 1);
				
			}
			if($M->remove()){
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
	 * @aclDesc 冻结/解冻
	 * @return [type] [description]
	 */
	public function freezeAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IBuyer::findFirst($id);
			if(!$M){
				throw new \Exception("用户不存在", 1);
				
			}
			if($M->status>0){
				$res = $M->freeze();
				$msg = '用户已被冻结';
			}
			else{
				$res = $M->unfreeze();
				$msg = '用户已解除冻结';
			}
			if($res){
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
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
	 * @aclDesc 审核通过
	 * @return [type] [description]
	 */
	public function auditAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IBuyer::findFirst($id);
			if(!$M){
				throw new \Exception("用户不存在", 1);
				
			}
			$res = $M->audit();
			if($res){
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
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
	 * @aclDesc 重置密码
	 * @return [type] [description]
	 */
	public function resetpswAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IBuyer::findFirst($id);
			if(!$M){
				throw new \Exception("用户不存在", 1);
				
			}
			$res = $M->resetpsw();
			if($res){
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
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
    
    public function fakeAction(){
        $username = request()->getQuery('username');
        $name = request()->getQuery('name');
        $Buyer = new IBuyer;
        $Buyer->username = $username;
        $Buyer->name = $name;
        $Buyer->password = '111111';
        $Buyer->shop_id = $this->auth->getShopId();
        $Buyer->save();

        var_dump($Buyer->getErrorMsg());
        var_dump($Buyer->buyer_id);
        exit;
    }

}