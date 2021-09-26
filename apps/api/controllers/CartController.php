<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\ICart;
use Common\Models\IGoodsSku as Sku;
use Common\Models\IUserLevel;
use Common\Libs\Func;
use Common\Components\ValidateMsg;

class CartController extends ControllerAuth {

	public function listAction(){

		$carts = ICart::find([
			'user_id=:user_id:',
			'bind'=>['user_id'=>$this->User->user_id],
			'order'=>'create_time DESC'
		]);

		$list = [];
		$total_amount = 0;
		$total_rebate = 0;

		if($carts){
			foreach ($carts as $Cart) {
                if($Cart->Spu->status>0){
                    $rebates = $Cart->Spu->getFmtRebates(true,false);
                    /*foreach ($vips as $k=>$vip) {
                        $vips[$k]['rebate'] = fmtMoney(($rebates[$vip['level_id']] ? $rebates[$vip['level_id']]['rebate'] : 0)*100 * $Cart->num);
                    }*/

                    $rebate = $rebates[$this->User->level_id] ? $rebates[$this->User->level_id]['rebate'] : 0;
                    $list[] = [
                        'cart_id'=>$Cart->cart_id,
                        'spu_id'=>$Cart->spu_id,
                        'spu_name'=>$Cart->Spu->spu_name,
                        'sku_id'=>$Cart->sku_id,
                        'spec_info'=>$Cart->Sku->spec_info,
                        'cover'=>Func::staticPath($Cart->Spu->cover),
                        'price'=>fmtMoney($Cart->price),
                        'rebate'=> fmtMoney($rebate),
                        'num'=>$Cart->num,
                        'unit'=>$Cart->Spu->unit,
                        'stock'=>$Cart->Sku->stock,
                        'min_in_cart'=>$Cart->Spu->min_in_cart,
                        'min_to_buy'=>$Cart->Spu->min_to_buy,
                    ];

                    $total_amount += $Cart->num * $Cart->price;
                    $total_rebate += $Cart->num * $rebate;
                }
				
			}
        }
        
        $settings = settings();
		
		$this->sendJSON([
			'data'=>[
				'total_amount'=>fmtMoney($total_amount),
				'total_rebate'=>fmtMoney($total_rebate),
				'total'=>count($carts),
				'list'=>$list,
                //'vips'=>$vips
                'min_order_amount'=>$settings['min_order_amount'] ? fmtMoney(fmtPrice($settings['min_order_amount'])) : 0
                
			]
			
		]);

	}

	public function listWithShopAction(){

		$carts = ICart::find([
			'user_id=:user_id:',
			'bind'=>['user_id'=>$this->User->user_id],
			'order'=>'create_time DESC'
		]);

		$list = [];
		$tmp_list = [];
		$total_amount = 0;
		$total_rebate = 0;
		
		if($carts){
			foreach ($carts as $Cart) {
                if($Cart->Spu->status>0){
                    $rebates = $Cart->Spu->getFmtRebates(true,false);
                    $rebate = $rebates[$this->User->level_id] ? $rebates[$this->User->level_id]['rebate'] : 0;
                    
                    if(!isset($tmp_list[$Cart->shop_id])){
                        
                        $tmp_list[$Cart->shop_id] = [
                            'shop_id'=>$Cart->shop_id,
                            'shop_name'=>$Cart->Shop->shop_name,
                            'logo'=>Func::staticPath($Cart->Shop->logo),
                            'carts'=>[],
                        ];
                    }

                    $cart_data = [
                        'cart_id'=>$Cart->cart_id,
                        'spu_id'=>$Cart->spu_id,
                        'spu_name'=>$Cart->Spu->spu_name,
                        'spec_info'=>$Cart->Sku->spec_info,
                        'cover'=>Func::staticPath($Cart->Spu->cover),
                        'price'=>fmtMoney($Cart->price),
                        'rebate'=> fmtMoney($rebate),
                        'num'=>$Cart->num,
                        'unit'=>$Cart->Spu->unit,
                        'stock'=>$Cart->Sku->stock,
                        'min_in_cart'=>$Cart->Spu->min_in_cart,
                        'min_to_buy'=>$Cart->Spu->min_to_buy,
                    ];

                    $tmp_list[$Cart->shop_id]['carts'][] = $cart_data;

                    $total_amount += $Cart->num * $Cart->price;
                    $total_rebate += $Cart->num * $rebate;
                }
				
			}
		}

		foreach ($tmp_list as $v) {
			$list[] = $v;
		}

		unset($tmp_list);
		
		$this->sendJSON([
			'data'=>[
				'total_amount'=>fmtMoney($total_amount),
				'total_rebate'=>fmtMoney($total_rebate),
				'total'=>count($carts),
				'list'=>$list,
			]
			
		]);

	}


