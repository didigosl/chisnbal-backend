<?php

namespace Api\Components;

use Common\Models\IBuyer;
use Common\Libs\Arr;

class ControllerBuyer extends ControllerBase
{

	public $Buyer = null;

	public function initialize(){

      	parent::initialize();
    }
    
	protected function auth(){
	
		$sign = $this->post['sign'];
		$token = $this->post['token'];

		if(empty($sign) or empty($token)){
			throw new \Exception('缺少身份参数');
		}

		$Buyer = IBuyer::findFirst([
			'token=:token:',
			'bind'=>['token'=>$token]
		]);
		if($Buyer && $Buyer->secret_key==$sign){			
			$this->Buyer = $Buyer;
			return true;
		}
		else{
			throw new \Exception('身份验证失败');
		}
     
	}

	public function checkOwner($buyer_id){
		if($this->Buyer->buyer_id != $buyer_id){
			throw new \Exception("非法操作", 2004);
			
		}
	}

}
