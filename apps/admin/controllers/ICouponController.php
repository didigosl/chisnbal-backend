<?php
namespace Admin\Controllers;

use Common\Models\ICoupon;
use Common\Models\ICategory;
use Common\Models\IUser;
use Common\Models\SAdminLog;
use Common\Libs\Func;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 代金券
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class ICouponController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '代金券';
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

		if($id){
			$conditions[] = 'coupon_id=:id:';
			$params['coupon_id'] = $id;
		}

		$conditions[] = 'shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\ICoupon')
                ->where($conditionSql,$params)
                ->orderBy(ICoupon::getPkCol().' DESC');

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
		$M = ICoupon::findFirst($id);
		if(!$M){
			$M = new ICoupon;		

			if($cached_data){
				$M->assign($cached_data);
			}

			$M->sn = Func::makeNum();
		}

		if($M->link_type == 'category'){
			$linkCategory = ICategory::findFirst($M->link_id);
			$link_select_default = $linkCategory ? trim($linkCategory->merger.$linkCategory->category_id,',') : '';
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

		$this->view->setVars([
			'M'=>$M,
			'link_select_default'=>$link_select_default,
			'link_spu'=>$link_spu
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
			// $this->view->disable();
			$id = $this->request->getPost('coupon_id','int');
			$data['coupon_name'] = $this->request->getPost('coupon_name');
			$data['sn'] = $this->request->getPost('sn');
			$data['amount'] = $this->request->getPost('amount');
			$data['start_time'] = $this->request->getPost('start_time');
			$data['end_time'] = $this->request->getPost('end_time');
			$data['min_limit'] = $this->request->getPost('min_limit');
			$with_rebate = $this->request->getPost('with_rebate');
			$data['with_rebate'] = $with_rebate ? 1 : 0;			
			$with_discount = $this->request->getPost('with_discount');
			$data['with_discount'] = $with_discount ? 1 : 0;
			$level = $this->request->getPost('level');
			$special_users = $this->request->getPost('special_users');
			$user = $this->request->getPost('user');
			// var_dump($_POST);EXIT;
			if(empty($level) && empty($user)){
				throw new \Exception("必须指定适用人群", 1);
				
			}

			$data['amount'] = fmtPrice($data['amount']);
			$data['min_limit'] = fmtPrice($data['min_limit']);

			$target = [];
			if($special_users){
				$target['type'] = 'user';
				$target['list'] = $user;
			}
			else{
				$target['type'] = 'level';
				$target['list'] = $level;
			}

			$data['target'] = json_encode($target);

			if($id){
				$Model = ICoupon::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new ICoupon;
			}

			try{
				$Model->assign($data);

				$this->db->begin();
				if($Model->save()){

					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->coupon_id,$Model->coupon_name);
					$this->db->commit();
					$this->flashSession->success("数据提交成功");
					$this->clearCachedFormData();
					$this->jump($this->url->get($this->base_url."/index"));
				}
				else{
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e){
				$this->db->rollback();
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
			$M = ICoupon::findFirst($id);
			
			if($M->delete()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->coupon_id,$M->coupon_name);
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
	 * @aclDesc 查看
	 */
	public function viewAction(){
		$id = $this->request->getQuery('id','int');

		$M = ICoupon::findFirst($id);
		if(!$M){
			$M = new ICoupon;
		}
		
		$this->view->setVars([
			'M'=>$M
			]);		
		
		if($this->request->isAjax()){	

			$this->view->disable();
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),'view');
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);
		}
	}

}