<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use QL\QueryList;

class StripePay extends Component
{

    public $gateway;

    public $params = [];

    public $redsys_errors = [

    ];

    public function __construct($params)
    {
        $log_dir = SITE_PATH.'/../runtime/logs';
        if(!is_dir($log_dir)){
            mkdir($log_dir,0777);
        }

        $this->params = $params;

        $conf = \Phalcon\Di::getDefault()->get('conf');
        
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

        file_put_contents(SITE_PATH.'/pay.txt',$this->params['order_id'].time().PHP_EOL);

        \Stripe\Stripe::setApiKey("sk_test_BQokikJOvBiI2HlWgH4olfQ2");

        $charge = \Stripe\Charge::create([
            'amount' => 999,
            'currency' => 'usd',
            'source' => 'tok_visa',
            'receipt_email' => 'jenny.rosen@example.com',
        ]);


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

}
