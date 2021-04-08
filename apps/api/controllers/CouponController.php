<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Libs\Func;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class CouponController extends ControllerAuth {


	public function listAction(){

		$conditions = [];
		$params = [];

		$conditions[] = 'user_id=:user_id:';
		$params['user_id'] = $this->User->user_id;

		$conditions[] = 'c.status=2 AND cu.use_flag=0';

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';


		$limit = $this->post['page_limit'] ? (int)$this->post['page_limit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$builder = $this->modelsManager->createBuilder()
                ->columns(['c.*','cu.coupon_user_id'])
                ->from(['cu'=>'Common\Models\ICouponUser'])
                ->join('Common\Models\ICoupon','cu.coupon_id=c.coupon_id','c')
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
				$list[] = [
					'coupon_user_id'=>$item['coupon_user_id'],
					'coupon_name'=>$item['c']->coupon_name,
					'amount'=>fmtMoney($item['c']->amount),
					'start_time'=>$item['c']->start_time,
					'end_time'=>$item['c']->end_time,
					'min_limit'=>$item['c']->min_limit ? fmtMoney($item['c']->min_limit) : 0,
					'with_rebate'=>$item['c']->with_rebate,
					'with_discount'=>$item['c']->with_discount,
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
