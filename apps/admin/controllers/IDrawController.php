<?php
namespace Admin\Controllers;

use Common\Models\IDraw;
use Common\Models\IUser;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 提现申请
 * @acl shopadmin
 * @aclCustom single_shop
 */
class IDrawController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '提现';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 列表
	 * @return [type] [description]
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$user_id = $this->request->getQuery('user_id');
		$status = $this->request->getQuery('status');
		$name = trim($this->request->getQuery('name'));
		$phone = trim($this->request->getQuery('phone'));
		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		if($name){

			$conditions[] = 'u.name like :name:';
			$params['name'] = '%'.$name.'%';
		}

		if($phone){
			if(strlen($phone)<4){
				$this->flashSession->error('搜索的手机号码必须大于4位');
				$this->jump();
				exit;
			}

			$conditions[] = 'u.phone like :phone:';
			$params['phone'] = '%'.$phone.'%';
		}

		if($user_id){
			$conditions[] = 'd.user_id=:user_id:';
			$params['user_id'] = $user_id;
		}

		if($status){
			$conditions[] = 'd.status=:status:';
			$params['status'] = $status;
		}

		if($id){
			$conditions[] = 'd.draw_id=:id:';
			$params['id'] = $id;
		}

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns(['d.*'])
                ->from(['d'=>'Common\Models\IDraw'])
                ->join('Common\Models\IUser','d.user_id=u.user_id','u')
                ->where($conditionSql,$params)
                ->orderBy(IDraw::getPkCol().' ASC');

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
				'status'=>$status,
				'user_id'=>$user_id,
				'name'=>$name,
				'phone'=>$phone
			],
		]);

	}

	/**
	 * @aclDesc 审核
	 * @return [type] [description]
	 */
	public function checkAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$act = $this->request->getQuery('act');
			$M = IDraw::findFirst($id);
			if(!$M){
				throw new \Exception("用户不存在", 1);				
			}

			if($act=='pass'){
				$res = $M->checkPass();
				$msg = '提现审核通过';
			}
			elseif($act=='refuse'){
				$res = $M->checkRefuse();
				$msg = '提现请求被拒绝';
			}
			if($res){
				$this->flashSession->success($msg);
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


}