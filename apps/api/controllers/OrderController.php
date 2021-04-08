<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IOrder;
use Common\Models\IGoodsSpu as Spu;
use Common\Models\IGoodsSku as Sku;
use Common\Models\ICart;
use Common\Models\IOrderComment;
use Common\Libs\Func;
use Common\Components\Braintree;
use Common\Components\ValidateMsg;
use Common\Components\RedSysPay;
use Common\Components\WechatPay;
use Omnipay\Omnipay;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class OrderController extends ControllerAuth
{

	/**
	 * 订单数量统计
	 */
	public function statAction()
	{
		$data = [];
		foreach (IOrder::getFlagContext() as $k => $v) {
			$data['flag' . $k] = [
				'name' => $v,
				'total' => 0
			];
			$data['flag' . $k]['total'] = Iorder::count([
				'user_id=:user_id: AND flag=:flag:',
				'bind' => [
					'user_id' => $this->User->user_id,
					'flag' => $k
				]
			]);
		}

		$data['refound'] = [];
		$data['refound']['name'] = '退款';
		$data['refound']['total'] = Iorder::count([
			'user_id=:user_id: AND refound_flag>0',
			'bind' => [
				'user_id' => $this->User->user_id,
			]
		]);

		$data['all'] = [];
		$data['all']['name'] = '全部';
		$data['all']['total'] = Iorder::count([
			'user_id=:user_id: AND flag>0',
			'bind' => [
				'user_id' => $this->User->user_id,
			]
		]);

		$this->sendJSON([
			'data' => $data
		]);
	}

	public function listAction()
	{
		$flag = $this->post['flag'];

		$conditions = [];
		$params = [];

		if ($this->post['flag']) {
			$conditions[] = 'flag=:flag:';
			$params['flag'] = $this->post['flag'];
		}

		if ($this->post['refound_flag'] == 'refound') {
			$conditions[] = 'refound_flag>0';
		}

		if ($this->post['shop_id']) {
			$conditions[] = 'shop_id=:shop_id:';
			$params['shop_id'] = $this->post['shop_id'];
		}

		$conditions[] = 'user_id=:user_id:';
		$params['user_id'] = $this->User->user_id;

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$limit = $this->post['page_limit'] ? (int)$this->post['page_limit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$order = 'order_id DESC';

		$builder = $this->modelsManager->createBuilder()
			->from('Common\Models\IOrder')
			->where($conditionSql, $params)
			->orderBy($order);

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => $limit,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		$paginate = $paginator->getPaginate();
		$list = [];

		if ($paginate->items) {
			foreach ($paginate->items as $k => $item) {

				$list[$k] = [
					'order_id' => $item->order_id,
					'sn' => $item->sn,
					'total_amount' => fmtMoney($item->total_amount),
					'total_rebate' => fmtMoney($item->total_rebate),
					'total_discount' => fmtMoney($item->total_discount),
					'total_coupon' => fmtMoney($item->total_coupon),
					'express_fee' => fmtMoney($item->express_fee),
					'delivery_time' => $item->delivery_time,
					'total_fee' => fmtMoney($item->total_fee),
					'receive_area' => $item->receive_area,
					'receive_city_name' => $item->receive_city_name,
					'receive_man' => $item->receive_man,
					'receive_phone' => $item->receive_phone,
					'receive_address' => $item->receive_address,
					'receive_postcode' => $item->receive_postcode,
					'create_time' => $item->create_time,
					'flag' => $item->flag,
                    'flag_text' => $item->getFlagContext($item->flag),
                    'payment_method'=>$item->payment_method,
                    'payment_method_text'=> $item->payment_method ? $item->getPaymentMethodContext($item->payment_method) : '',
				];

				if ($item->refound_flag) {
					$list[$k]['refound_flag'] = $item->refound_flag;
					$list[$k]['refound_flag_text'] = $item->getRefoundFlagContext($item->refound_flag);
				}

				if ($item->skus) {
					$list[$k]['goods'] = [];
					foreach ($item->skus as $OrderSku) {
						$list[$k]['goods'][] = [
							'spu_id' => $OrderSku->spu_id,
							'sku_id' => $OrderSku->sku_id,
							'cover' => Func::staticPath($OrderSku->Spu->cover),
							'spu_name' => $OrderSku->Spu->spu_name,
							'spec_info' => $OrderSku->spec_info,
							'amount' => fmtMoney($OrderSku->price),
                            'num' => $OrderSku->num,
                            'unit'=>$OrderSku->Spu->unit
						];
					}
				}

			}
		}

		$this->sendJSON([
			'data' => [
				'total_pages' => $paginate->total_pages,
				'page_limit' => $limit,
				'page' => $page,
				'list' => $list,
			]
		]);

	}

	public function prepareAction()
	{

		$carts = $this->post['carts'];
		if (is_string($carts)) {
			$carts = json_decode($carts, JSON_UNESCAPED_UNICODE);
		}
		$data['address_id'] = $this->post['address_id'];
		$data['coupon_user_id'] = $this->post['coupon_user_id'];

		IOrder::prepare($carts, $data, true);

		$data['total_fee'] = fmtMoney($data['total_fee']);
		$data['total_amount'] = fmtMoney($data['total_amount']);
		$data['express_fee'] = fmtMoney($data['express_fee']);
		$data['total_discount'] = fmtMoney($data['total_discount']);
		$data['total_rebate'] = fmtMoney($data['total_rebate']);
		$data['all_rebate'] = fmtMoney($data['all_rebate']);

		$this->sendJSON([
			'data' => $data,
		]);

	}

	/**
	 * 获取订单详情
	 * @return [type] [description]
	 */
	public function getAction()
	{
		$order_id = $this->post['order_id'];
		$Order = IOrder::findFirst($order_id);

		if (!$Order) {
			throw new \Exception('订单不存在', 303001);

		}

		$this->checkOwner($Order->user_id);

		$data = [
			'order_id' => $Order->order_id,
			'sn' => $Order->sn,
			'total_rebate' => fmtMoney($Order->total_rebate),
			'total_discount' => fmtMoney($Order->total_discount),
			'total_coupon' => fmtMoney($Order->total_coupon),
			'express_fee' => fmtMoney($Order->express_fee),
			'total_amount' => fmtMoney($Order->total_amount),
			'receive_area' => $Order->receive_area,
			'receive_city_name' => $Order->receive_city_name,
			'receive_man' => $Order->receive_man,
			'receive_phone' => $Order->receive_phone,
			'receive_address' => $Order->receive_address,
			'receive_postcode' => $Order->receive_postcode,
			'express_corp_name' => $Order->ExpressCorp->corp_name,
			'express_no' => $Order->express_no,
			'flag' => $Order->flag,
            'flag_text' => $Order->getFlagContext($Order->flag),
            'payment_method'=>$Order->payment_method,
            'payment_method_text'=> $Order->payment_method ? $Order->getPaymentMethodContext($Order->payment_method) : '',
			'refound_flag' => $Order->refound_flag,
			'refound_flag_text' => $Order->getRefoundFlagContext($Order->refound_flag),
			'close_flag' => $Order->close_flag,
			'close_flag_text' => $Order->getCloseFlagContext($Order->close_flag),
			'create_time' => $Order->create_time,
			'pay_time' => $Order->pay_time,
			'delivery_time' => $Order->delivery_time,
			'finish_time' => $Order->finish_time,
		];

		if ($Order->pay_flag) {
			$data['paymethod_text'] = $Order->payment_method ? $Order->getPaymentMethodContext($Order->payment_method) : '';
			$data['transaction_id'] = $Order->transaction_id;
		}

		foreach ($Order->skus as $OrderSku) {
			$data['goods'][] = [
				'spu_id' => $OrderSku->spu_id,
				'sku_id' => $OrderSku->sku_id,
				'cover' => Func::staticPath($OrderSku->Spu->cover),
				'spu_name' => $OrderSku->Spu->spu_name,
				'spec_info' => $OrderSku->spec_info,
				'amount' => fmtMoney($OrderSku->price),
                'num' => $OrderSku->num,
                'unit'=>$OrderSku->Spu->unit
			];
		}

		$data['has_comment'] = $Order->Comment ? 1 : 0;

		if ($data['has_comment']) {
			$data['comment'] = [
				'star' => $Order->Comment->star,
				'content' => $Order->Comment->content,
				'create_time' => $Order->Comment->create_time
			];
		}


		$this->sendJSON([
			'data' => $data
		]);
	}

	public function buyNowAction()
	{

		$return_fmt = $this->post['return_fmt'];
		$return_fmt = $return_fmt ? $return_fmt : 'simple';
		$sku_id = $this->post['sku_id'];
		$num = $this->post['num'];
		$num = $num ? $num : 1;

		$cart_data = [
			'sku_id' => $sku_id,
			'num' => $num
		];

		$messages = ICart::validator(array_keys($cart_data))->validate($cart_data);
		ValidateMsg::run('Common\Models\ICart', $messages);

		$Cart = ICart::add($this->User->user_id, $cart_data['sku_id'], $cart_data['num'], false);

		if ($Cart) {

			if ($return_fmt == 'full') {
				$carts = [];
				$carts[] = $Cart->cart_id;
				// var_dump($carts);exit;
				IOrder::prepare($carts, $data, true);

				$data['total_fee'] = fmtMoney($data['total_fee']);
				$data['total_amount'] = fmtMoney($data['total_amount']);
				$data['express_fee'] = fmtMoney($data['express_fee']);
				$data['total_discount'] = fmtMoney($data['total_discount']);
				$data['total_rebate'] = fmtMoney($data['total_rebate']);
				$data['carts'][] = $Cart->cart_id;
				unset($data['shop_data']);

				$this->sendJSON([
					'data' => $data,
				]);
			} else {
				$this->sendJSON([
					'data' => $Cart->cart_id,
				]);
			}


		} else {
			throw new \Exception("创建购物车失败", 1);

		}
	}

	/**
	 * 创建订单
	 * @return [type] [description]
	 */
	public function createAction()
	{
		$carts = json_decode($this->post['carts']);
		$data['coupon_user_id'] = $this->post['coupon_user_id'];
        $data['address_id'] = $this->post['address_id'];
        $data['remark'] = $this->post['user_remark'];
		//来自哪个用户的分享
		$share_from_token = $this->post['share_from_token'];

		if(!is_array($carts)){
			throw new \Exception('carts参数错误',2001);
		}

		$conf = conf();

		if ($conf['affiliate_type'] == 'sale') {
			$share_from_user_id = db()->fetchColumn("SELECT user_id FROM i_user WHERE token=:token", ['token' => $share_from_token]);
			$data['share_from_user_id'] = (int)$share_from_user_id;
        }
        
        //单店铺，并且开启了收货区域邮编限制
        if(!$conf['enable_multi_shop'] && !empty($conf['enable_postcode_limit']) && !empty($data['address_id']) ){
            $address_postcode = db()->fetchColumn("SELECT postcode from i_address WHERE address_id=:address_id",['address_id'=>$data['address_id']]);
            $shop_postcode = db()->fetchColumn("SELECT postcode FROM i_shop WHERE shop_id=1");
            $shop_postcode_arr = explode(',',trim(trim($shop_postcode),','));
            array_walk($shop_postcode_arr,'trim');
            if(!in_array($address_postcode,$shop_postcode_arr)){
                // throw new \Exception('抱歉，您的收货区域不在店铺的发货范围内',2001);
                throw new \Exception('Código postal fuera de área de entrega',2001);
            }
        }

		db()->begin();
		//是否开启未确认订单的修改功能
		if($conf['enabel_order_modify']){
			//是否有未确认的订单
			$UnconfirmedOrder = IOrder::findFirst([
				'user_id=:user_id: AND flag=1',
				'bind'=>[
					'user_id'=>$this->User->user_id
				]
			]);

			if($UnconfirmedOrder){
				//将订单商品生成临时购物车数据
				if($UnconfirmedOrder->skus){
					foreach($UnconfirmedOrder->skus as $OrderSku){
						$TmpCart = ICart::add($this->User->user_id,$OrderSku->sku_id,$OrderSku->num);
						if($TmpCart){
							$carts[] = $TmpCart->cart_id;
						}
					}
				}
			}
		}

		$carts = array_unique($carts);

		try {
			if($UnconfirmedOrder){
				$Order = IOrder::renew($UnconfirmedOrder->order_id,$carts, $data);
			}
			else{
				$Order = IOrder::add($carts, $data);
			}
			if ($Order) {
                db()->commit();
				$this->sendJSON([
					'data' => [
						'order_id' => $Order->order_id,
						'sn' => $Order->sn,
						'total_amount' => fmtMoney($Order->total_amount)
					],
				]);
			}
		} catch (\Exception $e) {
			db()->rollback();
			throw new \Exception($e->getMessage(), $e->getCode());

		}

	}

	public function modifyAction(){
		$order_id = (int)$this->post['order_id'];
		$goods = json_decode($this->post['goods']);
		if(!$goods || !is_array($goods)){
			throw new \Exception('参数错误',2001);
		}

		$Order = IOrder::findFirst($order_id);
		if(!$Order){
			throw new \Exception('订单不存在',303001);
		}

		$this->checkOwner($Order->user_id);

		db()->begin();
		$carts = [];
		foreach($goods as $item){
			if($item->skuId>0 AND $item->num>0){
				$TmpCart = ICart::add($this->User->user_id,$item->skuId,$item->num);
				if($TmpCart){
					$carts[] = $TmpCart->cart_id;
				}
			}
			
		}

		if(!$carts){
			throw new \Exception('没有选择任何商品',2001);
		}

		try {
			$data['coupon_user_id'] = $Order->coupon_user_id;
			$data['address_id'] = $Order->address_id;
			// $data['share_from_token'] = $Order->share_from_token;

			$Order = IOrder::renew($Order->order_id,$carts, $data);
			if ($Order) {
				db()->commit();
				$this->sendJSON([
					'data' => [
						'order_id' => $Order->order_id,
						'sn' => $Order->sn,
						'total_amount' => fmtMoney($Order->total_amount)
					],
				]);
			}
		} catch (\Exception $e) {
			db()->rollback();
			throw new \Exception($e->getMessage(), $e->getCode());

		}

		
	}

	/**
	 * 线下支付
	 * @return [type] [description]
	 */
	public function offlinePayAction()
	{
		$order_id = $this->post['order_id'];
		$Order = IOrder::findFirst($order_id);

		if (!$Order) {
			throw new \Exception('订单不存在', 303001);

		}

		$this->checkOwner($Order->user_id);

		if ($Order->offlinePay()) {
			$amount = fmtMoney($Order->total_amount);
			$this->sendJSON([
				'data' => [
					'amount' => $amount,
					'order_id' => $Order->order_id,
					'sn' => $Order->sn
				]
			]);
		} else {
			throw new \Exception('线下付款失败', 1002);
		}


	}

	/**
	 * 进入订单支付
	 * @return [type] [description]
	 */
	public function readyToPayAction()
	{
		$log = fopen(SITE_PATH . '/logs/ready_to_pay.txt', 'a+');
		fputs($log, var_export($this->post, true));
        $order_id = $this->post['order_id'];
        $openid = $this->post['openid'];
        // $env = $this->post['env'];
        $payment_method = $this->post['payment_method'];
        $payment_method = $payment_method ? $payment_method : conf('payment_method');

		$Order = IOrder::findFirst($order_id);

		if (!$Order) {
			throw new \Exception('订单不存在', 303001);

		}

		$this->checkOwner($Order->user_id);

        $amount = $Order->total_amount;
        //测试环境下支付金额固定为1分钱
        $env = $env ? $env : $this->conf['env'];
		// if($env=='test'){				
        //     $amount = 1;
		// }
		
		$fmt_amount = fmtMoney($amount);

        $payment_data = [];
        
        if ($payment_method == 'wechat') {

            $order_data = [
                'order_id'=>$Order->order_id,
                'sn'=>$Order->sn,
                'sku_names'=>$Order->getSkuNames(),
                'total_amount'=>$amount,
                'openid'=>$this->User->wx_openid
            ];
            //$this->sendJson($order_data);exit;
            $result = WechatPay::init()->preOrder($order_data);
            unset($result['appid']);
            
            $payment_data = [
                'timestamp'=>$result['timestamp'],
                'nonceStr'=>$result['nonceStr'],
                'package'=>$result['package'],
                'signType'=>$result['signType'],
                'paySign'=>$result['paySign'],
                'payConfig'=>$result,
            ];

        }

		if ($payment_method == 'braintree') {
			$payment_data['client_token'] = Braintree::generateClientToken();
        } 
        
        if ($payment_method == 'redsys') {
			$pay_params = [
				'amount' => $fmt_amount,
				'order_id' => $Order->order_id.'T'.rand(1,9),
				'trade_name' => $this->settings['app_name'] ,
				'titular' => $Order->order_id,
				'product_desc' =>  $Order->sn,
            ];
            
            if($env=='live'){
                $enviroment = 'live';
            }
            else{
                $enviroment = 'test';
            }
            
			$payment_data['redsys'] = (new RedSysPay($pay_params))->webRedirectPay($enviroment);
		}

		if ($payment_method == 'stripe') {
			/* if (isset($this->post['api_version'])) {

				if($env=='test'){
					$stripe_api_key = 'sk_test_7KJIaJ3cIH23KgGjciSFnnwb';
				}
				else{
					$stripe_api_key = $this->conf['stripe_key'];
                }
                fputs($log, date('Y-m-d H:i:s') . ' stripe_api_key:'.$stripe_api_key . PHP_EOL);


				\Stripe\Stripe::setApiKey($stripe_api_key);
				
				if(empty($this->User->stripe_customer)){
					try {					
						
						$customer = \Stripe\Customer::create(array(
							// 'email'=>$this->User->user_id.'@didigo.es',
							"description" => "Customer for Yipin shop user ".$this->User->user_id,
							// "source" => "tok_mastercard" // obtained with Stripe.js
						));

						$customer_id = $customer->id;
						fputs($log, var_export($customer, true));
		
						$this->User->stripe_customer = $customer_id;
						$this->User->save();
		
						// fputs($log, var_export($stripe_key, true));
						
					} catch (\Exception $e) {
						throw new \Exception($e->getMessage());
						// exit(http_response_code(500));
					}
				}
				else{
					$customer_id = $this->User->stripe_customer;
				}
				
				fputs($log, 'api_version:'.$this->post['api_version'].PHP_EOL);
						
				$stripe_key = \Stripe\EphemeralKey::create(
					["customer" => $customer_id],
					["stripe_version" => $this->post['api_version']]
                );
                
                $payment_data['stripe_key'] = $stripe_key;
                $payment_data['customerId'] = $customer_id ? $customer_id : null;
			} */
            
            //新版stripe支付方式

			$stripe_api_key = conf('stripe_key');

            fputs($log, 'stripe_api_key:'.$stripe_api_key . PHP_EOL);

            \Stripe\Stripe::setApiKey($stripe_api_key);
        
            fputs($log, 'api_version:'.$this->post['api_version'].PHP_EOL);

            $intent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'eur',
            ]);
            
    
            $payment_data['customerId'] = $customer_id ? $customer_id : null;
       
            $payment_data['stripeClientSecret'] = $intent->client_secret;
			
		}

		$this->sendJSON([
			'data' => array_merge(
				[
					'amount' => $fmt_amount,
					'orderId' => $Order->order_id,
					'sn' => $Order->sn,
				],
				$payment_data
			)
		], false);
	}

	/**
	 * 执行支付
	 * @return [type] [description]
	 */
	public function payAction()
	{
		$log = fopen(SITE_PATH . '/logs/pay.txt', 'a+');
		fputs($log, date('Y-m-d H:i:s') . PHP_EOL);
        fputs($log, var_export($this->post, true));
        
        $conf = conf();

		$order_id = $this->post['order_id'];
		$amount = $this->post['amount'];
        $pay_token = $this->post["pay_token"];
        $strip_payment_intent_id = $this->post["strip_payment_intent_id"];
        $payment_method = $this->post["payment_method"];
        $payment_method = $payment_method ? $payment_method : $conf['payment_method'];
        
        $env = $conf['env'];
        
        $paid_result = false;
        $transaction_id = '';

        db()->begin();

		$Order = IOrder::findFirst($order_id);

		if (!$Order) {
			throw new \Exception('订单不存在', 303001);
		}

		$this->checkOwner($Order->user_id);

		try {
			if ($pay_token == 'test') {

                $paid_result = true;			
                $transaction->id = 'test';
                
            } 
            elseif ($payment_method == 'stripe') {

				/* if($env=='test'){
					$stripe_api_key = 'sk_test_7KJIaJ3cIH23KgGjciSFnnwb';
				}
				else{
					$stripe_api_key = $this->conf['stripe_key'];
				}

                fputs($log, date('Y-m-d H:i:s') . ' stripe_api_key:'.$stripe_api_key . PHP_EOL);

				\Stripe\Stripe::setApiKey($stripe_api_key);
				
				$charge = \Stripe\Charge::create([
					'amount' => $Order->total_amount,
					'currency' => 'EUR',
					'description' => 'order ['.$Order->sn.'] paid',
					'source' => $pay_token,
					'customer'=>$this->post['customer_id']
				]);

				fputs($log, date('Y-m-d H:i:s') . ' charge_id:'.$charge->id . PHP_EOL);

				if($charge->paid){
					$result = new \stdClass;
					$result->success = true;
					$result->transaction = new \stdClass;

					fputs($log, date('Y-m-d H:i:s') . ' success pay' . PHP_EOL);
				}
				else{
					fputs($log, date('Y-m-d H:i:s') . ' fail pay' . PHP_EOL);
					fputs($log, date('Y-m-d H:i:s') . ' failure_code:' . $charge->failure_code . PHP_EOL);
					fputs($log, date('Y-m-d H:i:s') . ' failure_message:' . $charge->failure_message . PHP_EOL);
                } */

                \Stripe\Stripe::setApiKey(conf('stripe_key'));
                $intent = \Stripe\PaymentIntent::retrieve($strip_payment_intent_id);
                $charges = $intent->charges->data;
                $charge = $charges[0];
                fputs($log, var_export($charge,true) . PHP_EOL);

                fputs($log, date('Y-m-d H:i:s') . ' charge_id:'.$charge->id . PHP_EOL);
                
                $paid_result = false;
                if($charge->paid){

                    $paid_result = true;
                    $transaction_id = $charge->id;

                    fputs($log, date('Y-m-d H:i:s') . ' success pay' . PHP_EOL);
                }
                else{
                    fputs($log, date('Y-m-d H:i:s') . ' fail pay' . PHP_EOL);
                    fputs($log, date('Y-m-d H:i:s') . ' failure_code:' . $charge->failure_code . PHP_EOL);
                    fputs($log, date('Y-m-d H:i:s') . ' failure_message:' . $charge->failure_message . PHP_EOL);
                }
            }
            elseif($payment_method == 'money'){ //余额支付

                if($Order->total_amount > $this->User->money){
                    throw new \Exception('账户余额不足');
                }
                
                $this->User->money = $this->User->money - $Order->total_amount;
                if($this->User->save()){
                    $paid_result = true;
                    $transaction_id = '';
                }
                else{
                    throw new \Exception('账户余额更新失败');
                }
            }

			if ($paid_result) {

				$Order->transaction_id = $transaction_id;
				$Order->flag = 2;
				$Order->pay_flag = 2;
				$Order->pay_time = date('Y-m-d H:i:s');
				$Order->payment_method = $payment_method;
				if ($Order->save()) {
                    db()->commit();
					$this->sendJSON([
						'data' => [
							'order_id' => $Order->order_id,
							'sn' => $Order->sn,
						]
					]);
				} else {
					throw new \Exception("订单更新失败" . $Order->getErrorMsg(), 1002);

				}
            } 
            // else {
			// 	$errorString = "";

			// 	foreach ($result->errors->deepAll() as $error) {
			// 		$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
			// 	}

			// 	throw new \Exception("付款失败" . $errorString, 1001);

			// }
		} catch (\Exception $e) {

            db()->rollback();

			$msg = $e->getMessage();
			$msg = $msg ? $msg : '发生意外错误，支付失败';
			$log = fopen(SITE_PATH . '/logs/pay.log', 'a+');
			fputs($log, date('Y-m-d H:i:s') . PHP_EOL);
			fputs($log, 'order_id:' . $Order->order_id . PHP_EOL);
			fputs($log, 'msg:' . $e->msg . PHP_EOL);
			foreach ($e->getTrace() as $t) {
				fputs($log, 'line:' . $t['line'] . ' ' . $t['file'] . PHP_EOL);
			}
			fputs($log, '-------------------' . PHP_EOL);
			fclose($log);
			throw new \Exception($msg, $e->getCode() ? $e->getCode() : 1001);
		}

	}

	public function checkPayStatusAction(){
		$order_id = $this->post['order_id'];
		$Order = IOrder::findFirst($order_id);

		if (!$Order) {
			throw new \Exception('订单不存在', 303001);
		}

		$this->checkOwner($Order->user_id);

		$this->sendJSON([
			'paid'=>$Order->pay_flag ? 1 : 0
		]);
	}

	/**
	 * 取消订单
	 * @return [type] [description]
	 */
	public function cancelAction()
	{
		$order_id = $this->post['order_id'];
		$Order = IOrder::findFirst($order_id);

		if (!$Order) {
			throw new \Exception('订单不存在', 303001);

		}
		$this->checkOwner($Order->user_id);

		if ($Order->close()) {
			$this->sendJSON([]);
		}
	}

	public function finishAction()
	{
		$order_id = $this->post['order_id'];
		$Order = IOrder::findFirst($order_id);

		if (!$Order) {
			throw new \Exception('订单不存在', 303001);

		}
		$this->checkOwner($Order->user_id);

		db()->begin();
		try {
			if ($Order->finish()) {
				db()->commit();
	
				//成功购买数量+1
				// $this->User->buy_total = $this->User->buy_total+1;
				// $this->User->save();


				$this->sendJSON([]);
			}
		} catch (\Exception $e) {
			db()->rollback();
			throw new \Exception($e->getMessage(), $e->getCode());
		}

	}

	/**
	 * 申请退款
	 * @return [type] [description]
	 */
	public function requestRefoundAction()
	{
		$order_id = $this->post['order_id'];
		$Order = IOrder::findFirst($order_id);

		if (!$Order) {
			throw new \Exception('订单不存在', 303001);

		}
		$this->checkOwner($Order->user_id);

		if ($Order->requestRefound($this->post['refound_reason'])) {
			$this->sendJSON([]);
		}
	}

	/**
	 * 发布评论
	 */
	public function addCommentAction()
	{

		$Order = IOrder::findFirst($this->post['order_id']);
		if (!$Order) {
			throw new \Exception("订单不存在", 303001);

		}

		$this->checkOwner($Order->user_id);

		if ($Order->flag == 5) {
			throw new \Exception("此订单已经评价过了，请勿重复操作", 2001);

		}

		if ($Order->flag < 4) {
			throw new \Exception("不可评价尚未完成收货的订单", 2001);
		}

		$Comment = new IOrderComment;
		$Comment->assign([
			'order_id' => $Order->order_id,
			'star' => $this->post['star'] ? (int)$this->post['star'] : 3,
			'content' => $this->post['content'],
			'user_id' => $this->User->user_id,
		]);

		if ($Comment->save()) {
			$this->sendJSON([
				'data' => [
					'order_id' => $Order->order_id,
					'sn' => $Order->sn
				]
			]);
		}
	}

}
