<?php

namespace Api\Controllers;

use Common\Libs\Func;
use Api\Components\ControllerBase;
use Common\Models\IFlashSale;

class FlashSaleController extends ControllerBase {

	public function listAction(){

		$shop_id = (int)$this->post['shop_id'];
		$shop_id = $shop_id ? $shop_id : 1;

		if(!$this->conf['enable_multi_shop']){
			$shop_id = 1;
		}

		$sales = IFlashSale::find([
			'shop_id=:shop_id: AND status<3 and end_time>:end_time:',
			'bind'=>[
				'shop_id'=>$shop_id,
				'end_time'=>date('Y-m-d H:i:s')
			],
			'order'=>'status DESC,start_time ASC,sale_id ASC'
		]);

		$list = [];

		if($sales){
			foreach ($sales as $Sale) {
				if($Sale){
					$list[] = [
						'sale_id'=>$Sale->sale_id,
						'start_time'=>$Sale->start_time,
						'end_time'=>$Sale->end_time,
						'goods'=>$this->getSaleSpus($Sale),
						'now_time'=>date('Y-m-d H:i:s'),
					];

				}
			}
		}		
		else{
			$list = null;
		}

		$this->sendJSON([
			'data'=>$list
		]);
	}

	public function currentAction(){

		$shop_id = (int)$this->post['shop_id'];
		$shop_id = $shop_id ? $shop_id : 1;

		$Sale = IFlashSale::findFirst([
			'shop_id=:shop_id: AND status=2',
			'bind'=>[
				'shop_id'=>$shop_id,
			],
			'order'=>'sale_id ASC'
		]);

		if($Sale){
			$data = [
				'sale_id'=>$Sale->sale_id,
				'start_time'=>$Sale->start_time,
				'end_time'=>$Sale->end_time,
				'goods'=>[],
				'now_time'=>date('Y-m-d H:i:s'),
			];

			$data['goods'] = $this->getSaleSpus($Sale);
		}
		else{
			$data = [];
		}

		$this->sendJSON([
			'data'=>$data
		]);
	}

	public function nextAction(){

		$shop_id = (int)$this->post['shop_id'];
		$shop_id = $shop_id ? $shop_id : 1;

		$Sale = IFlashSale::findFirst([
			'status=1',
			'order'=>'sale_id ASC'
		]);

		if($Sale){
			$data = [
				'sale_id'=>$Sale->sale_id,
				'start_time'=>$Sale->start_time,
				'end_time'=>$Sale->end_time,
				'goods'=>[],
				'now_time'=>date('Y-m-d H:i:s'),
			];

			$data['goods'] = $this->getSaleSpus($Sale);
		}
		else{
			$data = [];
		}

		$this->sendJSON([
			'data'=>$data
		]);
	}

	protected function getSaleSpus($Sale){
		$ret = [];
		if($Sale->spus){
			foreach ($Sale->spus as $Item) {
				if($Item->Spu->status>0){
					$ret[] = [
						'spu_id'=>$Item->Spu->spu_id,
						'spu_name'=>$Item->Spu->spu_name,
						'cover'=>Func::staticPath($Item->Spu->cover),
						'price'=>fmtMoney($Item->sale_price),
						'origin_price'=>fmtMoney($Item->Spu->origin_price)
					];
				}
				
			}
		}

		return $ret;
	}

}
