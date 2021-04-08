<?php

namespace Common\Models;

use JPush\Client as JPush;

class ICouponUser extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $coupon_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $coupon_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $use_flag;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $order_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $shop_id;

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
        $this->setSource("i_coupon_user");
        $this->belongsTo('coupon_id', 'Common\Models\ICoupon', 'coupon_id', ['alias' => 'Coupon']);
    }

    static public function getPkCol(){
        return 'coupon_user_id';
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_coupon_user';
    }

    public function beforeCreate(){

        parent::beforeCreate();
        $this->use_flag = 0;
        $this->shop_id = $this->Coupon->shop_id;
    }

    public function beforeSave(){
        $this->coupon_id = (int)$this->coupon_id;
        $this->user_id = (int)$this->user_id;
        $this->use_flag = (int)$this->use_flag;
        $this->order_id = (int)$this->order_id;
    }

    public function afterCreate(){
        $log = fopen(SITE_PATH.'/logs/coupon_push_log.txt','a+');
        $conf = $this->getDi()->get('conf');
        $content = '您收到一张新的优惠券';
        if ($conf['enable_push']) {
            //推生产环境
           /*  $client = new JPush($conf['jiguang_app_key'], $conf['jiguang_secret']);
            $pusher = $client->push();
            $pusher->setPlatform('all');
            $pusher->addAllAudience();
            $pusher->iosNotification($content,[
                'badge' => '+1',
            ]);
            $pusher->androidNotification($content,[]);
            $pusher->options([
                'apns_production'=>true,
            ]);
            try {
                $pusher->send();
            } catch (\JPush\Exceptions\JPushException $e) {

                fputs($log,date('Y-m-d H:i:s').$e->getMessage().PHP_EOL);
            }

            //推开发环境
            $client = new JPush($conf['jiguang_app_key'], $conf['jiguang_secret']);
            $pusher = $client->push();
            $pusher->setPlatform('all');
            $pusher->addAllAudience();
            $pusher->iosNotification($content,[
                'badge' => '+1',
            ]);
            $pusher->androidNotification($content,[]);
            $pusher->options([
                'apns_production'=>false,
            ]);
            try {
                $pusher->send();
            } catch (\JPush\Exceptions\JPushException $e) {

                fputs($log,date('Y-m-d H:i:s').$e->getMessage().PHP_EOL);
            } */
        }
    }
}
