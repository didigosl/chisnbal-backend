<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Libs\Func;
use Common\Components\RedSysPay;
use Common\Components\WechatPay;
use Common\Models\IOrder;
use Common\Models\IRecharge;
use Common\Models\IPayLog;
use Common\Models\IVipPayment;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

class PayController extends ControllerBase {

    public $log;

    public function initialize()
    {
        $log_dir = SITE_PATH.'/../runtime/logs';
        if(!is_dir($log_dir)){
            mkdir($log_dir,0777);
        }
        $this->log = new FileAdapter($log_dir.'/pay.log');
    }

    public function stripeNotifyAction(){
        ini_set('display_errors',0);
        // You can find your endpoint's secret in your webhook settings
        $endpoint_secret = conf('stripe_endpoint_secret');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        $log = fopen(SITE_PATH.'/logs/stripe_notify.txt','a+');
        fputs($log,$payload);
        fclose($log);

        // var_dump($_SERVER);
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }
        // echo $event->type;
        if ($event->type == "payment_intent.succeeded") {

            $intent = $event->data->object;
            $transaction_id = $intent->charges->data[0]->id;

            printf("Succeeded: %s", $transaction_id);
            
            $Order = IOrder::findFirst([
                'transaction_id=:transaction_id:',
                'bind'=>[
                    'transaction_id'=>$transaction_id
                ]
            ]);
            if($Order){
                if($Order->flag==1){
                    $Order->flag = 2;
                    $Order->save();
                }
            }
            else{
                $VipPayment = IVipPayment::findFirst([
                    'transaction_id=:transaction_id:',
                    'bind'=>[
                        'transaction_id'=>$transaction_id
                    ]
                ]);

                if($VipPayment){
                    if(!$VipPayment->paid()){
                        echo ("Failed");
                    }
                }
            }

            http_response_code(200);
            exit();
        } elseif ($event->type == "payment_intent.payment_failed") {
            $intent = $event->data->object;
            $error_message = $intent->last_payment_error ? $intent->last_payment_error->message : "";
            printf("Failed: %s, %s", $intent->id, $error_message);
            http_response_code(200);
            exit();
        }
        exit;
    }

	public function notificationAction(){

        $conf = \Phalcon\Di::getDefault()->get('conf');

        if($conf['payment_method']=='wechat'){
            WechatPay::init()->handleNotify();
        }
        
        if($conf['payment_method']=='redsys'){
            $redsys = new \Buuum\Redsys($conf['redsys_key']);

            $this->db->begin();
            try{
                $this->log->info(var_export($_POST,true));
                $result = $redsys->checkPaymentResponse($_POST);            
                $this->log->info(var_export($result,true));

                if($result['Ds_Order']){
                    $data = [];
                    
                    //用户充值支付确认流程
                    if(strpos($result['Ds_Order'],'R')===0){
                        $recharge_id = intval(substr($result['Ds_Order'],1));
                        $Recharge = IRecharge::findFirst($recharge_id);

                        if(!$Recharge){
                            $this->log->error('Recharge not found : Ds_Order '.$result['Ds_Order']);
                        }
                        else{
                            $data['amount'] = $result['Ds_Amount'];
                            if($result['Ds_Response']!=='0000'){
                                $data['result'] = 'fail';
                            }
                            else{
                                $data['result'] = !empty($result['Ds_ErrorCode']) ? 'fail' : 'success';
                            }
                            
                            $data['content'] = json_encode($result,JSON_UNESCAPED_UNICODE);

                            $Recharge->assign($data);

                            if(!$Recharge->save()){
                                $this->db->commit();
                                echo 'success';
                                exit;
                            }
                            else{
                                $this->db->rollback();
                                $this->log->error('Recharge save failed:'.$Recharge->getErrorMsg(PHP_EOL));
                                echo 'fail';
                                exit;
                            }
                            
                        }
                    }
                    //普通订单支付确认流程
                    else{
                        $data['order_id'] = (int)$result['Ds_Order'];
                        $data['amount'] = $result['Ds_Amount'];
                        if($result['Ds_Response']!=='0000'){
                            $data['result'] = 'fail';
                        }
                        else{
                            $data['result'] = !empty($result['Ds_ErrorCode']) ? 'fail' : 'success';
                        }
                        $data['content'] = json_encode($result,JSON_UNESCAPED_UNICODE);
                        $data['payment_method'] = 'redsys';
                        
                        $PayLog = new IPayLog();
                        $PayLog->assign($data);
                        if(!$PayLog->save()){
                            $this->db->rollback();
                            $this->log->error('i_pay_log save failed:'.$PayLog->getErrorMsg(PHP_EOL));
                            echo 'fail';
                            exit;
                        }
                        else{
                            $this->db->commit();
                            echo 'success';
                            exit;
                        }
                    }
                    
                }
            }
            catch (\Exception $e) {
                $this->db->rollback();
                echo 'fail';
                $this->log->error('receive notification failed:'.$e->getMessage());
            }
            
        }
		exit;
    }
    

    public function testAction(){

        $order_id = (int)$this->request->getQuery('order_id');
        $payment_method = $this->request->getQuery('payment_method');
        $order_id = $order_id ? $order_id : 70;

        $env = $this->request->getQuery('env');
        $env = $env=='live' ? 'live' :'test';

        $Order = IOrder::findFirst($order_id);

		if(!$Order){
			throw new \Exception('订单不存在', 2002);
			
        }
        
        $payment_method = $payment_method ? $payment_method : conf('payment_method');

		$amount = fmtMoney($Order->total_amount);

		if($payment_method=='braintree'){
			$payment_data['client_token'] = Braintree::generateClientToken();
		}
		elseif($payment_method=='redsys'){
			$pay_params = [
				'amount'=>$amount,
				'order_id'=>'T'.$Order->order_id,
				'trade_name'=>$this->settings['app_name'].'商城支付',
				'titular'=>'订单支付',
				'product_desc'=>'订单编号：'.$Order->sn,
			];

			$payment_data['redsys'] = (new RedSysPay($pay_params))->webRedirectPay($env);
        }
        
        header('location:/pay_form.html');
        exit;
    }

}
