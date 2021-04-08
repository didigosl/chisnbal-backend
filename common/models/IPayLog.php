<?php
namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;

class IPayLog extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $pay_log_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $order_id;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $amount;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $result;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $content;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $msg;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $payment_method;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_pay_log");
        $this->belongsTo('order_id', 'Common\Models\IOrder', 'order_id', ['alias' => 'Order']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_pay_log';
    }

    public function beforeCreate()
    {
        parent::beforeCreate();
        try{
            if ($this->result == 'success') {
                if ($this->Order) {
                    if ($this->Order->flag == 1 && !$this->Order->pay_flag) {
                        try {
                            return $this->Order->paid($this->payment_method);
                        } catch (\Exception $e) {
                            throw new \Exception('付款失败：'.$e->getMessage());
                        }
    
                    } 
                    // else {
                    //     throw new \Exception('订单已经付款');
                    // }
                }
            }
        } catch (\Exception $e){
            $this->msg = $e->getMessage();
        }
        

    }

}
