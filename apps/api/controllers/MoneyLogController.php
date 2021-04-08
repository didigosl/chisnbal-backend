<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IMoneyLog;
use Common\Libs\Func;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MoneyLogController extends ControllerAuth {

	public function listAction(){

		$conditions = [];
		$params = [];

		$conditions[] = 'user_id=:user_id:';
		$params['user_id'] = $this->User->user_id;

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$limit = $this->post['page_limit'] ? (int)$this->post['page_limit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$builder = $this->modelsManager->createBuilder()
                ->from('Common\Models\IMoneyLog')
                ->where($conditionSql,$params)
                ->orderBy($order);        

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => $limit,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		$paginate = $paginator->getPaginate();
		$list = [];
		if($paginate->items){
			foreach($paginate->items as $item){
				if($item->type=='draw'){
					$amount = -1*$item->amount;
				}
				else{
					$amount = $item->amount;
				}
				
				$list[] = [
					'money_log_id'=>$item->money_log_id,
					'amount'=>fmtMoney($amount),
					'money'=>fmtMoney($item->money),
					'type'=>$item->type,
					'type_text'=>$item->getTypeContext($item->type),
					'remark'=>$item->remark,
					'create_time'=>$item->create_time
				];
			}
		}

		$this->sendJSON([
			'data'=>[
				'total_pages'=>$paginate->total_pages,
				'page_limit'=>$limit,
				'page'=>$page,
				'list'=>$list,
			]
		]);
	}
	
}
