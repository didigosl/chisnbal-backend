<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IUser;
use Common\Models\IUserLevel;
use Common\Models\IShare;
use Common\Models\IVipPayment;
use Common\Models\IArticle;
use Common\Libs\Func;
use Common\Components\ValidateMsg;
use Common\Components\Braintree;
use EasyWeChat\Foundation\Application;
use Common\Components\RedSysPay;

class UserController extends ControllerAuth {

	public function getMoneyAction(){

		$this->sendJSON([
			'data'=>[
				'money'=>fmtMoney($this->User->money)
			]
			
		]);

	}
	
	public function getInfoAction(){

		$this->sendJSON([
			'data'=>[
				'phone'=>$this->User->phone,
				'avatar'=>Func::staticPath($this->User->avatar),
				'name'=>$this->User->name,
				'status'=>$this->User->status,
				'gender'=>$this->User->gender,
				'gender_text'=>$this->User->gender ? $this->User->getGenderContext($this->User->gender) : '',
				'age'=>$this->User->age,
				'level_id'=>$this->User->level_id,
				'level_name'=>$this->User->UserLevel->level_name,
			]
			
		]);

	}

	public function updateAction(){

		$data = [];	

		/*if($this->post['avatar']){
			$data['avatar'] = $this->post['avatar'];
		}*/
		
		if($this->post['name']){
			$data['name'] = $this->post['name'];
		}

		if($this->post['gender']){
			$data['gender'] = $this->post['gender'];
		}

		if($this->post['age']){
			$data['age'] = $this->post['age'];
		}

		$this->User->assign($data);
		if(!$this->User->save()){
			throw new \Exception($this->User->getErrorMsg(), 2002);
			
		}
		else{
			$this->sendJson([]);
		}

    }
    
    public function changePasswordAction(){
        $password = $this->post['password'];
        $new_password = $this->post['new_password'];

        if(strlen($new_password)<6){
            throw new \Exception('新密码长度必须大于6位字符');
        }

        if(!security()->checkHash($password,$this->User->password)){
            throw new \Exception('旧密码错误');
        }

        $this->User->password = security()->hash($new_password);
        if($this->User->save()){
            $this->sendJSON([]);
        }
        else{
            throw new \Exception('密码修改失败');
        }
    }

	public function buyVipAction(){
		
		$levels = $this->db->fetchAll('SELECT * FROM i_user_level WHERE price>0');
		$list = [];
		foreach($levels as $v){

			$available = 1;
			if($this->User->level_id>=$v['level_id']){
            	$available = 0;
            
	        }

			$list[] = [
				'level_id'=>$v['level_id'],
				'level_name'=>$v['level_name'],
				'available'=>$available,
				'price'=>fmtMoney($v['price'])
			];
		}

        $Article = IArticle::findFirst(2);
        
		$this->sendJSON([
			'data'=>[
				'list'=>$list,
				'intro'=>Func::contentStaticPath($Article->content)
			]
		]);

    }
    
