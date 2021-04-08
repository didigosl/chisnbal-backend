<?php
namespace Common\Components;

use Common\Models\IWechatPreorder;
use Common\Models\IOrder;
use Phalcon\Mvc\User\Component;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

class WechatPay extends Component {

    static $i;
    protected $payment;

	static public function init(){

		$conf = \Phalcon\Di::getDefault()->get('conf');
        $url = \Phalcon\Di::getDefault()->get('url');
        $request = \Phalcon\Di::getDefault()->get('request');
        
        // $isWxmp =  \Phalcon\DI::getDefault()->get('isWxmp');
        $isWxmp = \Phalcon\DI::getDefault()->get('session')->get('isWxmp');
        // var_dump($isWxmp);exit;
        if($isWxmp){
            $options = [
                // 前面的appid什么的也得保留哦
                'app_id' => $conf['wechat_mp_app_id'],
                'secret' => $conf['wechat_mp_secret'],
                // ...
            
                // payment
                'payment' => [
                    'merchant_id'        => $conf['wechat_mp_mch_id'],
                    'key'                => $conf['wechat_mp_key'],
                    'cert_path'          => SITE_PATH.$conf['wechat_mp_cert_path'], // XXX: 绝对路径！！！！
                    'key_path'           => SITE_PATH.$conf['wechat_mp_key_path'],      // XXX: 绝对路径！！！！
                    'notify_url'         => 'http://'.$request->getHttpHost().$url->getStatic('/api/pay/notification'),       // 你也可以在下单时单独设置来想覆盖它
                    // 'device_info'     => '013467007045764',
                    // 'sub_app_id'      => '',
                    // 'sub_merchant_id' => '',
                    // ...
                ],
            ];
        }
        else{
            $options = [
                // 前面的appid什么的也得保留哦
                'app_id' => $conf['wechat_app_id'],
                'secret' => $conf['wechat_secret'],
            
                // payment
                'payment' => [
                    'merchant_id'        => $conf['wechat_mch_id'],
                    'key'                => $conf['wechat_key'],
                    'cert_path'          => SITE_PATH.$conf['wechat_cert_path'], // XXX: 绝对路径！！！！
                    'key_path'           => SITE_PATH.$conf['wechat_key_path'],      // XXX: 绝对路径！！！！
                    'notify_url'         => 'http://'.$request->getHttpHost().$url->getStatic('/api/pay/notification'),       // 你也可以在下单时单独设置来想覆盖它
                
                ],
            ];
        }
        

        // var_dump($options);
        
        $app = new Application($options);

        $i = new self;
        $i->setPayment($app->payment);
        return $i;
		

    }
    
    public function setPayment($payment){
        $this->payment = $payment;
    }

	public function preOrder($data){
        
        $attrs = [];

        $attr['body'] = '订单：'.$data['sn'];
        $attr['detail'] = $data['sku_names'];
        $attr['out_trade_no'] = $data['sn'].'-'.time();
        $attr['total_fee'] = $data['total_amount'];        

        // $isWxmp =  \Phalcon\DI::getDefault()->get('isWxmp');
        $isWxmp = \Phalcon\DI::getDefault()->get('session')->get('isWxmp');
        if($isWxmp){
            $attr['trade_type'] = 'JSAPI';
            $attr['openid'] = $data['openid'];
        }
        else{
            $attr['trade_type'] = 'APP';
        }
        
        $order = new Order($attr);
        $result = $this->payment->prepare($order);
        // var_dump($result);exit;
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id;

            if($isWxmp){
                $config = $this->payment->configForJSSDKPayment($prepayId);               
            }
            else{
                $config = $this->payment->configForAppPayment($prepayId);
            }

            // $config['trade_type'] = $attr['trade_type'];
            // $config['isWxmp'] = $isWxmp;

            // $WechatPreorder = new IWechatPreorder;
            // $WechatPreorder->assign([
            //     'prepay_id'=>$prepayId,
            //     'order_id'=>$data['order_id'],
            //     'order_sn'=>$data['sn']
            // ]);
            // $WechatPreorder->save();
            // var_dump($config);exit;
            return $config;
        }        
        else{
            throw new \Exception($result->return_msg.'/'.$result->return_code. '/'.$isWxmp);
        }
    }
    
    public function handleNotify(){
        $response = $this->payment->handleNotify(function($notify, $successful){
            
            $log = fopen(SITE_PATH.'/logs/pay_notify.txt','a+');
            fputs($log,var_export($notify,true).PHP_EOL);
            

            $attr = explode('-',$notify->out_trade_no);
            $out_trade_no = $attr[0];

            fputs($log,'out_trade_no:'.$out_trade_no.PHP_EOL);

            $Order = IOrder::findFirst([
                'sn=:sn:',
                'bind'=>[
                    'sn'=>$out_trade_no
                ]
            ]);

            if (!$Order) { // 如果订单不存在
                fputs($log,'Order not exist'.PHP_EOL);
                return 'Order not exist.'; 
            }

            if($Order->pay_flag==1){
                return true;
            }

            if ($successful) {
                
                $Order->transaction_id = $notify->transaction_id;
                $Order->paid();

            }
            fclose($log);
            return true; // 或者错误消息
        });
        
        $response->send(); 
    }
	
}
