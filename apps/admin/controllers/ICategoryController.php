<?php
namespace Admin\Controllers;

use Common\Models\ICategory;
use Common\Models\IUserLevel;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Admin\Components\FileSys;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 商品分类
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class ICategoryController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '商品分类';
		$this->view->setVar('controller_name',$this->controller_name);
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

		$conditions[] = 'shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$categories = ICategory::find([$conditionSql,'bind'=>$params,'order'=>'rank asc']);
		$levels = IUserLevel::find(['level_id>1']);

		$tmp = $this->db->fetchAll("SELECT * FROM i_rebate_category ");
		$rebates = [];
		foreach($tmp as $v){
			$rebates[$v['category_id']][$v['level_id']] = [
				'rebate'=>$v['rebate'],
				'rebate_type'=>$v['rebate_type']
			];
		}

		$tmp = $this->db->fetchAll("SELECT * FROM i_discount_category ");
		$discounts = [];
		foreach($tmp as $v){
			$discounts[$v['category_id']][$v['level_id']] = [
				'discount'=>$v['discount'],
				'discount_type'=>$v['discount_type']
			];
		}
		// var_dump($rebates);exit;
		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'action_name'=>'列表',
			'categories' =>	$categories,
			'levels' => $levels,
			'rebates'=>$rebates,
			'discounts'=>$discounts,
			'vars' => [
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

		$M = ICategory::findFirst($id);
		if(!$M){
			$M = new ICategory;

			if($cached_data){
				$M->assign($cached_data);
			}
		}
		
		$levels = IUserLevel::find(['level_id>1']);
		if($M->category_id){
			$tmp = $this->db->fetchAll('SELECT * FROM i_rebate_category WHERE category_id=:category_id',\Phalcon\Db::FETCH_ASSOC,['category_id'=>$M->category_id]);
			$rebates = [];
			foreach ($tmp as $v) {				
				$rebates[$v['level_id']] = $v;
				if($v['rebate_type']==1){
					$rebates[$v['level_id']]['rebate'] = fmtMoney($v['rebate']);
				}
			}

			$tmp = $this->db->fetchAll('SELECT * FROM i_discount_category WHERE category_id=:category_id',\Phalcon\Db::FETCH_ASSOC,['category_id'=>$M->category_id]);
			$discounts = [];
			foreach ($tmp as $v) {
				$discounts[$v['level_id']] = $v;
				if($v['discount_type']==1){
					$discounts[$v['level_id']]['discount'] = fmtMoney($v['discount']);
				}
			}
		}

		$this->view->setVars([
			'M'=>$M,
			'levels'=> $levels,
			'rebates'=>$rebates,
			'discounts'=>$discounts
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
			$id = $this->request->getPost('category_id','int');
			$data['category_name'] = $this->request->getPost('category_name');
			$data['parent_id'] = $this->request->getPost('parent_id');
			$data['seq'] = $this->request->getPost('seq','int');
			$data['recommend_flag'] = $this->request->getPost('recommend_flag','int');

			$rebates = $this->request->getPost('rebates');
			$rebate_types = $this->request->getPost('rebate_types');
			$discounts = $this->request->getPost('discounts');
			$discount_types = $this->request->getPost('discount_types');

			if($id){
				$Model = ICategory::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new ICategory;
			}

			$upload_dir = 'shop'.$this->auth->getShopId().'/image';
			$path = FileSys::upload($upload_dir, ['category_cover']);
			if ($path) {
				$data['category_cover'] = $path;
			}

			if($data['recommend_flag']){
				$path = FileSys::upload($upload_dir, ['recommend_pic']);
				if ($path) {
					$data['recommend_pic'] = $path;
				}
			}
			
		
			$Model->assign($data);

			if($Model->save()===false){

				throw new \Exception($Model->getErrorMsg(), 1);
			}
			else{
				$Model->saveRebates($rebates,$rebate_types);
				$Model->saveDiscounts($discounts,$discount_types);

				SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->category_id,$Model->category_name);
				$this->flashSession->success("数据提交成功");
				$this->clearCachedFormData();
				$this->jump($this->url->get($this->base_url."/index"));

				/*$data = [
					'status'=>'1',
					'code'=>'',
				];*/
				
			}

			// $this->sendJSON($data);

		}
		else{
			$this->form();
		}
	}


	public function orderAction(){
		if($this->request->isPost()){

			$cates = $this->request->getPost('cates');
			if(is_array($cates)){
				$this->db->begin();
				try{
					foreach($cates as $seq=>$category_id){
						$seq = $seq+1;
						$Category = ICategory::findFirst($category_id);
						if(!$Category){
							throw new \Exception('分类不存在');
						}
						$Category->seq = $seq;
						if(!$Category->save()){
							throw new \Exception($Category->getErrorMsg());
						}

					}

					$this->db->commit();
					$this->jump($this->url->get($this->base_url."/index"));
				} catch(\Exception $e){
					$this->db->rollback();
					throw new \Exception("分类排序更新失败".$e->getMessage(), 1); 
				}
				
			}
		}
		else{
			$parent_id = $this->request->get('parent_id');
			$list = ICategory::find([
				'parent_id=:parent_id:',
				'bind'=>[
					'parent_id'=>$parent_id
				],
				'order'=>'seq ASC'
			]);

			$this->view->setVars([
				'list'=>$list,
			]);	

			$this->view->disable();
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),'order');
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);
		}
	}

	/**
	 * @aclDesc 删除
	 */
	public function deleteAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = ICategory::findFirst($id);
			// var_export($M);exit;
			if(!$M){
				throw new \Exception("分类不存在", 1);
				
			}
			if($M->delete()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->category_id,$M->category_name);
				$this->flashSession->success("成功删除了一个分类");
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

	/**
	 * @aclDesc 排序
	 */
	public function setOrderAction(){

		
	}

}