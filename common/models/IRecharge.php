<?php
namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;

class IRecharge extends Model
{

    public $recharge_id;

    public $sn;

    public $user_id;

    public $amount;

    public $add_amount;

    public $result;
    
    public $content;

    public $msg;

    public $payment_method;

    public $create_time;

    public function initialize()
    {
        $this->setSource("i_recharge");
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_recharge';
    }

    public function afterCreate(){
        $this->recharge();
    }

    public function afterSave(){

        if($this->getOperationMade() == self::OP_UPDATE && $this->hasUpdated('result')){
            $this->recharge();
        }
        
    }

    public function recharge(){
        if($this->result=='success'){
            $this->User->money = $this->User->money+$this->add_amount;
            if($this->User->money<0){
                throw new \Exception('用户余额超出合理范围');
            }
            
            if(!$this->User->save()){
                throw new \Exception('用户余额更新失败');
            }
        }
    }

}
