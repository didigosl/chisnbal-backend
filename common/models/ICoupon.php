<?php

namespace Common\Models;

use Common\Libs\Func;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\Numericality;

class ICoupon extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $coupon_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $coupon_name;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $sn;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $amount;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $start_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $end_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $min_limit;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $with_rebate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $with_discount;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $target;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $send_total;


    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $used_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $order_total_amount;

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
        $this->setSource("i_coupon");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_coupon';
    }

    static public function getPkCol(){
        return 'coupon_id';
    }

    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('coupon_name',$cols)){
            $validator->add(
                'coupon_name',
                new PresenceOf()
            );
        }
        
        if($cols_length==0 OR in_array('sn',$cols)){
            $validator->add(
                'sn',
                new PresenceOf()
            );
        }
        
        if($cols_length==0 OR in_array('amount',$cols)){
             $validator->add(
                'amount',
                new PresenceOf()
            );
        }  

        if($cols_length==0 OR in_array('amount',$cols)){
             $validator->add(
                'amount',
                new Between([
                    'minimum'=>1,
                    'maximum'=>9999999999,
                    "message" => "代金券金额必须大于0.01元",
                ])
            );
        }   

        if($cols_length==0 OR in_array('min_limit',$cols)){
             $validator->add(
                'min_limit',
                new Between([
                    'minimum'=>1,
                    'maximum'=>9999999999,
                    "message" => "满减金额必须大于0.01元",
                    'allowEmpty' => true,
                ])
            );
        } 

        if($cols_length==0 OR in_array('start_time',$cols)){
             $validator->add(
                'start_time',
                new PresenceOf()
            );
        }  

        if($cols_length==0 OR in_array('end_time',$cols)){
             $validator->add(
                'end_time',
                new PresenceOf()
            );
        }             

        return $validator;
    }

    static public $attrNames = [
        'sn'=>'序列号',
        'coupon_name'=>'说明',
        'amount'=>'抵用金额',
        'send_total'=>'发放总量',
        'used_total'=>'使用次数',
        'user_total'=>'使用人数',
        'order_total_amount'=>'总订单金额',
        'start_time'=>'生效时间',   
        'end_time'=>'失效时间',
        'min_limit'=>'订单最低金额',
        'with_rebate'=>'与返利共享',
        'with_discount'=>'与优惠共享',
        'create_time'=>'创建时间',
        'target'=>'发放目标',
        'status'=>'代金券状态'
    ];

    public static function getStatusContext($var = null) {
        $data = [
            1  => '未生效',
            2  => '有效',
            3  => '过期'
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function beforeSave(){
        $this->amount = (int)$this->amount;
        $this->min_limit = (int)$this->min_limit;
        $this->with_rebate = (int)$this->with_rebate;
        $this->with_discount = (int)$this->with_discount;
    }

    public function beforeCreate(){
        parent::beforeCreate();
        $this->used_total = 0;
        $this->user_total = 0;
        $this->order_total_amount = 0;
        $this->status = 1;
        $this->shop_id = $this->getDi()->get('auth')->getShopId();
    }

    public function afterCreate(){

        $db = $this->getDi()->get('db');

        //发放优惠券
        $target = json_decode($this->target);
        if($target){
            if($target->type == 'level'){

                $level_ids = implode(',', $target->list);

                $users = $db->fetchAll('SELECT user_id FROM i_user WHERE level_id in ('.$level_ids.')',\Phalcon\Db::FETCH_ASSOC);

                $send_total = 0;
                foreach($users as $v){
                    $CouponUser = new ICouponUser;
                    $CouponUser->assign([
                        'coupon_id'=>$this->coupon_id,
                        'user_id'=>$v['user_id'],
                    ]);
                    if($CouponUser->save()){
                        $send_total++;
                    }
                    else{
                        throw new \Exception("发放优惠券失败", 1);
                        
                    }
                }
            }
            elseif($target->type == 'user'){
                if($target->list){
                    $send_total = 0;
                    foreach($target->list as $v){
                        $CouponUser = new ICouponUser;
                        $CouponUser->assign([
                            'coupon_id'=>$this->coupon_id,
                            'user_id'=>$v,
                        ]);
                        if($CouponUser->save()){
                            $send_total++;
                        }
                        else{
                            throw new \Exception("发放优惠券失败", 1);
                            
                        }
                    }
                }
            }

            $this->send_total = $send_total;
            $this->save();
        }
    }

    public function getLimitText(){
        $ret = [];
        if($this->min_limit){
            $ret[] = "满".fmtMoney($this->min_limit).'可用';
        }
        if($this->with_rebate){
            $ret[] = '与返利共享';
        }
        if($this->with_discount){
            $ret[] = '与优惠共享';
        }
        return $ret;
    }

    public function getFmtTarget(){
        $ret = [];
        $target = json_decode($this->target);
        
        $db = $this->getDi()->get('db');

        if(is_array($target->list)){
            $list = implode(',',$target->list);
            if($target->type=='level'){
                $ret['type'] = '限定会员等级';
                $ret['list'] = [];
                $levels = $db->fetchAll('SELECT level_id,level_name FROM i_user_level WHERE level_id in ('.$list.')');
                if($levels){
                    foreach($levels as $v){
                        $ret['list'][] = $v['level_name'];
                    }
                    $ret['list'] = implode(',',$ret['list']);
                }
            }
            elseif($target->type=='user'){
                $ret['type'] = '特定会员';
                $users = $db->fetchAll('SELECT user_id,name,phone FROM i_user WHERE user_id in ('.$list.')');
                if($users){
                    foreach($users as $v){
                        $ret['list'][] = $v['name'].'('.$v['phone'].')';
                    }

                    $ret['list'] = implode(',',$ret['list']);
                    
                }
            }
        }

        return implode('：',$ret);
    }

}
