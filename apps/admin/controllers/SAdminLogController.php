<?php
namespace Admin\Controllers;

use Common\Models\SAdminLog;
use Common\Models\SAdmin;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;


/**
 * @aclDesc 系统日志
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class SAdminLogController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '系统日志';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$username = $this->request->getQuery('username');
		$admin_id = $this->request->getQuery('admin_id');
		$start_day = $this->request->getQuery('start_day');
		$end_day = $this->request->getQuery('end_day');
		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		if($username){

			$Admin = SAdmin::findFirst([
				'username=:username:',
				'bind'=>[
					'username'=>$username
				]
			]);

			if($Admin){
				$admin_id = $Admin->id;
			}
			else{
				$this->flashSession->error("没有找到您指定的管理员");
			}
		}

		if($admin_id){
			$conditions[] = 'admin_id=:admin_id:';
			$params['admin_id'] = $admin_id;
		}

		if($start_day){
			$conditions[] = 'create_time>=:start:';
			$params['start'] = date('Y-m-d H:i:s',strtotime($start_day));
		}

		if($end_day){
			$conditions[] = 'create_time<:end:';
			$params['end'] = date('Y-m-d H:i:s',strtotime($end_day.' +1 day'));
		}

		$conditions[] = 'shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from(['u'=>'Common\Models\SAdminLog'])
                ->where($conditionSql,$params)
                ->orderBy(SAdminLog::getPkCol().' DESC');

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
				'username'=>$username,
				'start_day'=>$start_day,
				'end_day'=>$end_day,
			],
		]);

	}



}