<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IShop;
use Common\Models\ISort;
use Common\Libs\Func;

class MyShopController extends ControllerAuth {

	public function createAction(){

		$data = [];
		$data['shop_name'] = $this->post['shop_name'];
		$data['sort_id'] = (int)$this->post['sort_id'];
		$data['intro'] = $this->post['intro'];
		$data['contact_man'] = $this->post['contact_man'];
		$data['tel'] = $this->post['tel'];
		$data['logo'] = $this->post['logo'];
		$data['user_id'] = $this->User->user_id;

		$check = $this->db->fetchColumn('SELECT count(1) FROM i_shop WHERE user_id=:user_id',['user_id'=>$this->User->user_id]);

		if($check){
			throw new \Exception("您已经开通了店铺，或者存在正在待审的店铺申请，请勿重复提交", 2001);
			
		}

		$Shop = new IShop;
		$Shop->assign($data);

		if(!$Shop->save()){
			throw new \Exception($Shop->getErrorMsg(), 2001);
			
		}

		$this->sendJSON([
			'data'=>[
				'shop_id'=>$Shop->shop_id,
			]
		]);
	}

}