	public function addAction(){

        if(conf('enable_pending_user_permission') && $this->User->status==0){
            throw new \Exception('您还需通过商家审核');
        }
        $data_array = $this->post['data'];
        if(is_string($data_array)){
            $data_array = json_decode($data_array,JSON_UNESCAPED_UNICODE);
        }
        if(is_array($data_array)){
            $this->db->begin();
            foreach($data_array as $data){
                $Sku = Sku::findFirst($data['sku_id']);
                if(!$Sku){
                    throw new \Exception('商品信息不正确');
                }

                $messages  = ICart::validator(array_keys($data))->validate($data);
                ValidateMsg::run('Common\Models\ICart',$messages);

                if($Cart = ICart::add($this->User->user_id,$data['sku_id'],$data['num'])){

                    $total = $this->db->fetchColumn('SELECT count(1) FROM i_cart WHERE user_id=:user_id',['user_id'=>$this->User->user_id]);
                    $spu_total = $this->db->fetchColumn('SELECT count(1) FROM i_cart WHERE user_id=:user_id AND spu_id=:spu_id',['user_id'=>$this->User->user_id,'spu_id'=>$Cart->spu_id]);
                }
                else{
                    $this->db->rollback();
                    throw new \Exception("加入购物车失败", 1002);

                }
            }
            $this->db->commit();
            $this->sendJSON([
                'data'=>[
                    'total'=>$total,
                    'spu_total'=>$spu_total
                ],
            ]);
        }
        /*$data = [
			'sku_id'=>$this->post['sku_id'],
			'num'=>$this->post['num']
		];



		$Sku = Sku::findFirst($data['sku_id']);
		if(!$Sku){
			throw new \Exception('商品信息不正确');
		}
		
		$messages  = ICart::validator(array_keys($data))->validate($data);
		ValidateMsg::run('Common\Models\ICart',$messages);

		if($Cart = ICart::add($this->User->user_id,$data['sku_id'],$data['num'])){

            $total = $this->db->fetchColumn('SELECT count(1) FROM i_cart WHERE user_id=:user_id',['user_id'=>$this->User->user_id]);
            $spu_total = $this->db->fetchColumn('SELECT count(1) FROM i_cart WHERE user_id=:user_id AND spu_id=:spu_id',['user_id'=>$this->User->user_id,'spu_id'=>$Cart->spu_id]);
			$this->db->commit();
			$this->sendJSON([
				'data'=>[
                    'total'=>$total,
                    'spu_total'=>$spu_total
                ],
			]);
		}
		else{
			$this->db->rollback();
			throw new \Exception("加入购物车失败", 1002);
			
		}*/

    }
    
