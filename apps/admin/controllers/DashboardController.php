<?php
namespace Admin\Controllers;

use Admin\Components\ControllerAuth;
use Phalcon\Mvc\Model\Query;

/**
 * @acl *
 * @aclCustom false
 */
class DashboardController extends ControllerAuth{

	public function initialize(){
		parent::initialize();		
		$this->view->setVar('controller_name','管理系统');
	}

	public function indexAction() {

		$data = [];
		$shop_id  = $this->auth->getShopId();
		if($shop_id){
			$data['spu_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_goods_spu WHERE shop_id=:shop_id AND remove_flag=0',['shop_id'=>$shop_id]);
			$data['order_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_order WHERE shop_id=:shop_id',['shop_id'=>$shop_id]);
			$data['draw_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_draw WHERE status=1');
			$data['flashsale_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_flash_sale WHERE shop_id=:shop_id AND status=2',['shop_id'=>$shop_id]);
		}
		else{
			$data['user_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_user WHERE remove_flag=0');
			$data['spu_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_goods_spu WHERE remove_flag=0');
			$data['order_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_order WHERE flag>1 AND shop_id>0 AND close_flag=0');
			$data['draw_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_draw WHERE status=1');
			$data['flashsale_total'] = $this->db->fetchColumn('SELECT count(1) FROM i_flash_sale WHERE status=2');
		}
		
		$this->view->setVars(array_merge($data,[
			'action_name'=>'数据'
			]));

	}


	public function errorAction(){

		$msg = $this->request->getQuery('msg');
		if($msg){
			$this->view->setVar('msg',$msg);
		}
		$this->view->setVars($this->dispatcher->getParams());
	}

}