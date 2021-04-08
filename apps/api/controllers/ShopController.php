<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IShop;
use Common\Models\IGoodsSpu as Spu;
use Common\Models\ISort;
use Common\Libs\Func;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ShopController extends ControllerBase {

	/**
	 * 店铺列表
	 * @param integer $category_id
	 * @param integer $label
	 * @param string $order
	 * @param integer $page
	 * @param integer $page_limit
	 * @return [type] [description]
	 */
	public function listAction(){	

		$sort_id = (int)$this->post['sort_id'];

		$conditions = [];
		$params = [];

		if($sort_id){
			$conditions[] = 'sort_id=:sort_id:';
			$params['sort_id'] = $sort_id;
		}

		$conditions[] = ' status=2 ';

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$order = '';
		switch ($this->post['order']) {
			case 'timeDesc':
				$order = 'shop_id DESC';
				break;
			case 'timeAsc':
				$order = 'shop_id ASC';
				break;
			case 'spuDesc':
				$order = 'spu_total DESC';
				break;
			case 'saleDesc':
				$order = 'sale_total DESC';
				break;			
			default:
				$order = 'shop_id ASC';
				break;
		}

		$limit = $this->post['pageLimit'] ? (int)$this->post['pageLimit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$builder = $this->modelsManager->createBuilder()
            ->from('Common\Models\IShop')
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

				$goods = $this->db->fetchAll('SELECT spu_id,spu_name,cover,price FROM i_goods_spu WHERE shop_id=:shop_id AND status=1 AND remove_flag=0 ORDER BY update_time DESC LIMIT 3',\Phalcon\Db::FETCH_ASSOC,['shop_id'=>$item->shop_id]);

				foreach ($goods as $k => $v) {
					$goods[$k]['cover'] = Func::staticPath($v['cover']);
					$goods[$k]['price'] = fmtMoney($v['price']);
				}
								
				$list[] = [
					'shop_id'=>$item->shop_id,
					'shop_name'=>$item->shop_name,
					'intro'=>$item->shop_name,
					'logo'=>Func::staticPath($item->logo),
					'spu_total'=>$item->spu_total,
					'sale_total'=>$item->sale_total,
					'goods'=>$goods
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

		if($this->conf['enable_multi_shop']){
			if(!$this->post['shop_id']){
				throw new \Exception("必须提供店铺ID", 2001);
				
			}

			$shop_id = $this->post['shop_id'];
		}
		else{
			$shop_id = 1;
		}

		$Shop = IShop::findFirst($shop_id);

		if(!$Shop){
			throw new \Exception("店铺不存在", 2001);
			
		}

		if($Shop->status != 2){
			throw new \Exception("店铺未通过审核或已经关闭", 2002);
			
		}

		$data = [
			'shop_id'=>$Shop->shop_id,
			'shop_name'=>$Shop->shop_name,
			'logo'=>Func::staticPath($Shop->logo),
			'bg'=>Func::staticPath($Shop->bg),
			'intro'=>$Shop->intro,
			'tel'=>$Shop->tel,
			'email'=>$Shop->email,
            'address'=>$Shop->address,
            'lan'=>$Shop->lan,
            'lon'=>$Shop->lon,
            'postcode'=>$Shop->postcode,
		];

		$this->sendJSON([
			'data'=>$data
		]);
	}
}
