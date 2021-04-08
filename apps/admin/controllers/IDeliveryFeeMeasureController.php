<?php
namespace Admin\Controllers;

use Common\Models\IDeliveryFeeMeasure as IDeliveryFee;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 运费
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class IDeliveryFeeMeasureController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '运费';
		$this->view->setVar('controller_name',$this->controller_name);
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
		$M = IDeliveryFee::findFirst($id);
		if(!$M){
            $M = new IDeliveryFee;
            
            $M->basic_measure = 1;
            $M->step_measure = 1;
		}

		$this->view->setVars([
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
			
			$id = $this->request->getPost('id','int');
			$data['area_id'] = $this->request->getPost('area_id','int');
			$data['basic_fee'] = $this->request->getPost('basic_fee');
            $data['basic_fee'] = fmtPrice($data['basic_fee']);
            $data['step_fee'] = $this->request->getPost('step_fee');
            $data['step_fee'] = fmtPrice($data['step_fee']);
			$data['basic_measure'] = $this->request->getPost('basic_measure');
			$data['basic_measure'] = fmtPrice($data['basic_measure']);
			$data['step_measure'] = $this->request->getPost('step_measure');
			$data['step_measure'] = fmtPrice($data['step_measure']);           
			
			// var_dump($data);exit;

            $shop_id = (int)$this->auth->getShopId();
            $data['shop_id'] = $shop_id;

			if($id){
				$Model = IDeliveryFee::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}

				if($Model->shop_id != $shop_id){
					throw new \Exception('操作非法',1);
				}
			}
			else{

				$Model = new IDeliveryFee;
			}

			$this->db->begin();

			try{
				$Model->assign($data);

				if($Model->save()){

					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->id,$Model->area_id ? $Model->Area->getFullName():'全局运费');
					$this->flashSession->success("数据提交成功");

					$this->db->commit();

					if($this->request->isAjax()){
						$this->view->disable();
						$this->sendJSON([
							'status'=>'1',
						]);
					}
					else{
						$this->jump();
					}

					// $this->jump($this->url->get($this->base_url."/setting"));
				}
				else{
					$this->db->rollback();
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
			$M = IDeliveryFee::findFirst($id);
			
			if($M->delete()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->id,$M->Area->getFullName());
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