    public function toPayVipAction(){

        $level_id = $this->post['level_id'];
        $payment_method = $this->post['payment_method'];

        $UserLevel = IUserLevel::findFirst($level_id);

        if(!$UserLevel){
            throw new \Exception('VIP等级不存在');
        }

        $payment_method = $payment_method ? $payment_method : conf('payment_method');

        $env = conf('env');

        $VipPayment = new IVipPayment;
        $VipPayment->assign([
            'level_id'=>$level_id,
            'user_id'=>$this->User->user_id,
            'refer_user_id'=>0,
            'amount'=> $UserLevel->price,
            'transaction_id'=>'',
            'payment_method'=>$payment_method

        ]);
        if(!$VipPayment->save()){
            throw new \Exception("升级订单创建失败".$VipPayment->getErrorMsg(), 1002);
        }

        $payment_data = [];

        $order_id = 'VIP-'.$VipPayment->vip_payment_id;
        if ($payment_method == 'redsys') {
			$pay_params = [
				'amount' => fmtMoney($UserLevel->price),
				'order_id' => $order_id,
				'trade_name' => 'Buy VIP:'.$UserLevel->level_name ,
				'titular' => $order_id,
				'product_desc' => 'Buy VIP:'.$UserLevel->level_name,
            ];
            
            if($env=='live'){
                $enviroment = 'live';
            }
            else{
                $enviroment = 'test';
            }
            
			$payment_data['redsys'] = (new RedSysPay($pay_params))->webRedirectPay($enviroment);
		}

        if('stripe'==$payment_method){
            $stripe_api_key = conf('stripe_key');

            \Stripe\Stripe::setApiKey($stripe_api_key);

            $intent = \Stripe\PaymentIntent::create([
                'amount' => $UserLevel->price,
                'currency' => 'eur',
            ]);
    
            $payment_data['stripeClientSecret'] = $intent->client_secret;
        }

        $this->sendJSON([
            'data'=>[
                'vip_payment_id'=>$VipPayment->vip_payment_id,
                'payment_method'=>$payment_method,
                'amount'=>fmtMoney($UserLevel->price),
                'payment_data'=>$payment_data
            ]
        ]);
    }

	public function payVipAction(){

		$vip_payment_id = $this->post['vip_payment_id'];
        $amount = $this->post['amount'];
        $strip_payment_intent_id = $this->post["strip_payment_intent_id"];
        $share_code = $this->post["share_code"];
        
        $VipPayment = IVipPayment::findFirst($vip_payment_id);
        if(!$VipPayment){
            throw new \Exception("付款信息不存在");
        }

        if(!$VipPayment->UserLevel){
            throw new \Exception("等级信息错误", 1);
            
        }
        if($this->User->level_id==$VipPayment->level_id){
            throw new \Exception('您的已经是“'.$VipPayment->UserLevel->level_name.'”了，无需重复升级', 1);
            
        }

        if($this->User->level_id > $VipPayment->level_id){
            throw new \Exception('您的等级高于“'.$VipPayment->LevUserLevelel->level_name.'”，无需升级', 1);
            
        }

        if($share_code){
        	$Share = IShare::findFirst([
        		'code=:code:',
        		'bind'=>[
					'code'=>$share_code
        		]
        	]);

        }

        // db()->begin();

        $paid_result = false;
        $transaction_id = '';

        if($VipPayment->payment_method=='money'){
            if($this->User->money<$VipPayment->amount){
                $VipPayment->status = 3;
                $VipPayment->save();
                
                throw new \Exception('账户余额不足');
            }

            if($VipPayment->paid()){
                $VipPayment->refresh();
                // var_dump($VipPayment->status);exit;
                $paid_result = true;
            }
            else{
                db()->rollback();
                throw new \Exception('执行付款失败');
            }
        }

        if($VipPayment->payment_method=='stripe'){
            \Stripe\Stripe::setApiKey(conf('stripe_key'));
            $intent = \Stripe\PaymentIntent::retrieve($strip_payment_intent_id);
            $charges = $intent->charges->data;
            $charge = $charges[0];
            
            $paid_result = false;
            if($charge->paid){
                $paid_result = true;
                $transaction_id = $charge->id;
            }
            else{
                $VipPayment->status = 3;
                $VipPayment->save();
                throw new \Exception('付款失败');
            }
        }

        if($VipPayment->payment_method=='redsys'){
           
        }

		if($paid_result){
            $VipPayment->transaction_id = $transaction_id;
            $VipPayment->save();
			
        }
        else{
            $VipPayment->status = 3;
            $VipPayment->transaction_id = $transaction_id;
            $VipPayment->save();
        }

        // db()->commit();
        $this->sendJSON([
            'data' => [
                'vip_payment_id' => $VipPayment->vip_payment_id,
                'status'=>$VipPayment->status,

            ],
        ]);
    }
}
