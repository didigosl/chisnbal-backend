<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IArea;
use Common\Models\IUser;
use Common\Libs\Func;
use Omnipay\Omnipay;
use Common\Components\RedSysPay;
use Common\Components\WechatPay;
use Common\Components\Mail;

class TestController extends ControllerBase {


    public function testAction(){
        echo SITE_PATH;
        exit;
    }

	public function getAction(){

		$num1 = Func::makeOrderNum();
		$num2 = Func::makeNum();
		$this->sendJSON([
			'data1'=>$num1,
			'data2'=>$num2
		]);
	}

	public function areaAction(){
		$Area = IArea::findFirst(3015);
		var_dump($Area->getParents());
		exit;
	}

	public function md5Action(){
		$params = $this->post['params'];
		$token = $this->post['token'];
		$time = $this->post['time'];

		$User = IUser::findFirst([
			'token=:token:',
			'bind'=>['token'=>$this->post['token']]
		]);

		if(!$User){
			throw new \Exception("用户信息不存在", 2003);
			
		}
		$sign = md5($params.$User->secret_key.$time);
		$this->sendJSON([
			'origin'=>$params.$User->secret_key.$time,
			'secret_key'=>$User->secret_key,
			'sign'=>$sign,
		]);
	}

	public function payAction(){
		

		$gateway = Omnipay::create('RedSys');
		$gateway->setApiKey('abc123');

		$formData = array('number' => '4242424242424242', 'expiryMonth' => '6', 'expiryYear' => '2030', 'cvv' => '123');
		$response = $gateway->purchase(array('amount' => '10.00', 'currency' => 'USD', 'card' => $formData))->send();

		if ($response->isRedirect()) {
			// redirect to offsite payment gateway
			$response->redirect();
		} elseif ($response->isSuccessful()) {
			// payment was successful: update database
			print_r($response);
		} else {
			// payment failed: display message to customer
			echo $response->getMessage();
		}
	}

	public function redsysWebPayAction(){

		$params = [
			'amount'=>'0.01',
			'order_no'=>date('YmdHis'),
			'trade_name'=>'trade_name',
			'titular'=>'Titular',
			'product_desc'=>'Product description',
			// ''=>'',
		];
		// $Pay =  new RedSysPay($params);
		
		// $data = $Pay->webRedirectPay();
		$data = (new RedSysPay($params))->webRedirectPay();
		echo $this->d->one($data);
		exit;
		echo $this->d->one($form);
	}

	public function stripeTokenACtion(){
		$gateway = Omnipay::create('Stripe');
		$stripe_key = 'sk_test_7KJIaJ3cIH23KgGjciSFnnwb';
		$gateway->setApiKey($stripe_key);

		// $gateway->createToken()->send();
		$ret = $gateway->fetchToken()->send();

		echo '<pre>';
		var_dump($ret);
		echo '</pre>';
		echo $this->d->one($ret);
		echo $this->d->one($ret->getResponse());
		exit;

	}

	public function stripePayAction(){
		// Set your secret key: remember to change this to your live secret key in production
		// See your keys here: https://dashboard.stripe.com/account/apikeys
		// \Stripe\Stripe::setApiKey("sk_test_BQokikJOvBiI2HlWgH4olfQ2");
		// \Stripe\Stripe::setApiKey("sk_live_Epsd8R6yJMTXXAaO8z0CMyi6");

		// // Token is created using Checkout or Elements!
		// // Get the payment token ID submitted by the form:
		// $token = $_POST['stripeToken'];

		// $charge = \Stripe\Charge::create([
		// 	'amount' => 999,
		// 	'currency' => 'usd',
		// 	'description' => 'Example charge',
		// 	// 'source' => $token,
		// 	'source' => 'tok_visa',
		// ]);
		
		// var_dump($charge);exit;
		// $this->sendJSON($charge);

		$log = fopen(SITE_PATH.'/logs/pay.txt','a+');

		$pay_token = $this->post['stripe_token'];
		$amount = '9.99';

		fputs($log,var_export($this->post,true) );
		$gateway = Omnipay::create('Stripe');
		$stripe_key = 'sk_test_7KJIaJ3cIH23KgGjciSFnnwb';
		$gateway->setApiKey($stripe_key);
		$pay_params = [
			'amount' => $amount,
			'currency' => 'USD',
			'token' => $pay_token,
		];
		fputs($log,var_export($pay_params,true) );

		$response = $gateway->purchase($pay_params)->send();


		if ($response->isRedirect()) {
			fputs($log,date('Y-m-d H:i:s').' redirect pay'.PHP_EOL);
			// $response->redirect();
		} elseif ($response->isSuccessful()) {
			$result = new \stdClass;
			$result->success = true;
			$result->transaction = new \stdClass;
			// payment was successful: update database
			
			fputs($log,date('Y-m-d H:i:s').' success pay'.PHP_EOL);
			fputs($log,date('Y-m-d H:i:s').' '. $response->getMessage().PHP_EOL);
			fputs($log,date('Y-m-d H:i:s'). ' '. var_export( $response->getTransactionReference(),true).PHP_EOL);
			// echo $this->d->one($response);
		} else {
			// payment failed: display message to customer
			// echo $response->getMessage();
			fputs($log,date('Y-m-d H:i:s').' fail pay'.PHP_EOL);
			fputs($log,date('Y-m-d H:i:s').' '. $response->getMessage().PHP_EOL);
		}	

		echo $response->getMessage();
		exit;
    }
    
    public function wechatAction(){
        $result = WechatPay::init()->preOrder([
            'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...
            'body'             => 'iPad mini 16G 白色',
            'detail'           => 'iPad mini 16G 白色',
            'out_trade_no'     => '1217752501201407033233368018',
            'total_fee'        => 5388, // 单位：分
            'notify_url'       => 'http://xxx.com/order-notify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'sub_openid'        => '当前用户的 openid', 
        ]);
        var_dump($result);
        exit;
    }

    public function mailAction(){
        $settings = settings();
        Mail::init()->sendContent('imcaidao@163.com','工作汇报','2019工作汇报内容，详细内容请联系公司前天咨询。');
    }


}
