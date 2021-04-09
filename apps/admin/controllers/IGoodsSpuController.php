<?php
namespace Admin\Controllers;

use Common\Models\IGoodsSpu;
use Common\Models\ISpuCategory;
use Common\Models\ISpec;
use Common\Models\ICategory;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Admin\Components\FileSys;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 商品
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class IGoodsSpuController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '商品';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 列表
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$keyword_type = trim($this->request->getQuery('keyword_type'));
		$keyword = trim($this->request->getQuery('keyword'));
		$status = trim($this->request->getQuery('status'));
		$label_id = trim($this->request->getQuery('label_id'));
		$category_id = $this->request->getQuery('category_id');
        $low_stock = $this->request->getQuery('low_stock');

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditions[] = 'spu.shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		if($keyword_type=='spu_name' && !empty($keyword)){
			$conditions[] = 'spu_name like :keyword:';
			$params['keyword'] = '%'.$keyword.'%';
		}

		if($keyword_type=='sn' && !empty($keyword)){
			$conditions[] = 'sn like :keyword:';
			$params['keyword'] = $keyword;
		}

		if($status){
			$conditions[] = 'spu.status=:status:';
			$params['status'] = $status;
		}

		if($label_id){
			$conditions[] = 'spu.labels like :label:';
			$params['label'] = '%,'.$label_id.',%';
		}

		if($category_id){

			$category_data = $this->db->fetchOne('SELECT * FROM i_category WHERE category_id=:category_id',\Phalcon\Db::FETCH_ASSOC,['category_id'=>$category_id]);
			if($category_data['level']==3){
				$conditions[] = 'sc.category_id=:category_id:';
				$params['category_id'] = $category_id;
				
			}
			else{
				$conditions[] = '(sc.category_id=:category_id: OR c.merger like :merger:)';
				$params['category_id'] = $category_id;
				$params['merger'] = ','.ltrim($category_data['merger'],',').$category_data['category_id'].',%';
			}
        }

		if($id){
			$conditions[] = 'spu.spu_id=:spu_id:';
			$params['spu_id'] = $id;
        }
        
        $total_of_low_stock = 0;
        if(conf('enable_low_stock_warning')){
            $low_stock_amount = (int)settings('low_stock_amount');
            if($low_stock && $low_stock_amount){
                $conditions = ['spu.stock<:stock:']            ;
                $params = [
                    'stock'=>$low_stock_amount
                ];
                
            }

            $total_of_low_stock = db()->fetchColumn("SELECT count(1) FROM i_goods_spu WHERE stock<:stock AND remove_flag=0",[
                'stock'=>$low_stock_amount
            ]);
        }
        

		$conditions[] = "spu.remove_flag=0";
		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$mysql_version = $this->db->fetchColumn("select version()");

		if($category_id){
			// var_dump($conditions,$params);exit;
			if($category_data['level']==3){
				if(strpos($mysql_version,'5.5')===0){
					$builder = $this->modelsManager->createBuilder()
		                ->columns(['spu.*','sc.category_id'])
		                ->from(['sc'=>'Common\Models\ISpuCategory'])
		                ->join('Common\Models\IGoodsSpu','sc.spu_id=spu.spu_id','spu')
		                ->groupBy('sc.spu_id')
		                ->where($conditionSql,$params)
		                ->orderBy('spu.seq DESC,spu.spu_id DESC');
				}
				else{
					$builder = $this->modelsManager->createBuilder()
		                ->columns(['spu.*','any_value(sc.category_id)'])
		                ->from(['sc'=>'Common\Models\ISpuCategory'])
		                ->join('Common\Models\IGoodsSpu','sc.spu_id=spu.spu_id','spu')
		                ->groupBy('sc.spu_id')
		                ->where($conditionSql,$params)
		                ->orderBy('spu.seq DESC,spu.spu_id DESC');
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
		                ->orderBy('spu.seq DESC,spu.spu_id DESC');
				}
				else{
					$builder = $this->modelsManager->createBuilder()
		                ->columns(['spu.*','any_value(sc.category_id)'])
		                ->from(['sc'=>'Common\Models\ISpuCategory'])
		                ->join('Common\Models\ICategory','c.category_id=sc.category_id','c')
		                ->join('Common\Models\IGoodsSpu','sc.spu_id=spu.spu_id','spu')
		                ->groupBy('sc.spu_id')
		                ->where($conditionSql,$params)
		                ->orderBy('spu.seq DESC,spu.spu_id DESC');
				}
				
			}
			
		}
		else{
			$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from(['spu'=>'Common\Models\IGoodsSpu'])
                // ->leftJoin('Common\Models\ISpuCategory','sc.spu_id=spu.spu_id','sc')
                ->where($conditionSql,$params)
                ->orderBy('spu.seq DESC,spu.spu_id DESC');
		}
		

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
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'keyword_type'=>$keyword_type,
				'keyword'=>htmlspecialchars($keyword),
				'category_id'=>$category_id,
				'label_id'=>$label_id,
                'status'=>$status,
                'low_stock'=>$low_stock
            ],
            'low_stock'=>$low_stock,
            'total_of_low_stock'=>$total_of_low_stock
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

		$M = IGoodsSpu::findFirst($id);
		if(!$M){
			$M = new IGoodsSpu;
			$M->min_in_cart = 1;
			$M->min_to_buy = 1;
			$rebates = [];
		}
		else{
			$rebates = $M->getFormRebates();
		}
		
		$cur_labels = [];
		$categories = [];
		if($M->spu_id){
			$cur_labels = explode(',',trim($M->labels,','));
			$spu_categories = ISpuCategory::find(['spu_id=:spu_id:','bind'=>['spu_id'=>$M->spu_id],'order'=>'seq asc']);
			foreach ($spu_categories as $SC) {
				$categories[] = [
					'seq'=>$SC->seq,
					'category_id'=>$SC->category_id,
					'merger'=>trim($SC->Category->merger.$SC->category_id,',')
				];
			}

		}

		if($M->sort_id){
			$def_sort = $M->Sort ? trim($M->Sort->merger.$M->sort_id,',') : '';
		}
		else{
			$def_sort = '';
		}
		// var_dump($categories);exit;

		$specs = $M->Cate1->Category->cate_specs;

		$spec_data = $M->spec_data ? json_decode($M->spec_data,JSON_UNESCAPED_UNICODE) : null;
		$spec_data = $spec_data ? $spec_data : [];
		$skus = [];

		$global_spec = [];
		$global_spec_info = ISpec::getGlobalSpec();
		if($global_spec_info['status'] == -2)
        {
            $show_global_spec = true;
        }
		else{
		    $show_global_spec = false;
        }
		if($M->skus){
			foreach($M->skus as $Sku){
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
                }
			    else{
                    $skus[] = [
                        'sku_id'=>$Sku->sku_id,
                        'spec_info'=>$Sku->spec_info,
                        'status'=>$Sku->status,
                        'sn'=>$Sku->sku_sn,
                        'stock'=>$Sku->stock,
                        'price'=>fmtMoney($Sku->price),
                        'default_flag'=>intval($Sku->default_flag),
                        'weigh_flag'=>intval($Sku->weigh_flag)
                    ];
                }
			}

			//print_r($skus);
			//exit;

		}
		// var_dump(json_encode($skus,JSON_UNESCAPED_UNICODE));exit;
        // var_dump($spec_data);exit;
        
        $user_levels = db()->fetchAll("SELECT level_id,level_name FROM i_user_level WHERE level_id>1 ORDER BY seq ASC");

		$this->view->setVars([
			'M'=>$M,
			'specs'=>$specs ? $specs : [],
			'spec_data'=>$spec_data,
			'spec_data_json'=>json_encode($spec_data,JSON_UNESCAPED_UNICODE),
			'rebates'=>$rebates,
			'skus'=>json_encode($skus,JSON_UNESCAPED_UNICODE),
			'cur_labels'=>$cur_labels,
			'categories'=>$categories,
			'def_sort'=>$def_sort,
            'enable_no_sku'=>$this->conf['enable_no_sku'],
            'user_levels'=>$user_levels,
            'show_global_spec' => $show_global_spec,
            'global_spec' => $global_spec,
            'global_spec_info' => $global_spec_info
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
			$id = $this->request->getPost('spu_id','int');
			$data['spu_name'] = $this->request->getPost('spu_name');
			$data['sn'] = $this->request->getPost('sn');
			$data['video'] = $this->request->getPost('video');			
			$data['origin_price'] = $this->request->getPost('origin_price');
			$data['origin_price'] = fmtPrice($data['origin_price']);
			$data['price'] = $this->request->getPost('price');
            $data['price'] = fmtPrice($data['price']);

            if(conf('enable_vip_price')){
                $data['price2'] = $this->request->getPost('price2');
                $data['price2'] = fmtPrice($data['price2']);
                $data['price3'] = $this->request->getPost('price3');
                $data['price3'] = fmtPrice($data['price3']);
                $data['price4'] = $this->request->getPost('price4');
                $data['price4'] = fmtPrice($data['price4']);
                $data['price5'] = $this->request->getPost('price5');
                $data['price5'] = fmtPrice($data['price5']);
            }

			$data['cost_price'] = $this->request->getPost('cost_price');	
			$data['cost_price'] = fmtPrice($data['cost_price']);					
			$data['stock'] = $this->request->getPost('stock');
			$data['unit'] = $this->request->getPost('unit');
			$data['min_in_cart'] = $this->request->getPost('min_in_cart');
            $data['min_to_buy'] = $this->request->getPost('min_to_buy');
            
            $data['weight'] = $this->request->getPost('weight');
            $data['length'] = $this->request->getPost('length');
            $data['width'] = $this->request->getPost('width');
            $data['height'] = $this->request->getPost('height');
            $data['content'] = $this->request->getPost('content');
            $data['seq'] = $this->request->getPost('seq');
            
			//商品选中的规格
			$spec = $this->request->getPost('spec');
			if(is_array($spec)){
				$data['spec_data'] = json_encode($spec,JSON_UNESCAPED_UNICODE);
			}

			//商品单品数据
            $skus = $this->request->getPost('skus');
            if($skus){
                $skus = json_decode($skus,JSON_UNESCAPED_UNICODE);
                $data['weigh_flag'] = 0;
            }
            else{
                $data['weigh_flag'] = $this->request->getPost('weigh_flag') ? 1 : 0;
            }
            $global_spec_sku = $this->request->getPost('global_spec_sku');
            if(!empty($global_spec_sku))
            {
                if(!is_array($skus))
                {
                    $skus = [];
                }


                foreach($global_spec_sku as $_spec_sn_all => $_spec_stock)
                {
                    list($_spec_sn,$_sku_id) = explode(":::",$_spec_sn_all);
                    $skus[] = [
                        'spec_info' => $_spec_sn,
                        'status' => 1,
                        'sn' => $_spec_sn,
                        'stock' => $_spec_stock,
                        'price' => 0,
                        'sku_id' => $_sku_id,
                        'default_flag' => 0,
                        'weight_flag' => 0,
                    ];
                }
            }
			//优惠设置
			$rebate_with_discount = $this->request->getPost('rebate_with_discount');
			$data['rebate_with_discount'] = $rebate_with_discount ? 1 : 0;

			//标签设置
			$labels = $this->request->getPost('labels');
			$data['labels'] = is_array($labels) ? ','.implode(',',$labels).',' : '';

			//商品相册
			$pics = $this->request->getPost('pics');
			// var_dump($pics);exit;
			if(count($pics)){
				$data['pics'] = implode(',', $pics);
			}
			else{
				$data['pics'] = '';
			}

			//返利设置
			$rebate = $this->request->getPost('rebate');
			if(is_array($rebate)){
				foreach ($rebate as $k => $v) {
					$rebate[$k] = fmtPrice($v);
				}
				$data['rebate'] = json_encode($rebate,JSON_UNESCAPED_UNICODE);
			}
			else{
				$data['rebate'] = '';
			}
			

			//分类设置
			$category_id1 = $this->request->getPost('category_id1');
			$category_id2 = $this->request->getPost('category_id2');
			$category_id3 = $this->request->getPost('category_id3');
			$categories = [];
			if($category_id1){
				$categories[] = $category_id1;
			}
			if($category_id2){
				$categories[] = $category_id2;
			}
			if($category_id3){
				$categories[] = $category_id3;
			}
			
            $data['sort_id'] = $this->request->getPost('sort_id','int');
            $data['distribution_type_id'] = $this->request->getPost('distribution_type_id','int');
            
            
			if($id){
				$Model = IGoodsSpu::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{
				$Model = new IGoodsSpu;
			}

			//print_r($_POST);
			//exit;


			$upload_dir = 'shop'.$this->auth->getShopId().'/image';
			$images = FileSys::uploadAndThumb($upload_dir, ['cover']);
			if ($images['cover']) {
				$data['cover'] = $images['cover']['mid'];
			}
			$this->db->begin();
			// var_dump($data);exit;
			try{
				$Model->assign($data);

				if($Model->save()){
					$Model->updateCategory($categories);
					// var_dump($skus);exit;
					$Model->updateSkus($skus);
                    $Model->updateSpecs($spec);
                    
                    $values = [];
                    if(!empty($id)){
                        $attrs = $Model->toArray();
                        $changedFields = $Model->getUpdatedFields();
                        foreach($attrs as $k=>$v){
                            if(in_array($k,$changedFields)){
                                $values[$k] = $v;
                            }
                        }
                    }

					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->spu_id,$Model->spu_name,json_encode($values));
					$this->db->commit();
					$this->flashSession->success("数据提交成功");
					$referer = $this->request->getPost('referer');
					$redirect_url = $referer ? $referer : $this->url->get('i_goods_spu/index');
					$this->jump($redirect_url);
		
				}
				else{
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e){
				$this->db->rollback();
				// exit($e->getMessage());
				throw new \Exception($e->getMessage(), 1);
			}

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
			$M = IGoodsSpu::findFirst($id);
			
			$this->db->begin();
			if($M->remove()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->spu_id,$M->spu_name);
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}
	}

	/**
	 * @aclDesc 下架/上架
	 * @return [type] [description]
	 */
	public function saleAction(){
		$this->view->disable();

		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IGoodsSpu::findFirst($id);
			if(!$M){
				throw new \Exception("商品不存在", 1);
				
			}
			$this->db->begin();
			if($M->status>0){
				$res = $M->offSale();
				$msg = '商品已下架';
			}
			else{
				$res = $M->onSale();
				$msg = '商品已上架';
			}
			if($res){
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}
		else{
			$this->sendJSON([
				'status'=>'0',
				'code'=>'',
				'msg'=>'error method'
			]);
		}
	}

	/**
	 * @aclDesc 批量上架
	 */
	public function allOnSaleAction(){
		if($this->request->isAjax()){
			$this->view->disable();
			$ids = $this->request->getPost('ids');
			if(!is_array($ids) || count($ids)==0){
				throw new \Exception("没有选中任何需要操作的数据", 1);
				
			}

			$this->db->begin();
			try{
				$success = 0;
				foreach($ids as $id){
					$M = IGoodsSpu::findFirst($id);
					if($M->status==1){
						continue;
					}

					if($M->onSale()){
						$success++;
					}
				}
				$this->db->commit();
				$this->sendJSON([
					'status'=>1,
					'msg'=>'操作完成，'.$success.'条商品成功设置为上架状态'
				]);
			} catch( \Exception $e){
				$this->db->rollback();
				$success = 0;
				throw new \Exception($e->getMessage(), 1);
				
			}
			
		}
	}

	/**
	 * @aclDesc 批量下架
	 */
	public function allOffSaleAction(){
		if($this->request->isAjax()){
			$this->view->disable();
			$ids = $this->request->getPost('ids');
			if(!is_array($ids) || count($ids)==0){
				throw new \Exception("没有选中任何需要操作的数据", 1);
				
			}

			$this->db->begin();
			try{
				$success = 0;
				foreach($ids as $id){
					$M = IGoodsSpu::findFirst($id);
					if($M->status==-1){
						continue;
					}

					if($M->offSale()){
						$success++;
					}
				}
				$this->db->commit();
				$this->sendJSON([
					'status'=>1,
					'msg'=>'操作完成，'.$success.'条商品成功设置为下架状态'
				]);
			} catch( \Exception $e){
				$this->db->rollback();
				$success = 0;
				throw new \Exception($e->getMessage(), 1);
				
			}
			
		}
	}

	/**
	 * @aclDesc 批量删除
	 */
	public function allDeleteAction(){
		if($this->request->isAjax()){
			$this->view->disable();
			$ids = $this->request->getPost('ids');
			if(!is_array($ids) || count($ids)==0){
				throw new \Exception("没有选中任何需要操作的数据", 1);
				
			}

			$this->db->begin();
			try{
				$success = 0;
				foreach($ids as $id){
					$M = IGoodsSpu::findFirst($id);
					if(!$M){
						throw new \Exception("所选商品不存在", 1);
						
					}

					if($M->remove()){
						SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->spu_id,$M->spu_name);
						$success++;
					}
				}
				$this->db->commit();
				$this->flashSession->success('操作完成，'.$success.'条商品成功删除');
				$this->sendJSON([
					'status'=>1,
					'msg'=>'操作完成，'.$success.'条商品成功删除'
				]);
			} catch( \Exception $e){
				$this->db->rollback();
				$success = 0;
				throw new \Exception($e->getMessage(), 1);
				
			}
			
		}
    }
    
    public function quickUpdateAction(){
        if($this->request->isAjax()){
            $id = $this->request->getPost('id','int');
            $col = $this->request->getPost('col');
            $val = $this->request->getPost('val');

            if($col=='price'){
                $val = fmtPrice($val);
            }

			$Spu = IGoodsSpu::findFirst($id);
			if(!$Spu){
				throw new \Exception("商品不存在", 1);
				
			}
			$this->db->begin();
            $Spu->$col = $val;
            $res = $Spu->save();
            
			if($res){
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}
		else{
			$this->sendJSON([
				'status'=>'0',
				'code'=>'',
				'msg'=>'error method'
			]);
		}
    }

	/**
	 * @aclDesc 搜索
	 */
	public function searchAction(){
		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$keyword = trim($this->request->getQuery('keyword'));
		$category_id = $this->request->getQuery('category_id');
		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditions[] = 'spu.shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		if($category_id){
			$conditions[] = '(c.category_id=:category_id: OR FIND_IN_SET(:category_id:,c.merger))';
			$params['category_id'] = $category_id;
		}

		if(!empty($keyword)){
			$conditions[] = 'spu_name like :keyword: OR sn like :keyword:';
			$params['keyword'] = '%'.$keyword.'%';
		}

		$conditions[] = ' spu.status>0 AND spu.remove_flag=0 ';

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns(['spu.*'])
                ->from(['sc'=>'Common\Models\ISpuCategory'])
                ->join('Common\Models\IGoodsSpu','sc.spu_id=spu.spu_id','spu')
                ->join('Common\Models\ICategory','sc.category_id=c.category_id','c')
                ->where($conditionSql,$params)
                ->groupBy('spu.spu_id')
                ->orderBy('spu.seq DESC,spu.spu_id DESC');
        
        $q = $builder->getQuery()->getSql();

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 10,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));
		$paginate = $paginator->getPaginate();
		unset($paginator);

		$list = [];
		foreach ($paginate->items as $k=>$item) {
			$list[$k] = $item->toArray();
            $list[$k]['price'] = fmtMoney($list[$k]['price']);
            $list[$k]['cover'] = $item->getFmtCover();
		}

		$this->view->disable();
		$data = [
			'status'=>'1',
			'code'=>'',
			'data'=>[
				'list'=>$list,
				'total_pages'=>$paginate->total_pages,
			]
		];
		$this->sendJSON($data);		
	}

	public function updateCategoryStatAction(){

		$list = ICategory::find();

		foreach ($list as $Category) {
			$merger = '';
	        if($Category->parent_id){
	            $merger = ','.trim($Category->merger.$Category->category_id,',').',';
	        }
	        else{
	            $merger = ','.$Category->category_id.',';
	        }

	        $sql = "SELECT count(distinct spu.spu_id) 
	            FROM i_spu_category as sc
	            JOIN i_goods_spu as spu on sc.spu_id=spu.spu_id
	            WHERE spu.remove_flag=0 AND merger like '".$merger."%'";

			$total = $this->db->fetchColumn($sql);


			echo $sql.'<br>';
			echo $total.'<br>';

			$Category->spu_total = $total;
			$Category->save();

		}
		exit;

	}

}