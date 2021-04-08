<?php

namespace Common\Models;

class IWechatPreorder extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    public $prepay_id;
    public $order_id;
    public $order_sn;
    public $msg;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_wechat_preorder");
        $this->belongsTo('order_id', 'Common\Models\IOrder', 'order_id', ['alias' => 'Order']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_wechat_preorder';
    }

    public static function getStatusContext($var = null) {
        $data = [
            -1  => '付款失败',
            1   => '待付款',
            2   =>  '付款成功'
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function beforeCreate(){
        parent::beforeCreate();
        $this->status = 1;
    }

}
