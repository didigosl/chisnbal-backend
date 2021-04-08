<?php
namespace Admin\Controllers;

use Common\Models\IOrderRemark;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 订单备注
 * @acl shopadmin
 */
class IOrderRemarkController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '订单备注';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 列表
	 * @aclCustom single_shop,multi_shop
	 */
	public function listAction(){
		$this->view->disable();
		$order_id = $this->request->getQuery('order_id');
		// $list = $this->db->fetchAll('SELECT * FROM i_order_remark WHERE order_id=:order_id ORDER BY order_remark_id DESC',\Phalcon\Db::FETCH_ASSOC,['order_id'=>$order_id]);

		$remarks = IOrderRemark::find([
			'order_id=:order_id:',
			'bind'=>['order_id'=>$order_id],
			'order'=>'order_remark_id DESC'
		]);

		$list = [];
		foreach ($remarks as $Remark) {
			$list[] = [
				'order_remark_id'=>$Remark->order_remark_id,
				'content'=>$Remark->content,
				'admin_username'=>$Remark->admin_id ? $Remark->Admin->username : '',
				'create_time'=>$Remark->create_time
			];
		}

		$this->sendJSON([
			'status'=>1,
			'data'=>[
				'list'=>$list
			]
		]);
	}

	/**
	 * @aclDesc 新增
	 * @aclCustom single_shop,multi_shop
	 */
	public function createAction(){
		$this->modify();
	}

	protected function modify(){
		$this->view->disable();
		if($this->request->isPost()){
			
			$id = $this->request->getPost('order_remark_id','int');
			$data['order_id'] = $this->request->getPost('order_id','int');
			$data['content'] = $this->request->getPost('content');

			if($id){
				$Model = IOrderRemark::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new IOrderRemark;
			}


			$Model->assign($data);

			if($Model->save()===false){

				throw new \Exception($Model->getErrorMsg(), 1);
			}
			else{

				SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->order_remark_id,'订单号'.$Model->Order->sn);
				$data = [
					'status'=>'1',
					'code'=>'',
				];
				
			}

			$this->sendJSON($data);

		}
		else{
			$this->form();
		}
	}

	/**
	 * @aclDesc 删除
	 * @aclCustom single_shop,multi_shop
	 */
	public function deleteAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IOrderRemark::findFirst($id);
			
			if($M->delete()){

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->order_remark_id,'订单号'.$M->Order->sn);
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