    public function updateAction(){

        if(conf('enable_pending_user_permission') && $this->User->status==0){
            throw new \Exception('您还需通过商家审核');
        }

		$data = [
			'sku_id'=>$this->post['sku_id'],
			'num'=>$this->post['num']
		];

		db()->begin();

		$Sku = Sku::findFirst($data['sku_id']);
		if(!$Sku){
			throw new \Exception('商品信息不正确');
		}
		
		$messages  = ICart::validator(array_keys($data))->validate($data);
        ValidateMsg::run('Common\Models\ICart',$messages);
        
        $Cart = ICart::findFirst([
            'user_id=:user_id: AND sku_id=:sku_id:',
            'bind'=>[
                'user_id'=>$this->User->user_id,
                'sku_id'=>$data['sku_id']
            ]
        ]);

        if(!$Cart){
            $Cart = ICart::add($this->User->user_id,$data['sku_id'],$data['num']);
        }
        else{
            $Cart->num = $data['num'];
            if(!$Cart->save()){
                throw new \Exception('购物车更新失败');
            }
        }

        db()->commit();

        //重新计算购物车汇总数据
        $carts = ICart::find([
			'user_id=:user_id:',
			'bind'=>['user_id'=>$this->User->user_id],
			'order'=>'create_time DESC'
		]);

		$total_amount = 0;
		$total_rebate = 0;

		if($carts){
			foreach ($carts as $Cart) {
                if($Cart->Spu->status>0){
                    $rebates = $Cart->Spu->getFmtRebates(true,false);
 
                    $rebate = $rebates[$this->User->level_id] ? $rebates[$this->User->level_id]['rebate'] : 0;
                  
                    $total_amount += $Cart->num * $Cart->price;
                    $total_rebate += $Cart->num * $rebate;
                }
				
			}
        }

        $this->sendJSON([
            'data'=>[
                'total_amount'=>fmtMoney($total_amount),
                'total_rebate'=>fmtMoney($total_rebate),
                'total'=>count($carts),
                'price'=>fmtMoney($Cart->price),
                'amount'=>fmtMoney($Cart->amount),
            ],
        ]);

	}

	public function renewRabateAction(){

		$levels = IUserLevel::find(['level_id>1']);
		$vips = [];
		foreach ($levels as $Level) {
			$vips[] = [
				'level_id'=>$Level->level_id,
				'level_name'=>$Level->level_name,
				'rebate'=>0
			];
		}
		unset($levels);

		//data = [{cart_id:1,num:1}]
		$data = $this->post['data'];
		if(is_string($data)){
			$data = json_decode($data,JSON_UNESCAPED_UNICODE);
		}
		
		if(empty($data) or !is_array($data)){
			$vips = null;
		}
		else{
			$this->db->begin();
			try{
				foreach($data as $item){
					$Cart = ICart::findFirst($item['cart_id']);
					if(!$Cart){
						throw new \Exception("购物车信息不存在", 2002);		
					}

					if($Cart->num != $item['num']){
						$Cart->num = (int)$item['num'];
						if(!$Cart->save()){
							throw new \Exception("更新购物车失败", 1002);
							
						}
					}

					$rebates = $Cart->Spu->getFmtRebates(true);
					foreach ($vips as $k=>$vip) {
						// $vips[$k]['rebate'] = fmtMoney(($rebates[$vip['level_id']] ? $rebates[$vip['level_id']]['rebate'] : 0)*100 * $Cart->num);
						$vips[$k]['rebate'] += fmtPrice(($rebates[$vip['level_id']] ? $rebates[$vip['level_id']]['rebate'] : 0)) * $Cart->num;
					}
					
				}

				foreach($vips as $k=>$v){
					$vips[$k]['rebate'] = fmtMoney($v['rebate']);
				}
				$this->db->commit();
			} catch (\Exception $e){
				$this->db->rollback();
				throw new \Exception($e->getMessage(), $e->getCode());
				
			}
			
		}
		

		$this->sendJSON([
			'data'=>[
				'vips'=>$vips
			]
			
		]);
	}


	public function renewAction(){

		$data = $this->post['data'];
		if(is_string($data)){
			$data = json_decode($data,JSON_UNESCAPED_UNICODE);
		}		

		if(is_array($data)){
			$user_id = $this->User->user_id;
			$this->db->begin();
			if(ICart::renew($user_id,$data)){
				$total = $this->db->fetchColumn('SELECT count(1) FROM i_cart WHERE user_id=:user_id',['user_id'=>$this->User->user_id]);
				$this->db->commit();
				$this->sendJSON([
					'data'=>[
						'total'=>$total,
					]
					
				]);;
			}
			else{
				$this->db->rollback();
				throw new \Exception("更新购物车失败", 1002);
			}
		}
		
	}

}
