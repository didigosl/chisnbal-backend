<?php
namespace Admin\Controllers;

use Common\Models\IFlashSale;
use Common\Models\IFlashSaleSpu as SaleSpu;
use Common\Models\IGoodsSpu;
use Common\Models\SAdminLog;
use Common\Components\ValidateMsg;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 抢购
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class IFlashSaleController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '限时抢购';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 列表
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$status = $this->request->getQuery('status');

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditions[] = 'shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		if($status){
			$conditions[] = 'status=:status:';
			$params['status'] = $status;
		}

		if($id){
			$conditions[] = 'sale_id=:id:';
			$params['sale_id'] = $id;
		}

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IFlashSale')
                ->where($conditionSql,$params)
                ->orderBy(IFlashSale::getPkCol().' DESC');
		

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
				'status'=>$status
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

		$M = IFlashSale::findFirst($id);
		if(!$M){
			$M = new IFlashSale;
			if($cached_data){
				$M->assign($cached_data);
			}
			
		}

		$spus = [];

		if($M->spus){
			foreach($M->spus as $SaleSpu){
				$spus[] = [
					'spu_id'=>$SaleSpu->spu_id,
					'sale_price'=>fmtMoney($SaleSpu->sale_price),
					'sale_stock'=>$SaleSpu->sale_stock,
					'per_limit'=>$SaleSpu->per_limit,
					'spu_name'=>$SaleSpu->Spu->spu_name,
					'cover'=>$SaleSpu->Spu->cover,
					'price'=>fmtMoney($SaleSpu->Spu->price),
					'stock'=>$SaleSpu->Spu->stock,
				];
			}

		}


		$this->view->setVars([
			'M'=>$M,
			'spus'=>json_encode($spus,JSON_UNESCAPED_UNICODE),
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
			$this->cacheFormData();
			$id = $this->request->getPost('sale_id','int');
			$data['sale_name'] = $this->request->getPost('sale_name');
			$data['start_time'] = $this->request->getPost('start_time');
			$data['end_time'] = $this->request->getPost('end_time');

			$spu_id = $this->request->getPost('spu_id');
			$sale_price = $this->request->getPost('sale_price');
			$sale_stock = $this->request->getPost('sale_stock');
			$per_limit = $this->request->getPost('per_limit');

			$shop_id = $this->auth->getShopId();

			//检测当前时段是否已经安排了抢购活动
			$check = $this->db->fetchColumn('SELECT count(1) FROM i_flash_sale WHERE status<3 AND shop_id=:shop_id AND ((start_time<:start_time AND end_time>:start_time) OR (start_time<:end_time AND end_time>:end_time) OR (start_time<:start_time AND end_time>:end_time))',['shop_id'=>$shop_id,'start_time'=>$data['start_time'],'end_time'=>$data['end_time']]);

			if($check){
				throw new \Exception("指定时间段内已经安排了抢购活动，时间不可冲突", 1);
				
			}

			$spus = [];

			if(is_array($spu_id) && count($spu_id)){
				foreach ($spu_id as $k => $v) {
					$spus[$k]['spu_id'] = $v;
					$spus[$k]['sale_price'] = fmtPrice($sale_price[$k]);
					$spus[$k]['sale_stock'] = $sale_stock[$k];
					$spus[$k]['per_limit'] = $per_limit[$k];

					$messages  = SaleSpu::validator(['spu_id','sale_price','sale_stock'])->validate($spus[$k]);
					ValidateMsg::run('Common\Models\IFlashSaleSpu',$messages);
					
				}
			}
			else{
				throw new \Exception("必须添加抢购商品", 2001);
				
			}
			if($id){
				$Model = IFlashSale::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new IFlashSale;

			}

		
			$this->db->begin();
			try{
				$Model->assign($data);

				if($Model->save()){

					$Model->updateSpus($spus);
					$this->db->commit();

					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->sale_id,$Model->sale_name);
					$this->clearCachedFormData();
					$this->flashSession->success("数据提交成功");
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
			$M = IFlashSale::findFirst($id);
			
			if($M->remove()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->sale_id,$M->sale_name);				
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
	 * @aclDesc 结束
	 * @return [type] [description]
	 */
	public function finishAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IFlashSale::findFirst($id);
			if(!$M){
				throw new \Exception("限时抢购不存在", 1);
				
			}
			
			if($M->finish()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$Model->sale_id,$Model->sale_name);
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
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