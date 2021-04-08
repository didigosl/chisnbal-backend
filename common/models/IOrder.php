<?php

namespace Common\Models;

use Common\Components\Mail;
use Common\Libs\Func;

class IOrder extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $order_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $parent_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $shop_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $level_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $sn;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total_fee;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total_amount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total_rebate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $all_rebate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total_discount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total_coupon;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $express_fee;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $adjustment;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $coupon_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $address_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $receive_man;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $receive_phone;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $receive_area;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $receive_city_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $receive_address;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $express_corp_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $express_no;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $remark;

    /**
     * 
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $transaction_id;

    /**
     * 
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $share_from_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $flag;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $payment_method;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $pay_flag;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $refound_flag;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $refound_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $refound_reason;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $close_flag;

    public $finish_flag;

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
    public $pay_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $delivery_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $finish_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {

        $this->setSource("i_order");
        $this->belongsTo('parent_id', 'Common\Models\IOrder', 'order_id', ['alias' => 'Parent']);
        $this->hasMany('parent_id', 'Common\Models\IOrder', 'order_id', ['alias' => 'sons']);
        $this->belongsTo('shop_id', 'Common\Models\IShop', 'shop_id', ['alias' => 'Shop']);
        $this->hasMany('order_id', 'Common\Models\IOrderSku', 'order_id', ['alias' => 'skus']);
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
        $this->belongsTo('level_id', 'Common\Models\IUserLevel', 'level_id', ['alias' => 'UserLevel']);
        $this->belongsTo('coupon_user_id', 'Common\Models\ICouponUser', 'coupon_user_id', ['alias' => 'CouponUser']);
        $this->belongsTo('address_id', 'Common\Models\IAddress', 'address_id', ['alias' => 'Address']);
        $this->belongsTo('express_corp_id', 'Common\Models\IExpressCorp', 'express_corp_id', ['alias' => 'ExpressCorp']);
        $this->hasOne('order_id', 'Common\Models\IOrderComment', 'order_id', ['alias' => 'Comment']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_order';
    }

    static public function getPkCol()
    {
        return 'order_id';
    }

    static public $attrNames = [
        'user_id' => '购买用户',
        'level_id' => '用户等级',
        'sn' => '订单号',
        'total_fee' => '订单总额',
        'total_amount' => '订单总金额',
        'total_rebate' => '买家获得返利',
        'all_rebate' => '返利总额',
        'total_discount' => '优惠总额',
        'total_coupon' => '代金券抵用',
        'express_fee' => '运费',
        'adjustment' => '价格调整',
        'receive_man' => '收货人',
        'receive_phone' => '收货人电话',
        'receive_area' => '收货区域',
        'receive_city_name' => '城市',
        'receive_postcode' => '邮编',
        'receive_address' => '收货地址',
        'express_corp_id' => '物流公司',
        'express_no' => '物流单号',
        'flag' => '订单状态',
        'create_time' => '下单时间',
        'pay_time' => '付款时间',
        'delivery_time' => '发货时间',
        'finish_time' => '完成时间',
        'refound_reason' => '申请退款原因',
        'remark'=>'买家留言'
    ];

    public static function getFlagContext($var = null)
    {
        $data = [
            -1 => '已取消',
            1 => '未付款',
            2 => '已付款',
            3 => '已发货',
            4 => '已完成',
            5 => '已评价',
        ];

        $conf = conf();
        if($conf['enable_pay']){
            $data[1] = '未确认';
            $data[2] = '已确认';
        }

        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public static function getRefoundFlagContext($var = null)
    {
        $data = [
            1 => '申请退款',
            2 => '退款完成',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public static function getCloseFlagContext($var = null)
    {
        $data = [
            1 => '系统关闭',
            2 => '用户关闭',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public static function getFinishFlagContext($var = null)
    {
        $data = [
            1 => '自动确认',
            2 => '用户确认',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public static function getPaymentMethodContext($var = null)
    {
        $data = [
            'paypal' => 'Paypal支付',
            'credit' => '信用卡支付',
            'offline' => '线下支付',
            'money' => '余额支付',
            'redsys' => 'REDSYS',
            'stripe' => 'STRIPE'
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function beforeCreate()
    {
        parent::beforeCreate();
        $this->sn = Func::makeOrderNum();
    }

    public function beforeSave()
    {
        $this->user_id = (int)$this->user_id;
        $this->level_id = (int)$this->level_id;
        $this->total_rebate = (int)$this->total_rebate;
        $this->total_discount = (int)$this->total_discount;
        $this->total_coupon = (int)$this->total_coupon;
        $this->express_fee = (int)$this->express_fee;
        $this->total_fee = (int)$this->total_fee;
        $this->adjustment = (int)$this->adjustment;
        $this->total_amount = (int)$this->total_amount;
        $this->coupon_user_id = (int)$this->coupon_user_id;
        $this->address_id = (int)$this->address_id;
        $this->express_corp_id = (int)$this->express_corp_id;
        $this->pay_flag = (int)$this->pay_flag;
        $this->refound_flag = (int)$this->refound_flag;
        $this->close_flag = (int)$this->close_flag;
    }

    public function afterCreate()
    {
        $conf = conf();
        $settings = settings();

        if ($this->address_id && $this->Address) {
            $this->Address->update_time = date('Y-m-d H:i:s');
            $this->Address->save();
        }

        if (!is_numeric($this->coupon_user_id)) {
            $this->coupon_user_id = 0;
        }

        if ($this->coupon_user_id && $this->CouponUser) {
            $this->CouponUser->use_flag = 1;
            $this->CouponUser->order_id = $this->order_id;
            $this->CouponUser->save();
        }

        IOrderLog::log([
            'order_id'=>$this->order_id,
            'action'=>'create',
            'user_id'=>$this->user_id,
            'show_flag'=>1
        ]);

    }

    /**
     * 关闭
     * @return [type] [description]
     */
    public function close($role = 'user',$admin_id=0)
    {

        if ($this->flag > 1) {
            throw new \Exception("不可关闭已付款的订单", 1);

        }

        if ($this->close_flag) {
            throw new \Exception("订单已经关闭，请勿重复操作", 1);
        }

        $this->flag = -1;
        $this->close_flag = ($role == 'user' ? 2 : 1);

        $ret = $this->save();
        if ($ret) {
            //同步关闭子订单
            if ($this->sons) {
                foreach ($this->sons as $Son) {
                    $Son->close($role);
                }
            }

            //恢复sku库存
            if ($this->skus) {
                foreach ($this->skus as $OrderSku) {

                    $OrderSku->Sku->stock = $OrderSku->Sku->stock + $OrderSku->num;
                    $OrderSku->Sku->save();
                }
            }

            //恢复代金券
            if ($this->coupon_user_id && $this->CouponUser) {
                $this->CouponUser->use_flag = 0;
                $this->CouponUser->order_id = 0;
                $this->CouponUser->save();
            }
;
            if($role=='user'){
                IOrderLog::log([
                    'order_id'=>$this->order_id,
                    'action'=>substr(strrchr(__METHOD__,':'),1),
                    'user_id'=>$this->user_id,
                    'show_flag'=>1
                ]);
            }
            else{
                IOrderLog::log([
                    'order_id'=>$this->order_id,
                    'action'=>substr(strrchr(__METHOD__,':'),1),
                    'admin_id'=>0,
                    'show_flag'=>1
                ]);
            }
            
        }
        return $ret;
    }

    /**
     * 申请退款
     * @return [type] [description]
     */
    public function requestRefound($refound_reason)
    {
        if ($this->flag < 2) {
            throw new \Exception("此订单尚未付款，不可退款", 1003);

        }
        if ($this->close_flag > 0) {
            throw new \Exception("此订单已经关闭，不可操作", 2001);

        }
        $refound_reason = trim($refound_reason);
        if (empty($refound_reason)) {
            throw new \Exception("请提供申请退款的原因", 2001);

        }
        $this->refound_flag = 1;
        $this->refound_reason = $refound_reason;

        $ret = $this->save();
        if ($ret) {
            IOrderLog::log([
                'order_id'=>$this->order_id,
                'action'=>substr(strrchr(__METHOD__,':'),1),
                'user_id'=>$this->user_id,
                'show_flag'=>1
            ]);
        }
        return $ret;
    }

    /**
     * 退款
     * @return [type] [description]
     */
    public function refound()
    {

        if ($this->flag < 2) {
            throw new \Exception("此订单尚未付款，不可退款", 1003);

        }
        if ($this->close_flag > 0) {
            throw new \Exception("此订单已经关闭，不可操作", 2001);

        }
        $this->refound_flag = 2;
        $this->refound_time = date('Y-m-d H:i:s');
        $ret = $this->save();
        if ($ret) {
            IOrderLog::log([
                'order_id'=>$this->order_id,
                'action'=>substr(strrchr(__METHOD__,':'),1),
                'admin_id'=>$this->getDI()->get('auth')->getUser()->id,
                'show_flag'=>1
            ]);
        }
        return $ret;
    }

    public function offlinePay()
    {
        if ($this->flag != 1) {
            throw new \Exception("此订单已经付款，不可重复付款", 1003);

        }
        if ($this->close_flag > 0) {
            throw new \Exception("此订单已经关闭，不可操作", 1003);

        }
        $this->payment_method = 'offline';
        $this->pay_flag = 1;
        // $this->flag = 2;
        // $this->pay_time = date('Y-m-d H:i:s');
        $ret = $this->save();
        if ($ret) {
            //同步完成子订单支付
            if ($this->sons) {
                foreach ($this->sons as $Son) {
                    $Son->offlinePay();
                }
            }

            IOrderLog::log([
                'order_id'=>$this->order_id,
                'action'=>substr(strrchr(__METHOD__,':'),1),
                'user_id'=>$this->user_id,
                'show_flag'=>1
            ]);
        }
        return $ret;
    }

    /**
     * 付款
     * @return [type] [description]
     */
    public function paid($payment_method='')
    {
        if ($this->flag > 1) {
            throw new \Exception("此订单已经付款，不可重复付款", 1003);

        }
        if ($this->close_flag > 0) {
            throw new \Exception("此订单已经关闭，不可操作", 1003);

        }

        /*
        //付款阶段不做代金券有效性的检查
         if($this->coupon_user_id){
            if($this->CouponUser && $this->CouponUser->Coupon->status!=2){
                throw new \Exception("此订单选用的代金券未生效或已经过期", 1);
            }
        }*/

        $conf = conf();
        $settings = settings();

        $this->flag = 2;
        $this->pay_flag = 1;
        $this->pay_time = date('Y-m-d H:i:s');
        if(!empty($payment_method)){
            $this->payment_method = $payment_method;
        }
        $ret = $this->save();
        if ($ret) {

            //同步完成子订单支付
            if ($this->sons) {
                foreach ($this->sons as $Son) {
                    $Son->paid($this->payment_method);
                }
            }

            if ($this->coupon_user_id && $this->CouponUser) {
                $this->CouponUser->Coupon->used_total = $this->CouponUser->Coupon->used_total + 1;
                $this->CouponUser->Coupon->order_total_amount = $this->CouponUser->Coupon->order_total_amount + $this->total_amount;
                $this->CouponUser->Coupon->save();

            }

            //完成销量计数
            if ($this->skus) {
                $spus = [];
                foreach ($this->skus as $OrderSku) {
                    if (!isset($spus[$OrderSku->spu_id])) {
                        $spus[$OrderSku->spu_id] = 0;
                    }
                    $spus[$OrderSku->spu_id] += $OrderSku->num;

                }

                foreach ($spus as $k => $v) {
                    db()->execute("UPDATE i_goods_spu SET sold_total=sold_total+$v WHERE spu_id=:id", [

                        'id' => $k
                    ]);
                }
            }

            
            if($conf['enable_mail'] && $conf['enable_order_notify'] && $settings['email_for_order_notify']){
                Mail::init()->sendContent($settings['email_for_order_notify'],'New Order Notification',$settings['content_of_order_notify']);
            }

            IOrderLog::log([
                'order_id'=>$this->order_id,
                'action'=>substr(strrchr(__METHOD__,':'),1),
                'user_id'=>$this->user_id,
                'show_flag'=>1
            ]);

        }
        return $ret;
    }

    /**
     * 发货
     * 
     * @return [type] [description]
     */
    public function delivery($data = [])
    {
        //是否启用未付款订单直接发货
        $enable_unpaid_order_delivery = (int)conf('enable_unpaid_order_delivery');
        if(!$enable_unpaid_order_delivery){
            if ($this->flag < 2 or !$this->pay_flag) {
                throw new \Exception("此订单尚未付款，不可发货", 1003);
            }
        }
        
        if ($this->close_flag > 0) {
            throw new \Exception("此订单已经关闭，不可发货", 1003);

        }

        if (empty($data['express_corp_id']) || empty($data['express_no'])) {
            throw new \Exception("请填写快递公司及快递单号", 1);

        }

        $this->express_corp_id = $data['express_corp_id'];
        $this->express_no = $data['express_no'];
        $this->flag = 3;
        $this->delivery_time = date('Y-m-d H:i:s');
        $ret = $this->save();
        if ($ret) {
            IOrderLog::log([
                'order_id'=>$this->order_id,
                'action'=>substr(strrchr(__METHOD__,':'),1),
                'admin_id'=>$this->getDI()->get('auth')->getUser()->id,
                'show_flag'=>1
            ]);
        }
        return $ret;
    }

    /**
     * 完成
     * 
     * @return [type] [description]
     */
    public function finish($role = 'user')
    {
        if ($this->flag > 3) {
            throw new \Exception("此订单已经完成，请勿重复操作", 2001);

        }
        if ($this->close_flag > 0) {
            throw new \Exception("此订单已经关闭，不可操作", 2001);

        }

        $this->finish_flag = ($role == 'user' ? 2 : 1);
        $this->flag = 4;
        $this->finish_time = date('Y-m-d H:i:s');
        $ret = $this->save();
        if ($ret) {

            //成功购买数量+1
            db()->execute("UPDATE i_user SET buy_total=buy_total+1 WHERE user_id=:user_id", ['user_id' => $this->user_id]);
                
            //如果启动了推广返利，并且是购买型推广，则交易完成是判断是否需要产生会员推荐关系
            $conf = $this->getDI()->get('conf');
            if($conf['enable_vip_rebate'] && $conf['affiliate_type']=='sale'){
                if(!$this->User->parent_id){
                    $this->User->setParent($this->share_from_user_id);
                    
                    IOrderLog::log([
                        'order_id'=>$this->order_id,
                        'action'=>'build_user_parent',
                        'user_id'=>$this->user_id,
                        'show_flag'=>0
                    ]);
                }
                
            }
            $this->makeRebate();

            IOrderLog::log([
                'order_id'=>$this->order_id,
                'action'=>substr(strrchr(__METHOD__,':'),1),
                'user_id'=>$this->user_id,
                'show_flag'=>1
            ]);
        }
        return $ret;
    }

    //执行返利
    public function makeRebate()
    {
        $conf = $this->getDI()->get('conf');
        $settings = $this->getDI()->get('settings');
        $db = db();

        if($conf['enable_vip_rebate']){
            if ($this->all_rebate) {

                $merger = trim($this->User->parent_merger, ',') . ',' . $this->user_id;
                /*$tmp_users = db()->fetchAll('SELECT user_id,name,phone FROM i_user WHERE user_id in ('.$merger.')',\Phalcon\Db::FETCH_ASSOC);
                $users = [];
                foreach($tmp_users as $v){
                    $users[$v['user_id']] = $v['name'] ? $v['name'] : $v['phone'];
                }
                unset($tmp_users);*/
    
                $merger = explode(',', $merger);
                // array_push($merger,$this->user_id);
                $merger = array_reverse($merger);
                $rebate_rates = [
                    0 => $settings['rebate_1'],
                    1 => $settings['rebate_2'],
                    2 => $settings['rebate_3'],
                    3 => $settings['rebate_4'],
                ];
    
                foreach ($merger as $k => $user_id) {
                    //返利涉及最多4层用户
                    if ($k > 3) {
                        break;
                    }
                    if ($user_id && $rebate_rates[$k]) {

                        $amount = round($this->all_rebate * $rebate_rates[$k] * 0.01);
                        $MoneyLog = new IMoneyLog;
                        $MoneyLog->assign([
                            'user_id' => $user_id,
                            'amount' => $amount,
                            'type' => 'rebate',
                            'remark' => $k ? $k . '级子属：' . ($this->User->name ? $this->User->name : $this->User->phone) : '',
                            'order_id' => $this->order_id
                        ]);
                        $MoneyLog->save();
                    }
                }

                IOrderLog::log([
                    'order_id'=>$this->order_id,
                    'action'=>substr(strrchr(__METHOD__,':'),1),
                    'user_id'=>$this->user_id,
                    'show_flag'=>0
                ]);
            }
        }

        if($this->all_rebate>0 && !$conf['enable_vip_rebate']){
            IOrderLog::log([
                'order_id'=>$this->order_id,
                'action'=>'disable_rebate',
                'user_id'=>$this->user_id,
                'show_flag'=>0
            ]);
        }
        
    }


    /**
     * 调价
     * @return [type] [description]
     */
    public function adjust($new_total_amount)
    {
        if ($this->flag != 1) {
            throw new \Exception("此订单已经付款，不可调价", 1003);

        }
        if ($this->close_flag > 0) {
            throw new \Exception("此订单已经关闭，不可调价", 1003);

        }

        if (!is_numeric($new_total_amount)) {
            throw new \Exception("输入的调整金额必须是数字", 1);
        }

        $new_total_amount = fmtPrice($new_total_amount);
        //订单原始总价
        $origin_total_amount = $this->total_fee - $this->total_discount - $this->total_coupon + $this->express_fee;
        $this->adjustment = $new_total_amount - $origin_total_amount;
        // $this->adjustment = $this->total_amount - $new_total_amount;
        $this->total_amount = $new_total_amount;
        // var_dump( $this->total_amount,$new_total_amount,$this->adjustment);exit;
        $ret = $this->save();
        if ($ret) {
            IOrderLog::log([
                'order_id'=>$this->order_id,
                'action'=>substr(strrchr(__METHOD__,':'),1),
                'admin_id'=>$this->getDI()->get('auth')->getUser()->id,
                'show_flag'=>1
            ]);
        }
        return $ret;
    }

    public function saveSkus($carts)
    {
        db()->delete('i_order_sku','order_id='.$this->order_id);
        foreach ($carts as $item) {
            $data = [
                'order_id' => $this->order_id,
                'sku_id' => $item['model']->sku_id,
                'spu_id' => $item['model']->spu_id,
                // 'price' => $item['model']->Sku->price,
                'price' => $item['model']->price,
                'num' => $item['model']->num,
                'spec_info' => $item['model']->Sku->spec_info,
                'sku_sn' => $item['model']->Sku->sku_sn,
                'rebate' => $item['rebate'],
                'discount' => $item['discount'],
                'distribution_type_id' => $item['model']->Spu->distribution_type_id,
            ];

            $OrderSku = new IOrderSku;
            $OrderSku->assign($data);
            if (!$OrderSku->save()) {
                throw new \Exception("保存订单商品失败", 1002);

            } else {
                //删除购物车信息
                $item['model']->delete();
            }
        }
    }


    static public function prepare($carts, &$data, $just_prepare = false)
    {
        $User = apiAuth();
        //涉及的店铺
        $shop_ids = [];
        //按店铺分组购物车
        $shop_data = [];
        //代金券
        $coupons = [];

        if (!$carts || count($carts) == 0 || !is_array($carts)) {
            throw new \Exception('请在购物车中选择商品', 2001);
        }

        if ($data['coupon_user_id']) {

            $coupon_user_ids = explode(',', $data['coupon_user_id']);
            foreach ($coupon_user_ids as $cu_id) {
                $coupons[$cu_id] = ICouponUser::findFirst($cu_id);
                if (!$coupons[$cu_id]) {
                    throw new \Exception("代金券不存在", 304001);

                }

                if ($coupons[$cu_id]->use_flag) {
                    throw new \Exception("您选择的代金券已经被使用", 304002);

                }
            }

        }

        //获取默认地址
        if ($data['address_id']) {
            $Address = IAddress::findFirst($data['address_id']);
        }

        if (!$Address) {
            $Address = IAddress::findFirst([
                'user_id=:user_id: AND default_flag=1',
                'bind' => ['user_id' => $User->user_id]
            ]);
        }

        if ($Address) {
            // $Address = IAddress::findFirst($data['address_id']);
            $data['address_id'] = $Address->address_id;
            $data['area_id'] = $Address->area_id;
            $data['receive_man'] = $Address->man;
            $data['receive_phone'] = $Address->phone;
            $data['receive_area'] = $Address->area;
            $data['receive_address'] = $Address->address;
            $data['receive_postcode'] = $Address->postcode;
        } else {
            $data['address_id'] = null;
            $data['area_id'] = null;
            $data['receive_man'] = null;
            $data['receive_phone'] = null;
            $data['receive_area'] = null;
            $data['receive_address'] = null;
        }

        $data['total_fee'] = 0;
        $data['total_amount'] = 0;
        $data['express_fee'] = 0;
        $data['total_discount'] = 0;
        $data['total_rebate'] = 0;
        $data['all_rebate'] = 0;
        $data['user_id'] = $User->user_id;
        $data['level_id'] = $User->level_id;

        $cart_models = [];

        $weights = [];  //各店铺的商品总重量

        //计算订单商品总价、总优惠、总返利        
        foreach ($carts as $cart_id) {
            $Cart = ICart::findFirst($cart_id);
            if (!$Cart) {
                throw new \Exception("购物车信息不存在", 302001);

            }

            if ($Cart->Spu->status < 0 or $Cart->Spu->remove_flag > 0) {
                throw new \Exception("购物车中存在已经下架的商品，请刷新购物车后重新下单", 302002);

            }

            $shop_ids[] = $Cart->shop_id;
            if (!isset($shop_data[$Cart->shop_id])) {
                $shop_data[$Cart->shop_id] = [
                    'shop_id' => $Cart->shop_id,
                    'carts' => [],
                    'cart_models' => [],
                    // 'address_id'=>$Address->address_id,
                    'total_fee' => 0,
                    'total_amount' => 0,
                    'express_fee' => 0,
                    'total_discount' => 0,
                    'total_rebate' => 0,
                    'coupon_user_id' => '',
                    'total_coupon' => 0,
                    'user_id' => $User->user_id,
                    'level_id' => $User->level_id,
                    'coupons' => []
                ];
            }

            $shop_data[$Cart->shop_id]['carts'][] = $cart_id;

            if (!isset($cart_models[$cart_id])) {
                $cart_models[$cart_id] = [];
            }
            $cart_models[$cart_id]['model'] = $Cart;

            //如果是抢购商品，使用抢购价格
            if ($Cart->sale_spu_id) {
                if ($Cart->SaleSpu->FlashSale->status == 3) {
                    throw new \Exception("订单中存在已经结束抢购的抢购商品", 305002);

                }

                if ($Cart->SaleSpu->FlashSale->status == 1) {
                    throw new \Exception("订单中存在尚未开始抢购的抢购商品", 305003);

                }


                if ($Cart->SaleSpu->per_limit > 0 and $Cart->num > $Cart->SaleSpu->per_limit) {
                    throw new \Exception("订单中的抢购商品超出了最多可抢购的数量限制", 305004);
                }

                $data['total_fee'] += $Cart->SaleSpu->sale_price * $Cart->num;
                $shop_data[$Cart->shop_id]['total_fee'] += $Cart->SaleSpu->sale_price * $Cart->num;
            } else {
                // $data['total_fee'] += $Cart->Sku->price * $Cart->num;
                // $shop_data[$Cart->shop_id]['total_fee'] += $Cart->Sku->price * $Cart->num;
                $data['total_fee'] += $Cart->price * $Cart->num;
                $shop_data[$Cart->shop_id]['total_fee'] += $Cart->price * $Cart->num;
                
            }

            $rebates = $Cart->Spu->getFmtRebates(true, false);
            $all_rebate= $Cart->Spu->getAllRebate($User->level_id);
            $shop_data[$Cart->shop_id]['all_rebate'] += $all_rebate * $Cart->num;
            $data['all_rebate'] += $all_rebate * $Cart->num;
  
            $rebate = $rebates[$User->level_id] ? $rebates[$User->level_id]['rebate'] : 0;
            $cart_models[$cart_id]['rebate'] = $rebate * $Cart->num;
            $data['total_rebate'] += $cart_models[$cart_id]['rebate'];
            $shop_data[$Cart->shop_id]['total_rebate'] += $cart_models[$cart_id]['rebate'];

            $discounts = $Cart->Spu->getFmtDiscounts(true);

            $discount = isset($discounts[$User->level_id]) ? $discounts[$User->level_id] : 0;
            $cart_models[$cart_id]['discount'] = $discount * $Cart->num;
            $data['total_discount'] += $cart_models[$cart_id]['discount'];
            $shop_data[$Cart->shop_id]['total_discount'] += $cart_models[$cart_id]['discount'];

            if (!$just_prepare) {
                $shop_data[$Cart->shop_id]['cart_models'][$cart_id] = $cart_models[$cart_id];
            }

            //累加重量
            $shop_data[$Cart->shop_id]['weight'] = ($Cart->Spu->weight * $Cart->num) + $shop_data[$Cart->shop_id]['weight'];
            // //累加长度
            // $shop_data[$Cart->shop_id]['length'] = ($Cart->Spu->length * $Cart->num) + $shop_data[$Cart->shop_id]['length'];
            // //累加宽度
            // $shop_data[$Cart->shop_id]['width'] = ($Cart->Spu->width * $Cart->num) + $shop_data[$Cart->shop_id]['width'];
            // //累加高度
            // $shop_data[$Cart->shop_id]['height'] = ($Cart->Spu->height * $Cart->num) + $shop_data[$Cart->shop_id]['height'];
        }

        $shop_ids = array_unique($shop_ids);

        //应用代金券
        if (!empty($coupons)) {
            foreach ($coupons as $CouponUser) {
                if ($CouponUser->Coupon->amount) {
                    $data['total_coupon'] = $data['total_coupon'] + $CouponUser->Coupon->amount;
                    $shop_data[$Cart->shop_id]['total_coupon'] += $CouponUser->Coupon->amount;
                    $shop_data[$Cart->shop_id]['coupon_user_id'] = $CouponUser->coupon_user_id;
                }
            }

        }

        $data['total_amount'] = (int)$data['total_fee'] - (int)$data['total_discount'] - (int)$data['total_coupon'];
        $shop_data[$Cart->shop_id]['total_amount'] = (int)$shop_data[$Cart->shop_id]['total_fee'] - (int)$shop_data[$Cart->shop_id]['total_discount'] - (int)$shop_data[$Cart->shop_id]['total_coupon'];

        //计算运费
        $express_fee_fp = fopen(SITE_PATH.'/logs/express_fee.txt','a+');
        fputs($express_fee_fp,'------------------------------'.PHP_EOL); 
        fputs($express_fee_fp,json_encode($shop_data).PHP_EOL);
        fputs($express_fee_fp,'carts:'.json_encode($carts).PHP_EOL);
        fputs($express_fee_fp,'area_id:'.$data['area_id'].PHP_EOL);
        
        $conf = conf();
        if ($conf['delivery_fee_type'] != 'default') {
            foreach ($shop_ids as $shop_id) {
                fputs($express_fee_fp,'shop_id:'.$shop_id.PHP_EOL);
                $delivery_free_limit = db()->fetchColumn('SELECT delivery_free_limit FROM i_shop WHERE shop_id=:shop_id', ['shop_id' => $shop_id]);
                $delivery_free_limit = $delivery_free_limit ? $delivery_free_limit : 0;
                //商铺的订单金额是否超过免邮限额
                if (empty($delivery_free_limit) or $shop_data[$shop_id]['total_amount'] < $delivery_free_limit) {

                    fputs($express_fee_fp,'area_id:'.$data['area_id'].PHP_EOL);
                    if ($conf['delivery_fee_type'] == 'measure') {
                        
                        $express_fee = IDeliveryFeeMeasure::getFeeAmount($data['area_id'], $shop_id, $shop_data[$shop_id]['weight']);
                        fputs($express_fee_fp,'type:measure'.PHP_EOL);
                        fputs($express_fee_fp,'express_fee:'.$express_fee.PHP_EOL);

                    } elseif ($conf['delivery_fee_type'] == 'percent') {
                        
                        $express_fee = IDeliveryFeeMeasure::getFeeAmount($data['area_id'], $shop_id, $shop_data[$shop_id]['total_amount']);
                        fputs($express_fee_fp,'type:percent'.PHP_EOL);
                        fputs($express_fee_fp,'express_fee:'.$express_fee.PHP_EOL);
                    }
                    else{
                        fputs($express_fee_fp,'type:other'.PHP_EOL);
                    }

                    $data['express_fee'] += (int)$express_fee;
                    $data['total_amount'] += (int)$data['express_fee'];
                    $shop_data[$shop_id]['express_fee'] = $express_fee;
                } else {
                    fputs($express_fee_fp,'IT IS FREE:'.$delivery_free_limit.PHP_EOL);
                    $shop_data[$shop_id]['express_fee'] = 0;
                }

                fputs($express_fee_fp,'shop[express_fee]:'.$shop_data[$shop_id]['express_fee'] .PHP_EOL);
            }
        } else {
            foreach ($shop_ids as $shop_id) {
                fputs($express_fee_fp,'shop_id:'.$shop_id.PHP_EOL);
                $delivery_free_limit = db()->fetchColumn('SELECT delivery_free_limit FROM i_shop WHERE shop_id=:shop_id', ['shop_id' => $shop_id]);
                $delivery_free_limit = $delivery_free_limit ? $delivery_free_limit : 0;
                //商铺的订单金额是否超过免邮限额
                if (empty($delivery_free_limit) or $shop_data[$shop_id]['total_amount'] < $delivery_free_limit) {

                    $express_fee = IDeliveryFee::getFeeAmount($data['area_id'], $shop_id);
                    $data['express_fee'] += $express_fee;
                    $data['total_amount'] += (int)$data['express_fee'];
                    $shop_data[$shop_id]['express_fee'] = $express_fee;

                    fputs($express_fee_fp,'express_fee:'.$express_fee.PHP_EOL);

                } else {
                    fputs($express_fee_fp,'IT IS FREE:'.$delivery_free_limit.PHP_EOL);
                    $shop_data[$shop_id]['express_fee'] = 0;
                }

                fputs($express_fee_fp,'shop[express_fee]:'.$data['express_fee'] .PHP_EOL);
            }
        }

        fputs($express_fee_fp,'total_express_fee:'.$shop_data[$shop_id]['express_fee'] .PHP_EOL);

        fclose($express_fee_fp);


        //订单准备阶段，须返回可用代金券
        if ($just_prepare) {
            //是否获取可用代金券
            $data['coupons'] = [];
            $today = date('Y-m-d');
            foreach ($shop_ids as $shop_id) {
                $tmp_coupons = db()->fetchAll('SELECT cu.coupon_user_id,coupon_name,amount,start_time,end_time,min_limit,with_rebate,with_discount,cu.shop_id FROM i_coupon_user AS cu
                    JOIN i_coupon as c 
                        ON cu.coupon_id=c.coupon_id
                    WHERE cu.shop_id=:shop_id AND c.status=2 AND cu.user_id=:user_id AND cu.use_flag=0', \Phalcon\Db::FETCH_ASSOC, ['shop_id' => $shop_id, 'user_id' => $User->user_id]);

                foreach ($tmp_coupons as $k => $v) {
                    if (empty($v['min_limit']) || ($v['min_limit'] && $data['total_amount'] >= $v['min_limit'])) {
                        $v['amount'] = fmtMoney($v['amount']);
                        $data['coupons'][] = $v;
                        $shop_data[$shop_id]['coupons'][] = $v;
                    }
                }
                unset($tmp_coupons);
            }

        }
        $data['shop_data'] = $shop_data;

        return $cart_models;
    }

    static public function add($carts, $data)
    {
        $conf = conf();
        $settings = settings();

        $cart_models = self::prepare($carts, $data);

        if($conf['enable_min_order_amount'] && $settings['min_order_amount']>0){
            $min_order_amount = fmtPrice($settings['min_order_amount']);
            // var_dump($data['total_amount']);exit;
            if($min_order_amount>$data['total_amount']){
                throw new \Exception('订单总额不满足店铺的最小下单金额', 303002);
            }
        }

        $shop_total = count($data['shop_data']);
        if ($shop_total == 1) {
            $shop_data = array_shift($data['shop_data']);
            $Order = new self;
            $data['shop_id'] = $shop_data['shop_id'];
            $Order->assign($data);
            if ($Order->save()) {
                $Order->saveSkus($cart_models);
                return $Order;
            } else {
                throw new \Exception($Order->getErrorMsg(), 2001);

            }
        } else {
            $Order = new self;
            $data['shop_id'] = 0;
            $Order->assign($data);
            if ($Order->save()) {

                foreach ($data['shop_data'] as $shop_data) {
                    $ShopOrder = new self;
                    $shop_data['parent_id'] = $Order->order_id;
                    $shop_data['shop_id'] = $shop_data['shop_id'];
                    $shop_data['address_id'] = $data['address_id'];
                    $shop_data['area_id'] = $data['area_id'];
                    $shop_data['receive_man'] = $data['receive_man'];
                    $shop_data['receive_phone'] = $data['receive_phone'];
                    $shop_data['receive_area'] = $data['receive_area'];
                    $shop_data['receive_city_name'] = $data['receive_city_name'];
                    $shop_data['receive_address'] = $data['receive_address'];
                    $shop_data['share_from_user_id'] = $data['share_from_user_id'];
                    $ShopOrder->assign($shop_data);
                    if ($ShopOrder->save()) {
                        $ShopOrder->saveSkus($shop_data['cart_models']);

                    }
                }
                return $Order;
            } else {
                throw new \Exception($Order->getErrorMsg(), 2001);

            }
        }

        return $Order;
    }

    static public function renew($order_id, $carts, $data)
    {
        $cart_models = self::prepare($carts, $data);

        $shop_total = count($data['shop_data']);
        if ($shop_total == 1) {
            $shop_data = array_shift($data['shop_data']);
            $Order = self::findFirst($order_id);
            $data['shop_id'] = $shop_data['shop_id'];
            $Order->assign($data);
            if ($Order->save()) {
                $Order->saveSkus($cart_models);
                return $Order;
            } else {
                throw new \Exception($Order->getErrorMsg(), 2001);

            }
        } else {
            $new_sub_order_ids = [];
            $old_sub_order_ids = [];

            $old_sub_order_arr = db()->fetchAll("SELECT order_id FROM i_order WHERE parent_id=:parent_id",\Phalcon\Db::FETCH_ASSOC,[
                'parent_id'=>$order_id
            ]);
            if($old_sub_order_arr){
                foreach($old_sub_order_arr as $v){
                    $old_sub_order_ids[] = $v['order_id'];
                }
            }

            $Order = self::findFirst($order_id);            
            $data['shop_id'] = 0;
            $Order->assign($data);
            if ($Order->save()) {

                foreach ($data['shop_data'] as $shop_data) {
                    $ShopOrder = self::findFirst([
                        'parent_id=:parent_id: AND shop_id=:shop_id:',
                        'bind'=>[
                            'parent_id'=>$Order->order_id,
                            'shop_id'=>$shop_data['shop_id']
                        ]
                    ]);
                    if($ShopOrder){

                        $new_sub_order_ids[] = $ShopOrder->order_id;

                        $shop_data['parent_id'] = $Order->order_id;
                        $shop_data['shop_id'] = $shop_data['shop_id'];
                        $shop_data['address_id'] = $data['address_id'];
                        $shop_data['area_id'] = $data['area_id'];
                        $shop_data['receive_man'] = $data['receive_man'];
                        $shop_data['receive_phone'] = $data['receive_phone'];
                        $shop_data['receive_area'] = $data['receive_area'];
                        $shop_data['receive_city_name'] = $data['receive_city_name'];
                        $shop_data['receive_address'] = $data['receive_address'];
                        $shop_data['share_from_user_id'] = $data['share_from_user_id'];
                        $ShopOrder->assign($shop_data);
                        if ($ShopOrder->save()) {
                            $ShopOrder->saveSkus($shop_data['cart_models']);

                        }
                    }
                    
                }

                //删除掉new_sub_order_ids中不存在的订单数据
                $del_order_ids = array_diff($old_sub_order_ids,$new_sub_order_ids);
                if($del_order_ids){
                    foreach($del_order_ids as $v){
                        $DelOrder = self::findFirst($v);
                        if($DelOrder){
                            $DelOrder->delete();
                        }
                    }
                }

                return $Order;
            } else {
                throw new \Exception($Order->getErrorMsg(), 2001);

            }
        }

        return $Order;

    }

    public function getGoodsTotal()
    {
        $ret = 0;

        $ret = IOrderSku::count([
            'order_id=:order_id:',
            'bind' => [
                'order_id' => $this->order_id
            ]
        ]);

        return $ret;
    }

    public function getSkuNames(){
        $ret = [];
        if($this->skus){
            foreach($this->skus as $Sku){
                $ret[] = $Sku->Spu->spu_name;
            }
        }

        return implode(',',$ret);
    }

}
