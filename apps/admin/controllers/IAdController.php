<?php
namespace Admin\Controllers;

use Common\Models\IAd;
use Common\Models\ICategory;
use Common\Models\ISort;
use Common\Models\IGoodsSpu as Spu;
use Common\Models\SAdminLog;
use Common\Libs\Func;
use Admin\Components\ControllerAuth;
use Admin\Components\FileSys;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 广告位
 * @acl superadmin,shopadmin
 */
class IAdController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '广告';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	public function pcAction(){

		$shop_id = $this->auth->getShopId();

		$list = $this->db->fetchAll('SELECT * from i_ad_pos');

		foreach($list as $k=>$v){

			$conditions = [];
			$params = [];

			$conditions[] = 'ad_pos_id=:ad_pos_id:';
			$params['ad_pos_id'] = $v['ad_pos_id'];

			if($shop_id){
				$conditions[] = 'shop_id=:shop_id:';
				$params['shop_id'] = $shop_id;
			}

			$conditionSql = implode(' AND ', $conditions);
			$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

			$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IAd')
                ->where($conditionSql,$params)
                ->orderBy(IAd::getPkCol().' DESC');

			$paginator = new PaginatorQueryBuilder(array(
				"builder" => $builder,
				"limit" => 99999,
				"page" => $page,
				'adapter' => 'queryBuilder',
			));

			$list[$k]['ads'] = $paginator->getPaginate();
		}
		// echo $this->d->one($list);exit;
		$this->view->setVars([
			'list'=>$list
		]);
	}

	/**
	 * @aclDesc 查看
	 * @return [type] [description]
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		if($id){
			$conditions[] = 'ad_id=:id:';
			$params['ad_id'] = $id;
		}

		$conditions[] = 'shop_id=:shop_id: AND ad_pos_id is null';
		$params['shop_id'] = $this->auth->getShopId();

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IAd')
                ->where($conditionSql,$params)
                ->orderBy(IAd::getPkCol().' DESC');

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
				'category_id'=>$category_id
			],
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
		if($this->flashSession->has('error')){
			$cached_data = $this->getCachedFormData();
		}
		$id = $this->request->getQuery('id','int');
		$ad_pos_id = $this->request->getQuery('ad_pos_id','int');

		$ad_pos = [];

		if($ad_pos_id){
			$ad_pos = $this->db->fetchOne("SELECT * FROM i_ad_pos WHERE ad_pos_id=:ad_pos_id",\Phalcon\Db::FETCH_ASSOC,['ad_pos_id'=>$ad_pos_id]);
		}
		

		$M = IAd::findFirst($id);
		if(!$M){
			$M = new IAd;

			if($ad_pos){
				$M->position_type = $ad_pos['position_type'];
				$M->ad_pos_id = $ad_pos['ad_pos_id'];
			}

			if($cached_data){
				$M->assign($cached_data);
			}
		}
		else{
			if($M->ad_pos_id){
				$ad_pos = $this->db->fetchOne("SELECT * FROM i_ad_pos WHERE ad_pos_id=:ad_pos_id",\Phalcon\Db::FETCH_ASSOC,['ad_pos_id'=>$M->ad_pos_id]);
			}
		}

		if($M->position_type=='category'){
			
			if($M->category_id){
				$Category = ICategory::findFirst($M->category_id);
				$cat_select_default = $Category ? trim($Category->merger.$Category->category_id,',') : '';
			}

			if($M->sort_id){
				$Sort = ISort::findFirst($M->sort_id);
				$cat_select_default = $Sort ? trim($Sort->merger.$Sort->sort_id,',') : '';
			}
		}

		if($M->link_type == 'category' AND $M->link_id){

			if($M->shop_id){
				$linkCategory = ICategory::findFirst($M->link_id);
				$link_select_default = $linkCategory ? trim($linkCategory->merger.$linkCategory->category_id,',') : '';

				
			}
			else{
				$linkSort = ISort::findFirst($M->link_id);
				$link_select_default = $linkSort ? trim($linkSort->merger.$linkSort->sort_id,',') : '';

				

			}
			
		}
		elseif($M->link_type == 'goods'){
			$Spu = Spu::findFirst($M->link_id);
			if($Spu){
				$link_spu = [
					'spu_id'=>$Spu->spu_id,
					'cover'=>Func::staticPath($Spu->cover),
					'spu_name'=>$Spu->spu_name,
					'price'=>$Spu->price,
					'stock'=>$Spu->stock,
				];
				$link_spu = json_encode($link_spu,JSON_UNESCAPED_UNICODE);
			}
			else{
				$link_spu = '';
			}
        }
        elseif($M->link_type == 'goodsSeries'){
            $goods = $M->getLinkSeries();
            
            $link_series = [];
            if($goods){
                foreach($goods as $item){
                    $link_series[] = [
                        'spu_id'=>$item->spu_id,
                        'cover'=>Func::staticPath($item->cover),
                        'spu_name'=>$item->spu_name,
                        'price'=>$item->price,
                        'stock'=>$item->stock,
                    ];
                }
            }

            $link_series = json_encode($link_series,JSON_UNESCAPED_UNICODE);
		}

		$this->view->setVars([
			'M'=>$M,
			'cat_select_default'=>$cat_select_default,
			'link_select_default'=>$link_select_default,
            'link_spu'=>$link_spu,
            'link_series'=>$link_series,
			'ad_pos'=>$ad_pos
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
			$this->view->pick($this->dispatcher->getControllerName().'/form');
		}
		
	}

	protected function modify(){
		
		if($this->request->isPost()){
			$this->cacheFormData();

			$id = $this->request->getPost('ad_id','int');
			$data['ad_name'] = $this->request->getPost('ad_name');
			$data['position_type'] = $this->request->getPost('position_type');
			$data['start_time'] = $this->request->getPost('start_time');
			$data['end_time'] = $this->request->getPost('end_time');
			$data['category_id'] = $this->request->getPost('category_id','int');
			$data['sort_id'] = $this->request->getPost('sort_id','int');
			$data['link_type'] = $this->request->getPost('link_type');
			$data['link_id'] = $this->request->getPost('link_id');
			$data['link_url'] = $this->request->getPost('link_url');
			$data['ad_pos_id'] = $this->request->getPost('ad_pos_id');

			$upload_dir = 'shop'.$this->auth->getShopId().'/image';
			$path = FileSys::upload($upload_dir, ['img']);
			if ($path) {
				$data['img'] = $path;
			}

			if($id){
				$Model = IAd::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}

				$Model->status = 1;
			}
			else{

				$Model = new IAd;
			}

			try{
				$data['category_id'] = $data['category_id'] ? $data['category_id'] : 0;
				$Model->assign($data);

				if($Model->save()){
					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->ad_id,$Model->ad_name);
					$this->flashSession->success("数据提交成功");
					$this->clearCachedFormData();
					if(strpos($Model->position_type, 'pc_')!==false){
						$this->jump($this->url->get($this->base_url."/pc"));
					}
					else{
						$this->jump($this->url->get($this->base_url."/index"));
					}
					
				}
				else{
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e){
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
			$M = IAd::findFirst($id);
			
			if($M->delete()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->ad_id,$M->ad_name);
				$data = [
					'status'=>'1',
					'code'=>'',
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