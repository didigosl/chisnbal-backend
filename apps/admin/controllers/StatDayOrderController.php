<?php
namespace Admin\Controllers;

use Common\Models\StatDayOrder;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 销售报表
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class StatDayOrderController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '销售报表';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$start_day = $this->request->getQuery('start_day');
		$end_day = $this->request->getQuery('end_day');
		$id = $this->request->getQuery('id');

		$start_day = $start_day ? $start_day : date('Y-m-d',strtotime('-10 days'));
		$end_day = $end_day ? $end_day : date('Y-m-d');

		$conditions = [];
		$params = [];

		if($start_day){
			$conditions[] = 'day>=:start:';
			$params['start'] = date('Y-m-d',strtotime($start_day));
		}

		if($end_day){
			$conditions[] = 'day<:end:';
			$params['end'] = date('Y-m-d',strtotime($end_day.' +1 day'));
		}

		if($id){
			$conditions[] = 'job_id=:id:';
			$params['job_id'] = $id;
		}


		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\StatDayOrder')
                ->where($conditionSql,$params)
                ->orderBy('day DESC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 1000,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'start_day'=>$start_day,
				'end_day'=>$end_day,
			],
		]);

	}


}