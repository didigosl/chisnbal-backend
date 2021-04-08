<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;

class Braintree extends Component {

	static $inited = false;

	static public function init(){

		if(!self::$inited){
			$conf = \Phalcon\Di::getDefault()->get('conf');
			// var_dump($conf);exit;
			\Braintree\Configuration::environment($conf['bt_environment']);
			\Braintree\Configuration::merchantId($conf['bt_merchant_id']);
			\Braintree\Configuration::publicKey($conf['bt_public_key']);
			\Braintree\Configuration::privateKey($conf['bt_private_key']);

			self::$inited = true;
		}
		

	}

	static public function generateClientToken(){

		self::init();
		return \Braintree\ClientToken::generate();
	}

	static public function saleTransaction($amount,$nonce){
		self::init();
		$result = \Braintree\Transaction::sale([
		    'amount' => $amount,
		    'paymentMethodNonce' => $nonce,
		    'options' => [
		        'submitForSettlement' => true
		    ]
		]);

		return $result;
	}
	
}
