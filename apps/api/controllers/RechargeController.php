<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IRecharge;
use Common\Components\RedSysPay;
use Common\Libs\Func;

class RechargeController extends ControllerAuth {

	public function createAction(){

        $conf = conf();

        if(!$conf['enable_recharge']){
            throw new \Exception('功能未开通');
        }

        $amount = $this->post['amount'];

        if(!is_numeric($amount)){
            throw new \Exception('充值金币必须是数字');
        }

        $amount = floor($amount * 100);

        $recharge_award = (int)setting('recharge_award');
        if($recharge_award){
            $add_amount = (100+$recharge_award)*$amount;
        }
        else{
            $add_amount = $amount;
        }

        $Recharge = new IRecharge;
        $Recharge->user_id = $this->User->user_id;
        $Recharge->amount = $amount;
        $Recharge->add_amount = $add_amount;
        $Recharge->payment_method = $conf['payment_method'];
        if($Recharge->save()){
            if ($conf['payment_method'] == 'redsys') {
                $pay_params = [
                    'amount' => fmtMoney($Recharge->amount),
                    'order_id' => 'R'.$Recharge->recharge_id,
                    'trade_name' => $this->settings['app_name'] ,
                    'titular' => 'Recharge Payment',
                    'product_desc' =>  'User Recharge Payment',
                ];
                
                if($conf['env']=='live'){
                    $enviroment = 'live';
                }
                else{
                    $enviroment = 'test';
                }
                
                $payment_data['redsys'] = (new RedSysPay($pay_params))->webRedirectPay($enviroment);
            }

            $this->sendJSON([
                'data' => array_merge(
                    [
                        'amount' => $pay_params['amount'],
                        'recharge_id' => $Recharge->recharge_id,
                    ],
                    $payment_data
                )
            ], false);
        }

        
    }
    
    public function getAwardAction(){
        $award = (int)settings('recharge_award');
        $this->sendJSON([
            'data'=>[
                'award'=>$award
            ]
        ]);
    }
	
}
