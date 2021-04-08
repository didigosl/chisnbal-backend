<?php

namespace Common\Models;

class IUserLevel extends Model
{

    public $level_id;

    public $level_name;

    public $seq;

    public $price;

    public $discount;

    public $discount_type;

    public $create_time;

    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_user_level");
        $this->hasMany('level_id', 'Common\Models\IDiscountCategory', 'level_id', ['alias' => 'IDiscountCategory']);
        $this->hasMany('level_id', 'Common\Models\ISebateCategory', 'level_id', ['alias' => 'ISebateCategory']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_user_level';
    }

    static public function getPkCol(){
        return 'level_id';
    }

    static public $attrNames = [
        'level_name'=>'等级名称',
        'price'=>'购买价格',
        'discount_type'=>'优惠方式',
        'discount'=>'优惠',
        'system_flag'=>'系统预置',
    ];

    public function beforeSave(){
        $this->seq = (int)$this->seq;
        $this->price = (int)$this->price; 
        $this->discount_type = (int)$this->discount_type; 
        $this->discount = trim($this->discount);

        if(!empty($this->discount) && $this->discount_type!=1 && $this->discount_type!=2){
            throw new \Exception('必须指定优惠类型');
        }

        if(!empty($this->discount)){

            if(!preg_match('/^[0-9\.]+$/',$this->discount)){
                throw new \Exception('优惠数值必须是数字');
            }

            if($this->discount_type==1){
                $this->discount = fmtPrice($this->discount);
            }
        }

        
    }

    public function beforeDelete(){
        if($this->system_flag){
            throw new \Exception('不可删除系统会员等级');
        }

        $check_user = db()->fetchColumn("SELECT count(1) FROM i_user WHERE level_id=:level_id AND remove_flag=0",[
            'level_id'=>$this->level_id
        ]);

        if($check_user>0){
            throw new \Exception('此会员等级存在用户数据，不可删除');
        }
    }

    public static function getDiscountTypeContext($var = null) {
        $data = [
            1   => '固定金额优惠',
            2   => '百分比优惠',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function getUserTotal(){
        $db = $this->getDi()->get('db');
        $total = $db->fetchColumn('SELECT count(1) FROM i_user WHERE level_id=:level_id',['level_id'=>$this->level_id]);
        return $total;
    }

}
