<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IDraw;
use Common\Libs\Func;

class DrawController extends ControllerAuth {

	public function createAction(){

		$amount = $this->post['amount'];
		$amount = fmtPrice($amount);

		$this->User->refresh();

		if($this->User->money < $amount){
			throw new \Exception("提现金额超出了您的账户余额", 2001);
			
		}

		$check = $this->db->fetchColumn("SELECT count(1) FROM i_draw WHERE user_id=:user_id AND status=1",['user_id'=>$this->User->user_id]);

		if($check){
			throw new \Exception("您有一条提现申请正在处理中，不可发起新的提现请求", 1);
			
		}

		$Draw = new IDraw;
		$Draw->assign([
			'amount'=>$amount,
			'user_id'=>$this->User->user_id,
		]);

		if(!$Draw->save()){
			throw new \Exception($Draw->getErrorMsg(), 1001);
			
		}

		$this->sendJSON([
			'data'=>[
				'draw_id'=>$Draw->draw_id,
			]
		]);
	}
	
}
