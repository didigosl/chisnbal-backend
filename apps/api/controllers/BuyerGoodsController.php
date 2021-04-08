<?php

namespace Api\Controllers;

use Api\Components\ControllerBuyer;
use Common\Models\IBuyerGoods;
use Common\Components\Upload;
use Common\Libs\Func;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class BuyerGoodsController extends ControllerBuyer {

	public function listAction(){		
        $this->auth(); 
        $shop_id = $this->Buyer->shop_id;
        
        $buyer_id = $this->post['buyer_id'];
        $status = $this->post['status'];
        $name = $this->post['name'];
        $filter = $this->post['filter'];
		
		$conditions = [];
		$params = [];

		if($shop_id){
			$conditions[] = 'shop_id=:shop_id:';
			$params['shop_id'] = $shop_id;
        }
        
        if($filter=='mine'){
            $buyer_id = $this->Buyer->buyer_id;
        }

		if($buyer_id){
			$conditions[] = 'buyer_id = :buyer_id:';
			$params['buyer_id'] = $buyer_id;
        }
        
        if($status){
			$conditions[] = 'status = :status:';
			$params['status'] = $status;
        }
        
        if($name){
			$conditions[] = 'name like :name:';
			$params['name'] = '%'.$name.'%';
        }

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$order = ' create_time DESC';

		$limit = $this->post['page_limit'] ? (int)$this->post['page_limit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$builder = $this->modelsManager->createBuilder()
	                ->from('Common\Models\IBuyerGoods')
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
                    'buyer_goods_id'=>$item->buyer_goods_id,
                    'name'=>$item->name,
                    'cover'=>Func::staticPath($item->cover),
                    'price'=>fmtMoney($item->price),
                    'num'=>$item->num,
                    'unit'=>$item->unit,
                    'create_time'=>$item->create_time,
                    'buyer_id'=>$item->buyer_id,
                    'buyer_name'=>$item->Buyer->name
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
        $this->auth(); 
		if(!$this->post['buyer_goods_id']){
			throw new \Exception("必须提供商品ID", 2001);
		}

		$Goods = IBuyerGoods::findFirst($this->post['buyer_goods_id']);

		if(!$Goods){
			throw new \Exception("商品不存在", 2001);
		}

		if($Goods->status<0){
			throw new \Exception("商品已下架", 2002);
		}


		$data = [
			'buyer_goods_id'=>$Goods->buyer_goods_id,
			'shop_id'=>$Goods->shop_id,
			'name'=>$Goods->name,
            'sn'=>$Goods->sn,
            'price'=>fmtMoney($Goods->price),
            'num'=>$Goods->num,
            'unit'=>$Goods->unit,
			'cover'=>Func::staticPath($Goods->cover),
			'pics'=>$Goods->getFmtPics(),			
			'status'=>$Goods->status,
			'status_text'=>$Goods->getStatusContext($Goods->status),
			
		];

		$this->sendJSON([
			'data'=>$data
		]);
    }

    public function createAction(){
        
        $this->auth();        
        $data = [];

        $data['name'] = $this->post['name'];
		$data['sn'] = $this->post['sn'];
        $data['num'] = $this->post['num'];
        $data['unit'] = $this->post['unit'];
		$data['price'] = fmtPrice($this->post['price']);
        $data['buyer_id'] = $this->Buyer->buyer_id;

        $Model = new IBuyerGoods;

        $this->db->begin();
        try{
            
            if ($this->request->hasFiles()) {

                $Upload = new Upload;
                $files = $Upload->exec('images', ['pics' => 'image']);
                $this->log($files);
                $pics = [];
                if(is_array($files)){
                    foreach($files as $f){
                        $pics[] = '/uploads/'.$f['path'];
                    }
                }
                if(!empty($pics)){
                    $data['pics'] = implode(',',$pics);
                }
                
            }

            if(!empty($pics)){
                $data['cover'] = $pics[0];
            }
            
            $Model->assign($data);

            if($Model->save()){

                $this->db->commit();
                $this->flashSession->success("数据提交成功");
                $this->sendJSON([
                    'data'=>['buyer_goods_id'=>$Model->buyer_goods_id]
                ]);
    
            }
            else{
                throw new \Exception($Model->getErrorMsg());
            }
        } catch (Exception $e){
            $this->db->rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function updateAction(){
        $this->auth();
        
        $data = [];

        $buyer_goods_id = (int)$this->post['buyer_goods_id'];
        $data['name'] = $this->post['name'];
		$data['sn'] = $this->post['sn'];
        $data['num'] = $this->post['num'];
        $data['unit'] = $this->post['unit'];
		$data['price'] = fmtPrice($this->post['price']);
        $old_pics = $this->post['old_pics'];

        $Model = IBuyerGoods::findFirst($buyer_goods_id);

		$pics = [];
        $this->db->begin();
        try{
			
			if($old_pics){
                $old_pics = json_decode($old_pics);
                $old_pics = $old_pics ? $old_pics : [];
                if(is_array($old_pics)){
                    foreach($old_pics as $k=>$v){
                        $old_pics[$k] = str_ireplace('http://'.request()->getHttpHost(),'',$v);
                    }
                }
			}
			
            if ($this->request->hasFiles()) {

                $Upload = new Upload;
                $files = $Upload->exec('images', ['pics' => 'image']);
                $this->log($files);
                $pics = [];
                if(is_array($files)){
                    foreach($files as $f){
                        $pics[] = '/uploads/'.$f['path'];
                    }
                }
                
			}
			
			$pics = array_merge($old_pics,$pics);
            if(!empty($pics)){
                $data['pics'] = implode(',',$pics);
            }

            if(!empty($Model->cover)){
                $data['cover'] = $pics[0];
            }
            
            $Model->assign($data);

            if($Model->save()){

                $this->db->commit();
                $this->flashSession->success("数据提交成功");
                $this->sendJSON([
                    'data'=>['buyer_goods_id'=>$Model->buyer_goods_id]
                ]);
    
            }
            else{
                throw new \Exception($Model->getErrorMsg());
            }
        } catch (Exception $e){
            $this->db->rollback();
            throw new \Exception($e->getMessage());
        }
    }

}
