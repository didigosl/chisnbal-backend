<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IPush;
use Common\Models\IPushReaded;
use Common\Libs\Func;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class PushMsgController extends ControllerAuth {

	public function listAction(){

		$conditions = [];
		$params = [];

		$conditions[] = 'status=2';

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$limit = $this->post['page_limit'] ? (int)$this->post['page_limit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$builder = $this->modelsManager->createBuilder()
                ->from(['p'=>'Common\Models\IPush'])
                ->columns(['p.*','r.push_readed_id'])
                ->leftJoin('Common\Models\IPushReaded','p.push_id=r.push_id','r')
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
					'push_id'=>$item['p']->push_id,
					'content'=>$item['p']->content,
					'create_time'=>$item['p']->create_time,
					'is_readed'=>$item->push_readed_id ? 1 : 0,
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
    
    public function getAction(){
        $push_id = $this->post['push_id'];
        $Push = IPush::findFirst($push_id);

        if(!$Push){
            throw new \Exception('消息不存在');
        }

        $PushReaded = new IPushReaded;
        $PushReaded->assign([
            'push_id'=>$push_id,
            'user_id'=>$this->User->user_id,
        ]);
        $PushReaded->save();

        $this->sendJSON([
            'data'=>[
                'push_id'=>$Push->push_id,
                'content'=>$Push->content,
                'create_time'=>$Push->create_time
            ]
        ]);
    }
	
}
