<?php
namespace W\Controllers;
use \Common\Components\Assets;
use W\Components\ControllerBase;
use Common\Models\IOrder;

class PayController extends ControllerBase {

	public function successAction(){

		$order_id = $this->request->get('order_id');
		$Order = IOrder::findFirst($order_id);

		if($Order){
			if($Order==1){
				$Order->flag = 2;
				$Order->save();
			}
		}
		
	}

	public function failAction(){

		$order_id = $this->request->get('order_id');
		$Order = IOrder::findFirst($order_id);

		file_put_contents(SITE_PATH.'/runtime/logs/pay_redirect_fail.txt',$_REQUEST);
		
	}

}