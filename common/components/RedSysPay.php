<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use QL\QueryList;

class RedSysPay extends Component
{

    public $gateway;

    public $params = [];

    public $redsys_errors = [
        'SIS0062' => '付款金额超出上限',
        'SIS0063' => '付款银行卡号不正确',
        'SIS0064' => '付款银行卡号不正确',
        'SIS0065' => '付款银行卡号不正确',
        'SIS0066' => '付款银行卡过期时间不正确',
        'SIS0067' => '付款银行卡过期时间不正确',
        'SIS0068' => '付款银行卡已过期',
    ];

    public function __construct($params)
    {
        $log_dir = SITE_PATH.'/../runtime/logs';
        if(!is_dir($log_dir)){
            mkdir($log_dir,0777);
        }

        $this->params = $params;

        $conf = \Phalcon\Di::getDefault()->get('conf');
        // var_dump($conf);exit;
        // $conf = [
        //     'redsys_key'=>'sq7HjrUOBfKmC576ILgskD5srU870gJ7',
        //     'redsys_merchant_code'=>'999008881',
        //     'redsys_merchant_terminal'=>'1',
        // ];
        $redsys_key = $conf['redsys_key'];
        $redsys_merchant_code = $conf['redsys_merchant_code'];
        $redsys_merchant_terminal = $conf['redsys_merchant_terminal'];
        
        // var_dump($conf);

        $this->gateway = new \Buuum\Redsys($redsys_key);

        $notification_url = $this->request->getScheme().'://'.$this->request->getHttpHost().$this->url->get('api/pay/notification');
        // echo $notification_url;exit;
        $this->gateway->setNotification($notification_url); //Url de notificacion
        $this->gateway->setMerchantcode($redsys_merchant_code);
        $this->gateway->setTerminal($redsys_merchant_terminal);
        $this->gateway->setCurrency(978);
        $this->gateway->setAmount($this->params['amount']);
        $this->gateway->setOrder($this->params['order_id'].'B'.substr(time(),5));

        file_put_contents(SITE_PATH.'/pay.txt',var_export([
            'notification_url'=>$notification_url,
            'redsys_merchant_code'=>$redsys_merchant_code,
            'redsys_merchant_terminal'=>$redsys_merchant_terminal,
            'amount'=>$this->params['amount'],
            'order_id'=>$this->params['order_id']
        ],true));
        // var_dump($this->params);

    }

    /**
     * WebService 支付方式
     */
    public function wsPay()
    {

        try {

            // $this->gateway->setOrder($this->params['order_no']);
            $this->gateway->setPan($this->params['bank_number']);
            $this->gateway->setExpiryDate($this->params['expiry']);
            $this->gateway->setCVV($this->params['cvv']);
            $this->gateway->setTransactiontype('A');
            $this->gateway->setIdentifier('REQUIRED');
            $result = $this->gateway->firePayment();

            if ($result['error']) {
                if ($this->redsys_errors[$result['error']]) {
                    throw new \Exception($this->redsys_errors[$result['error']], 2002);
                }
            } else {
                return true;
            }

        } catch (\Exception $e) {

            throw new \Exception($e->message(), $e->getCode());
            // echo $e->getMessage();
            // die;
        }
    }

    /**
     * 网页跳转支付方式
     */
    public function webRedirectPay($enviroment='test')
    {

        try {

            $this->gateway->setTransactiontype('0');
            $this->gateway->setMethod('C');

            $success_url = $this->request->getScheme().'://'.$this->request->getHttpHost().$this->url->get('w/pay/success',[order_id=>$this->params['order_id']]);
            $fail_url = $this->request->getScheme().'://'.$this->request->getHttpHost().$this->url->get('w/pay/fail',[order_id=>$this->params['order_id']]);
            $this->gateway->setUrlOk($success_url);
            $this->gateway->setUrlKo($fail_url);

            $this->gateway->setTradeName($this->params['trade_name']);
            $this->gateway->setTitular($this->params['titular']);
            $this->gateway->setProductDescription($this->params['product_desc']);

            $form = $this->gateway->createForm($enviroment);
            if($form){

                file_put_contents(SITE_PATH.'/pay_form.html',$form);
                $rules = [
                    'form_action'=>['form','action'],
                    'Ds_MerchantParameters'=>['input[name="Ds_MerchantParameters"]','value'],
                    'Ds_Signature'=>['input[name="Ds_Signature"]','value'],
                    'Ds_SignatureVersion'=>['input[name="Ds_SignatureVersion"]','value'],
                    // 'submitname'=>['input[name="submitname"]','value'],
                ];
                $data = QueryList::Query($form,$rules)->data[0];
            }
            else{
                throw new \Exception('获取支付参数失败');
            }
            

        } catch (Exception $e) {

            throw new \Exception('生成支付参数失败 '.$e->getMessage());
            // var_dump($e->getCode(),$e->getMessage());
            exit;
        }

        return $data;
    }

}
