<?php
namespace Admin\Controllers;

use Common\Models\IShop;
use Admin\Components\FileSys;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 店铺
 * @aclCustom super,single_shop,multi_shop
 */
class IShopController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '店铺';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 列表
	 * @acl superadmin
	 * @aclCustom super
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$status = $this->request->getQuery('status');
		$shop_name = trim($this->request->getQuery('shop_name'));
		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		if($shop_name){
			$conditions[] = 's.shop_name like :shop_name:';
			$params['shop_name'] = '%'.$shop_name.'%';
		}

		if($user_id){
			$conditions[] = 's.user_id=:user_id:';
			$params['user_id'] = $user_id;
		}

		if($status){
			$conditions[] = 's.status=:status:';
			$params['status'] = $status;
		}

		if($id){
			$conditions[] = 'd.shop_id=:id:';
			$params['id'] = $id;
		}

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns(['s.*,u.name,u.phone'])
                ->from(['s'=>'Common\Models\IShop'])
                ->leftJoin('Common\Models\IUser','s.user_id=u.user_id','u')
                ->where($conditionSql,$params)
                ->orderBy(IShop::getPkCol().' ASC');

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
			'controller_name'=>$this->controller_name,
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'status'=>$status,
				'user_id'=>$user_id,
				'shop_name'=>$shop_name,
			],
		]);

	}

	/**
	 * @aclDesc 审核
	 * @acl superadmin
	 * @aclCustom super
	 */
	public function checkAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$act = $this->request->getQuery('act');
			$M = IShop::findFirst($id);
			if(!$M){
				throw new \Exception("用户不存在", 1);				
			}


			$this->db->begin();
			try{
				if($act=='pass'){
					$res = $M->checkPass();
					$msg = ' 店铺审核通过';
				}
				elseif($act=='refuse'){
					$res = $M->checkRefuse();

					$msg = '店铺申请被拒绝';
				}
				if($res){
					$this->flashSession->success($msg);
					$this->db->commit();

					$data = [
						'status'=>'1',
						'code'=>'',
						'msg'=>$msg
					];
				}
				else{
					$this->db->rollback();
					
				}
			} catch (\Exception $e){
				$data = [
						'status'=>'0',
						'code'=>$e->getCode(),
						'msg'=>$e->getMessage(),
					];
				
			}
			
			$this->sendJSON($data);

		}
	}

	/**
	 * @aclDesc 查看
	 * @acl superadmin
	 * @aclCustom super
	 */
	public function viewAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IShop::findFirst($id);
						
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$this->view->setVars([
				'M'=>$M
				]);
			
			$html = $this->view->getRender($this->dispatcher->getControllerName(),$this->dispatcher->getActionName());

			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);

		}

	}

	/**
	 * @aclDesc 修改
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function updateAction(){
		$this->modify();
	}

	protected function form(){

		if($this->flashSession->has('error')){
			$cached_data = $this->getCachedFormData();
		}

		$id = $this->request->getQuery('id','int');

		if($this->auth->getShopId()){
			$id = $this->auth->getShopId();
		}

		$M = IShop::findFirst($id);
		if(!$M){
			$M = new IShop;
			if($cached_data){
				$M->assign($cached_data);
			}
		}

		if($M->sort_id){
			$def_sort = $M->Sort ? trim($M->Sort->merger.$M->sort_id,',') : '';
		}
		else{
			$def_sort = '';
		}

		$this->view->setVars([
			'def_sort'=>$def_sort,
			'M'=>$M,
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
			$id = $this->request->getPost('shop_id','int');

			if($this->auth->getShopId()){
				$id = $this->auth->getShopId();
			}

			$data['shop_name'] = $this->request->getPost('shop_name');
			$data['tel'] = $this->request->getPost('tel');
			$data['email'] = $this->request->getPost('email');
            $data['address'] = $this->request->getPost('address');
            $data['lon'] = $this->request->getPost('lon');
            $data['lan'] = $this->request->getPost('lan');
			$data['intro'] = $this->request->getPost('intro');
            $data['sort_id'] = $this->request->getPost('sort_id','int');
            $data['postcode'] = $this->request->getPost('postcode','string');

			$upload_dir = 'shop'.$this->auth->getShopId().'/image';
			$logo = FileSys::upload($upload_dir, ['logo']);
			if ($logo) {
				$data['logo'] = $logo;
			}

			$bg = FileSys::upload($upload_dir, ['bg']);
			if ($bg) {
				$data['bg'] = $bg;
			}

			if($id){
				$Model = IShop::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}

			}
			else{

				$Model = new IShop;
			}

			try{

				$Model->assign($data);

				if($Model->save()){
					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->shop_id,$Model->shop_name);
					$this->flashSession->success("数据提交成功");
					$this->clearCachedFormData();
					$this->jump();
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

}