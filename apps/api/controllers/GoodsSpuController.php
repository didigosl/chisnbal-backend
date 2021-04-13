<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IGoodsSpu;
use Common\Models\ICategory;
use Common\Models\IOrderComment;
use Common\Models\ISpec;
use Common\Models\IUserKeyword;
use Common\Models\IAd;
use Common\Models\ISpuCollect;
use Common\Libs\Func;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class GoodsSpuController extends ControllerBase {

	/**
	 * 商品列表
	 * @param integer $category_id
	 * @param integer $label
	 * @param string $order
	 * @param integer $page
	 * @param integer $page_limit
	 * @return [type] [description]
	 */
	public function listAction(){		

        $this->auth();
		$shop_id = (int)$this->post['shop_id'];
		if(!conf('enable_multi_shop')){
			$shop_id = 1;
		}

		$conditions = [];
		$params = [];

		if($shop_id){
			$conditions[] = 'spu.shop_id=:shop_id:';
			$params['shop_id'] = $shop_id;
		}

		if($this->post['category_id']){
			$category_data = $this->db->fetchOne('SELECT * FROM i_category WHERE category_id=:category_id',\Phalcon\Db::FETCH_ASSOC,['category_id'=>$this->post['category_id']]);
			if($category_data){
				if($category_data['level']==3){
					$conditions[] = 'sc.category_id=:category_id:';
					$params['category_id'] = $this->post['category_id'];
					
				}
				else{
					$conditions[] = '(sc.category_id=:category_id: OR c.merger like :merger:)';
					$params['category_id'] = $this->post['category_id'];
					$params['merger'] = ','.ltrim($category_data['merger'],',').$category_data['category_id'].',%';
				}
			}
			
			
		}
		/*else{
			throw new \Exception("必须提供商品分类信息", 2001);
			
		}*/

		if($this->post['sort_id']){
			$sort_data = $this->db->fetchOne('SELECT * FROM i_sort WHERE sort_id=:sort_id',\Phalcon\Db::FETCH_ASSOC,['sort_id'=>$this->post['sort_id']]);
			if($sort_data){
				$conditions[] = '(s.sort_id=:sort_id: OR s.merger like :merger:)';
				$params['sort_id'] = $this->post['sort_id'];
				$params['merger'] = ','.ltrim($sort_data['merger'],',').$sort_data['sort_id'].',%';
			}
			
		}

		// var_dump($conditions,$params);exit;

		if($this->post['label_id']){
			$conditions[] = 'spu.labels like :label_id:';
			$params['label_id'] = '%,'.$this->post['label_id'].',%';
		}

		$conditions[] = ' spu.remove_flag=0 AND spu.status>0 ';

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		 // var_dump($conditionSql,$params);exit;

		$order = '';
		switch ($this->post['order']) {
			case 'priceDesc':
				$order = 'spu.price DESC';
				break;
			case 'priceAsc':
				$order = 'spu.price Asc';
				break;
			case 'onsaleDesc':
				$order = 'spu.onsale_time Desc,spu.spu_id DESC';
				break;
			case 'onsaleAsc':
				$order = 'spu.onsale_time Asc';
				break;
			case 'soldTotalDesc':
				$order = 'spu.sold_total DESC';
				break;
			case 'soldTotalAsc':
				$order = 'spu.sold_total ASC';
				break;
			default:
				$order = 'spu.seq DESC,spu.spu_id DESC';
				break;
			
		}

		$limit = $this->post['page_limit'] ? (int)$this->post['page_limit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$mysql_version = $this->db->fetchColumn("select version()");

		if($category_data['category_id']){
			if($category_data['level']==3){
				if(strpos($mysql_version,'5.5')===0){
					$builder = $this->modelsManager->createBuilder()
		                ->columns(['spu.*','sc.category_id'])
		                ->from(['sc'=>'Common\Models\ISpuCategory'])
		                ->join('Common\Models\IGoodsSpu','sc.spu_id=spu.spu_id','spu')
		                ->where($conditionSql,$params)
		                ->orderBy($order);
				}
				else{
					$builder = $this->modelsManager->createBuilder()
		                ->columns(['spu.*','any_value(sc.category_id)'])
		                ->from(['sc'=>'Common\Models\ISpuCategory'])
		                ->join('Common\Models\IGoodsSpu','sc.spu_id=spu.spu_id','spu')
		                ->where($conditionSql,$params)
		                ->orderBy($order);
				}
				
			}
			else{
				if(strpos($mysql_version,'5.5')===0){
					$builder = $this->modelsManager->createBuilder()
		                ->columns(['spu.*','sc.category_id'])
		                ->from(['sc'=>'Common\Models\ISpuCategory'])
		                ->join('Common\Models\ICategory','c.category_id=sc.category_id','c')
		                ->join('Common\Models\IGoodsSpu','sc.spu_id=spu.spu_id','spu')
		                ->groupBy('sc.spu_id')
		                ->where($conditionSql,$params)
		                ->orderBy($order);
				}
				else{
					$builder = $this->modelsManager->createBuilder()
		                ->columns(['spu.*','any_value(sc.category_id)'])
		                ->from(['sc'=>'Common\Models\ISpuCategory'])
		                ->join('Common\Models\ICategory','c.category_id=sc.category_id','c')
		                ->join('Common\Models\IGoodsSpu','sc.spu_id=spu.spu_id','spu')
		                ->groupBy('sc.spu_id')
		                ->where($conditionSql,$params)
		                ->orderBy($order);
				}
				
			}
		}
		elseif($sort_data['sort_id']){
			$builder = $this->modelsManager->createBuilder()
	                ->from(['spu'=>'Common\Models\IGoodsSpu'])	                
	                ->join('Common\Models\ISort','s.sort_id=spu.sort_id','s')
	                ->where($conditionSql,$params)
	                ->orderBy($order);
	        // $q = $builder->getQuery()->getSql();
	        // var_dump($params);
	        // var_dump($q);exit;
		}
		else{
			$builder = $this->modelsManager->createBuilder()
	                ->from(['spu'=>'Common\Models\IGoodsSpu'])
	                ->where($conditionSql,$params)
	                ->orderBy($order);
		}
		
        

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

				if($category_data['category_id']){

                    $specs = $item['spu']->getFmtSpecData();
                    $skus = [];

                    if ($item['spu']->skus) {
                        foreach ($item['spu']->skus as $Sku) {

                            if ($Sku->status > 0) {

                                //启用待审用户权限限制
                                if(conf('enable_pending_user_permission') && (empty($this->User) || $this->User->status==0)){
                                    $sku_price = '';
                                }
                                else{
                                    //如果商品抢购中，则所有规格的价格都是抢购价格
                                    $sku_price = fmtMoney($item['spu']->sale_spu_id ? $item['spu']->price : $Sku->price);
                                }
                        
                                $skus[] = [
                                    'sku_id' => $Sku->sku_id,
                                    'spec_info' => $Sku->spec_info == 'default' ? '' : $Sku->spec_info,
                                    'stock' => $Sku->stock,
                                    'price' => $sku_price,
                                    'spec_mode' => $Sku->getSpecMode()
                                ];
                            }

                            //存在默认sku，则不需要选择规格，故将规格数组设置为空
                            if ($Sku->spec_info == 'default' && $Sku->status > 0) {
                                $specs = [];
                            }
                        }
                    }

                    if ($item['spu']->sale_spu_id and $item['spu']->SaleSpu) {
                        $stock = $item->sale_stock;
                        //如果抢购限制的最大购买数量，小于商品原本的最小购买数量，则以抢购最大数量为准
                        if ($item['spu']->SaleSpu->per_limit < $item['spu']->min_in_cart) {
                            $min_in_cart = 1;
                            $per_limit = $item['spu']->SaleSpu->per_limit;
                        }
                    } else {

                        $min_in_cart = $item['spu']->min_in_cart;
                        $stock = $item['spu']->stock;
                    }

					$rebates = $item['spu']->getFmtRebates();
					$has_rebate = 0;
					foreach($rebates as $v){
						if(!empty($v['rebate'])){
							$has_rebate = 1;
							break;
						}
                    }
                    
                    //启用待审用户权限限制
                    if(conf('enable_pending_user_permission') && (empty($this->User) || $this->User->status==0)){
                        $price = '';
                        $origin_price = '';
                    }
                    else{
                        $price = fmtMoney($item['spu']->price);
                        $origin_price = fmtMoney($item['spu']->origin_price);
                    }
                    $is_collect='0';
                    $collect_id='';
                    //查询是否有分享
                    $ISpuCollect=ISpuCollect::findFirst(['spu_id=:spu_id: and user_id = :user_id:','bind'=>[
                        'spu_id'=>$item['spu']->spu_id,
                        'user_id'=>$this->User->user_id
                    ]]);
                    if($ISpuCollect){
                        $is_collect='1';
                        $collect_id=$ISpuCollect->collect_id;
                    }
                    $data = [
						'spu_id'=>$item['spu']->spu_id,
                        'is_collect'=>$is_collect,
                        'collect_id'=>$collect_id,
						'spu_name'=>$item['spu']->spu_name,
						'cover'=>Func::staticPath($item['spu']->cover),
						'price'=>$price,
						'origin_price'=>$origin_price,
						'labels'=>$item['spu']->getFmtLabels(),
                        'rebates'=>$has_rebate ? $rebates : null,
                        'unit'=>$item['spu']->unit,

                        'stock' => $stock,                  
                        'min_in_cart' => $min_in_cart,
                        'min_to_buy' => $item['spu']->min_to_buy,
                        'per_limit' => $per_limit ? $per_limit : 0,
                        'has_default_sku' => $item['spu']->has_default_sku,
                        'skus' => $skus,
                        'specs' => $specs,
                    ];
                    
                    if(conf('enable_vip_price')){
                        $data['price2'] = fmtMoney($item['spu']->price2);
                        $data['price3'] = fmtMoney($item['spu']->price3);
                        $data['price4'] = fmtMoney($item['spu']->price4);
                        $data['price5'] = fmtMoney($item['spu']->price5);
                    }

					$list[] = $data;
				}
				else{

                    $specs = $item->getFmtSpecData();
                    $skus = [];

                    if ($item->skus) {
                        foreach ($item->skus as $Sku) {

                            if ($Sku->status > 0) {
                                
                                //启用待审用户权限限制
                                if(conf('enable_pending_user_permission') && (empty($this->User) || $this->User->status==0)){
                                    $sku_price = '';
                                }
                                else{
                                    //如果商品抢购中，则所有规格的价格都是抢购价格
                                    $sku_price = fmtMoney($item->sale_spu_id ? $item->price : $Sku->price);
                                }
                                

                                $skus[] = [
                                    'sku_id' => $Sku->sku_id,
                                    'spec_info' => $Sku->spec_info == 'default' ? '' : $Sku->spec_info,
                                    'stock' => $Sku->stock,
                                    'price' => $sku_price,
                                    'spec_mode' => $Sku->getSpecMode()
                                ];
                            }

                            //存在默认sku，则不需要选择规格，故将规格数组设置为空
                            if ($Sku->spec_info == 'default' && $Sku->status > 0) {
                                $specs = [];
                            }
                        }
                    }

                    if ($item->sale_spu_id and $item->SaleSpu) {
                        $stock = $item->sale_stock;
                        //如果抢购限制的最大购买数量，小于商品原本的最小购买数量，则以抢购最大数量为准
                        if ($item->SaleSpu->per_limit < $item->min_in_cart) {
                            $min_in_cart = 1;
                            $per_limit = $item->SaleSpu->per_limit;
                        }
                    } else {

                        $min_in_cart = $item->min_in_cart;
                        $stock = $item->stock;
                    }

					$rebates = $item->getFmtRebates();
					$has_rebate = 0;
					foreach($rebates as $v){
						if(!empty($v['rebate'])){
							$has_rebate = 1;
							break;
						}
                    }
                    //启用待审用户权限限制
                    if(conf('enable_pending_user_permission') && (empty($this->User) || $this->User->status==0)){
                        $price = '';
                        $origin_price = '';
                    }
                    else{
                        $price = fmtMoney($item->price);
                        $origin_price = fmtMoney($item->origin_price);
                    }

                    $is_collect='0';
                    $collect_id='';
                    //查询是否有分享
                    $ISpuCollect=ISpuCollect::findFirst(['spu_id=:spu_id: and user_id = :user_id:','bind'=>[
                        'spu_id'=>$item->spu_id,
                        'user_id'=>$this->User->user_id
                    ]]);
                    if($ISpuCollect){
                        $is_collect='1';
                        $collect_id=$ISpuCollect->collect_id;
                    }
                    $data = [
						'spu_id'=>$item->spu_id,
						'spu_name'=>$item->spu_name,
						'is_collect'=>$is_collect,
						'collect_id'=>$collect_id,
						'cover'=>Func::staticPath($item->cover),
						'price'=>$price,
						'origin_price'=>$origin_price,
						'labels'=>$item->getFmtLabels(),
                        'rebates'=>$has_rebate ? $rebates : null,
                        'unit'=>$item->unit,
                        'stock' => $stock,                  
                        'min_in_cart' => $min_in_cart,
                        'min_to_buy' => $item->min_to_buy,
                        'per_limit' => $per_limit ? $per_limit : 0,
                        'has_default_sku' => $item->has_default_sku,
                        'skus' => $skus,
                        'specs' => $specs,
					];

                    if(conf('enable_vip_price')){
                        $data['price2'] = fmtMoney($item->price2);
                        $data['price3'] = fmtMoney($item->price3);
                        $data['price4'] = fmtMoney($item->price4);
                        $data['price5'] = fmtMoney($item->price5);
                    }
                     
					$list[] = $data;
				}
				
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

	public function searchAction(){
		$conditions = [];
		$params = [];

		if($this->post['keyword']){
			$conditions[] = 'spu.spu_name like :keyword:';
			$params['keyword'] = '%'.$this->post['keyword'].'%';

			$this->auth();
			if($this->User){
				IUserKeyword::log($this->User->user_id,$this->post['keyword']);
			}
		}
		else{
			throw new \Exception("必须提供关键词", 2001);
			
		}

		if($this->post['sort_id']){
			$conditions[] = 'spu.sort_id=:sort_id:';
			$params['sort_id'] = $this->post['sort_id'];
		}

		$conditions[] = 'spu.remove_flag=0 AND spu.status>0 ';

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		// var_dump($conditionSql,$params);exit;

		$order = '';
		switch ($this->post['order']) {
			case 'priceDesc':
				$order = 'spu.price DESC';
				break;
			case 'priceAsc':
				$order = 'spu.price Asc';
				break;
			case 'onsaleDesc':
				$order = 'spu.onsale_time Desc';
				break;
			case 'onsaleAsc':
				$order = 'spu.onsale_time Asc';
				break;
			default:
				$order = 'spu.onsale_time Desc';
				break;
		}

		$limit = $this->post['pageLimit'] ? (int)$this->post['pageLimit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from(['spu'=>'Common\Models\IGoodsSpu'])
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
                
                //启用待审用户权限限制
                if(conf('enable_pending_user_permission') && (empty($this->User) || $this->User->status==0)){
                    $price = '';
                    $origin_price = '';
                }
                else{
                    $price = fmtMoney($item->price);
                    $origin_price = fmtMoney($item->origin_price);
                }
                $is_collect='0';
                $collect_id='';
                //查询是否有分享
                $ISpuCollect=ISpuCollect::findFirst(['spu_id=:spu_id: and user_id = :user_id:','bind'=>[
                    'spu_id'=>$item->spu_id,
                    'user_id'=>$this->User->user_id
                ]]);
                if($ISpuCollect){
                    $is_collect='1';
                    $collect_id=$ISpuCollect->collect_id;
                }
                $data = [
					'spu_id'=>$item->spu_id,
					'is_collect'=>$is_collect,
					'collect_id'=>$collect_id,
					'spu_name'=>$item->spu_name,
					'cover'=>Func::staticPath($item->cover),
					'price'=>$price,
					'origin_price'=>$origin_price,
					'labels'=>$item->getFmtLabels(),
                    'rebates'=>$item->getFmtRebates(),
                    'unit'=>$item->unit
                ];
                
                if(conf('enable_vip_price')){
                    $data['price2'] = fmtMoney($item->price2);
                    $data['price3'] = fmtMoney($item->price3);
                    $data['price4'] = fmtMoney($item->price4);
                    $data['price5'] = fmtMoney($item->price5);
                }

				$list[] = $data;
			}
		}

		$this->sendJSON([
			'data'=>[
                'total number'=>count($list),
				'total_pages'=>$paginate->total_pages,
				'page_limit'=>$limit,
				'page'=>$page,
				'list'=>$list

			]
		]);
	}


	public function getAction(){

        $this->auth();

		if(!$this->post['spu_id']){
			throw new \Exception("必须提供商品ID", 2001);
			
		}

		$Spu = IGoodsSpu::findFirst($this->post['spu_id']);

		if(!$Spu){
			throw new \Exception("商品不存在", 2001);
			
		}

		if($Spu->remove_flag>0){
			throw new \Exception("商品不存在", 2002);
			
		}

		if($Spu->status<0){
			throw new \Exception("商品已下架", 2002);
			
		}


		/*$specs = $Spu->Cate1->Category->getSpecs();
		$fmtSpecs = [];
		foreach ($specs as $v) {
			$fmtSpecs[$v['spec_name']] = $v['specs'];
		}
		*/
		$specs = $Spu->getFmtSpecData();

		$fmtSpecs = [];
		foreach ($specs as $v) {
			$fmtSpecs[$v['spec_name']] = $v['specs'];
		}
		
		$skus = [];
        $global_spec = [];
		if($Spu->skus){
			foreach ($Spu->skus as $Sku) {
                //全局库存
                if(substr($Sku->spec_info,0,12) == 'global_spec:')
                {
                    $global_spec[$Sku->spec_info] = [
                        'sku_id'=>$Sku->sku_id,
                        'spec_info'=>$Sku->spec_info,
                        'status'=>$Sku->status,
                        'sn'=>$Sku->sku_sn,
                        'stock'=>$Sku->stock,
                        'price'=>fmtMoney($Sku->price),
                        'default_flag'=>intval($Sku->default_flag),
                        'weigh_flag'=>intval($Sku->weigh_flag)
                    ];
                    continue;
                }



			    if($Sku->status>0){
					$mode = [];	//记录当前sku的各个规格mode值
					if($Sku->spec_info!=='default'){
						$spec_info = explode(',',$Sku->spec_info);
						foreach($spec_info as $spec){
							$spec = explode(':',$spec);
							// var_dump($spec);exit;
							//将sku的规格值和spec的规格值比较，确定mode值
							if(is_array($fmtSpecs[$spec[0]])){
								foreach ($fmtSpecs[$spec[0]] as $v) {
									if($v['value']==$spec[1]){
										$mode[] = $v['mode'];
									}
								}
							}
							
						}
					}
					   
                    //启用待审用户权限限制
                    if(conf('enable_pending_user_permission') && (empty($this->User) || $this->User->status==0)){
                        $sku_price = '';
                    }
                    else{
                        //如果商品抢购中，则所有规格的价格都是抢购价格
                        $sku_price = fmtMoney($Spu->sale_spu_id ? $Spu->price : $Sku->price);
                    }

					$skus[] = [
						'sku_id'=>$Sku->sku_id,
						'spec_info'=>$Sku->spec_info=='default'?'':$Sku->spec_info,
						'stock'=>$Sku->stock,
						'price'=>$sku_price,
						'spec_mode'=>sprintf("%06s",implode('',$mode))
					];
				}
				

				//存在默认sku，则不需要选择规格，故将规格数组设置为空
				if($Sku->spec_info=='default' && $Sku->status>0){
					$specs = [];
				}
			}
		}

		$min_to_buy = $Spu->min_to_buy;
		if($Spu->sale_spu_id AND $Spu->SaleSpu){
			$stock = $Spu->sale_stock;
			//如果抢购限制的最大购买数量，小于商品原本的最小购买数量，则以抢购最大数量为准
			if($Spu->SaleSpu->per_limit < $Spu->min_in_cart){
				$min_in_cart = 1;
				$per_limit = $Spu->SaleSpu->per_limit;
			}
		}
		else{

			$min_in_cart = $Spu->min_in_cart;
			$stock = $Spu->stock;
        }
        
        //启用待审用户权限限制
        if(conf('enable_pending_user_permission') && (empty($this->User) || $this->User->status==0)){
            $price = '';
            $origin_price = '';
        }
        else{
            $price = fmtMoney($Spu->price);
            $origin_price = fmtMoney($Spu->origin_price);
        }
        $is_collect='0';
        $collect_id='';
        //查询是否有分享
        $ISpuCollect=ISpuCollect::findFirst(['spu_id=:spu_id: and user_id = :user_id:','bind'=>[
            'spu_id'=>$Spu->spu_id,
            'user_id'=>$this->User->user_id
        ]]);
        if($ISpuCollect){
            $is_collect='1';
            $collect_id=$ISpuCollect->collect_id;
        }
        //全局规格
        $global_ipsec = ISpec::getGlobalSpec();
        $global_sku = [];
        $outTable = '';
        if($global_ipsec['status'] == -2)
        {
            //头部标签
            $outTable .= '<table style="border-collapse: collapse; width: 400px">';
            $outTable .= "<thead>
                            <tr>
                             ";
            foreach($global_ipsec['size'] as $_size)
            {
                $outTable .= '<th style="  border: 1px solid #ddd;padding: 8px">'.$_size.'</th>';
            }
            $outTable .= '</thead>';
            $outTable .= '<tbody style="text-align: center">';

            //获取数据库中已经配置的数据
            foreach($global_ipsec['color'] as $_color)
            {
                $global_sku[$_color] = [];
                $outTable .= '<tr>';
                foreach($global_ipsec['size'] as $_size)
                {
                    $global_sku[$_color][$_size] = 0;
                    $global_spec_key = "global_spec:".$_color."-".$_size;
                    if(isset($global_spec[$global_spec_key]['stock']))
                    {
                        $global_sku[$_color][$_size] = $global_spec[$global_spec_key]['stock'];
                    }
                    else{
                        $global_sku[$_color][$_size] = 0;
                    }
                    $outTable .="<td style='border: 1px solid #ddd;padding: 8px'>".$global_sku[$_color][$_size]."</td>";
                }
                $outTable .= '</tr>';
            }

            $outTable .= '</tbody>';
            $outTable .= '</table>';
        }

		$data = [

			'spu_id'=>$Spu->spu_id,
			'is_collect'=>$is_collect,
			'collect_id'=>$collect_id,
			'sharelink'=>'http://share.xgj.didigo.es/?id='.$Spu->spu_id,
			'shop_id'=>$Spu->shop_id,
			'spu_name'=>$Spu->spu_name,
			'sn'=>$Spu->sn,
			'cover'=>Func::staticPath($Spu->cover),
			'video'=>Func::staticPath($Spu->video),
			'pics'=>$Spu->getFmtPics(),
			'labels'=>$Spu->getFmtLabels(),
			'coupon'=>conf('enable_vip_rebate') ? $Spu->getFmtRebatesAndDiscounts() : null,
			'origin_price'=>$origin_price,
			'price'=>$price,
            'stock'=>$stock,
            'unit'=>$Spu->unit,
			'min_in_cart'=>$min_in_cart,
			'min_to_buy'=>$Spu->min_to_buy,
			'per_limit'=>$per_limit?$per_limit:0,
			'content'=>Func::contentStaticPath($Spu->content),
            'global_ipsec'=>$outTable, //全局规格表格
			'status'=>$Spu->status,
			'status_text'=>$Spu->getStatusContext($Spu->status),
			'has_default_sku'=>$Spu->has_default_sku,
			'skus'=>$skus,
			'specs'=>$specs,
			'comments'=>IOrderComment::getComments($Spu->spu_id,1,4),
            'flash_sale_flag'=>$Spu->sale_spu_id ? 1 : 0,
            
        ];
        
        if(conf('enable_vip_price')){
            $data['price2'] = fmtMoney($Spu->price2);
            $data['price3'] = fmtMoney($Spu->price3);
            $data['price4'] = fmtMoney($Spu->price4);
            $data['price5'] = fmtMoney($Spu->price5);
        }

		$this->sendJSON([
			'data'=>$data
		]);
    }
    
    //广告系列商品
    public function seriesAction(){
        $ad_id = $this->post['ad_id'];
        $order = $this->post['order'];

        if($ad_id){
            $Ad = IAd::findFirst($ad_id);
            if(!$Ad){
                throw new \Exception('广告信息不存在');
            }

            if($Ad->link_type!=='goodsSeries'){
                throw new \Exception('广告类型不符');
            }

            if(empty($Ad->link_id)){
                throw new \Exception('未指定商品');
            }

            $orderBy = '';
            switch ($order) {
                case 'priceDesc':
                    $orderBy = 'price DESC';
                    break;
                case 'priceAsc':
                    $orderBy = 'price Asc';
                    break;
                case 'onsaleDesc':
                    $orderBy = 'onsale_time Desc';
                    break;
                case 'onsaleAsc':
                    $orderBy = 'onsale_time Asc';
                    break;
                default:
                    $orderBy = 'onsale_time Desc';
                    break;
            }

            $goods = $Ad->getLinkSeries($orderBy);

            $list = [];
            foreach($goods as $item){

                //启用待审用户权限限制
                if(conf('enable_pending_user_permission') && (empty($this->User) || $this->User->status==0)){
                    $price = '';
                    $origin_price = '';
                }
                else{
                    $price = fmtMoney($item->price);
                    $origin_price = fmtMoney($item->origin_price);
                }
                $is_collect='0';
                $collect_id='';
                //查询是否有分享
                $ISpuCollect=ISpuCollect::findFirst(['spu_id=:spu_id: and user_id = :user_id:','bind'=>[
                    'spu_id'=>$item->spu_id,
                    'user_id'=>$this->User->user_id
                ]]);
                if($ISpuCollect){
                    $is_collect='1';
                    $collect_id=$ISpuCollect->collect_id;
                }
                $data = [
                    'spu_id'=>$item->spu_id,
                    'collect_id'=>$collect_id,
                    'is_collect'=>$is_collect,
                    'spu_name'=>$item->spu_name,
                    'cover'=>Func::staticPath($item->cover),
                    'price'=>$price,
                    'origin_price'=>$origin_price,
                    'labels'=>$item->getFmtLabels(),
                    'rebates'=>$item->getFmtRebates(),
                    'unit'=>$item->unit
                ];

                if(conf('enable_vip_price')){
                    $data['price2'] = fmtMoney($item->price2);
                    $data['price3'] = fmtMoney($item->price3);
                    $data['price4'] = fmtMoney($item->price4);
                    $data['price5'] = fmtMoney($item->price5);
                }

                $list[] = $data;
            }

            $this->sendJSON([
                'data'=>[
                    'list'=>$list,
                ]
            ]);
        }
    }

    /**
     * 获取分类下的商品
     */
    public function getlabelspuAction(){
        $this->auth();
        $label = $this->post['label'];
        $offset = $this->post['offset'];
        $IGoods=IGoodsSpu::find([
            "status=1 and remove_flag=0 and labels like :labels:",
            "bind"=>[
                'labels'=>'%,'.$label.',%'
            ],
            "columns" => "spu_id,spu_name,cover,price",
            "limit" => array("number" => 10, "offset" => $offset)
        ])->toArray();
        foreach ($IGoods as &$IGood){
            $is_collect='0';
            $collect_id='';
            //查询是否有分享
            $ISpuCollect=ISpuCollect::findFirst(['spu_id=:spu_id: and user_id = :user_id:','bind'=>[
                'spu_id'=>$IGood['spu_id'],
                'user_id'=>$this->User->user_id
            ]]);
            if($ISpuCollect){
                $is_collect='1';
                $collect_id=$ISpuCollect->collect_id;
            }
            $IGood['is_collect']=$is_collect;
            $IGood['collect_id']=$collect_id;
            $IGood['price']=fmtMoney($IGood['price']);
            $IGood['cover']=Func::staticPath($IGood['cover']);
        }
        $this->sendJSON(['data'=>$IGoods]);
    }

}
