<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Common\Libs\Func;

class IBuyerGoods extends Model
{

    public $buyer_goods_id;

    public $name;

    public $sn;

    public $cover;

    public $pics;

    public $price;

    public $num;

    public $unit;

    public $buyer_id;

    public $shop_id;

    public $create_time;

    public $update_time;

    public $status;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->keepSnapShots(true);
        $this->setSource("i_buyer_goods");
        $this->belongsTo('buyer_id','Common\Models\IBuyer','buyer_id',['alias' => 'Buyer']);
        $this->belongsTo('shop_id', 'Common\Models\IShop', 'shop_id', ['alias' => 'Shop']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_buyer_goods';
    }

    static public function getPkCol(){
        return 'buyer_goods_id';
    }

    static public $attrNames = [
        'name'=>'商品名称',
        'sn'=>'货号',
        'cover'=>'商品图片',
        'pics'=>'商品相册',       
        'price'=>'商品价格',     
        'status'=>'状态',
        'shop_id'=>'店铺',
        'buyer_id'=>'采购员'
    ];

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);

        /* if($cols_length==0 OR in_array('name',$cols)){
            $validator->add(
                'name',
                new PresenceOf()
            );
        }

        if($cols_length==0 OR in_array('sn',$cols)){
            $validator->add(
                'sn',
                new PresenceOf()
            );
        } */

        if($cols_length==0 OR in_array('cover',$cols)){
            $validator->add(
                'cover',
                new PresenceOf()
            );
        }

        if($cols_length==0 OR in_array('price',$cols)){
            $validator->add(
                'price',
                new PresenceOf()
            );
        }
           

        return $validator;
    }

    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator);        
    }

    public static function getStatusContext($var = null) {
        $data = [
            -1  => '忽略',
            1   => '待处理',
            2   => '已入库',
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

        $this->shop_id = $this->Buyer->shop_id;
        if(!$this->shop_id){
            $this->shop_id = 1;
        }
        
    }

    public function beforeSave(){

        $this->buyer_id = (int)$this->buyer_id;
        $this->price = (int)$this->price;        
        $this->shop_id = $this->shop_id ? (int)$this->shop_id : 1;
    }

    public function getFmtPics(){
        $ret = [];
        // $host = $host ? $host : 'http://'.$this->getDi()->get('request')->getHttpHost();
        if($this->pics){
            $ret = explode(',', $this->pics);
            foreach($ret as $k=>$v){
                $ret[$k] = Func::staticPath($v);
            }
        }
        return $ret;
    }

    public function getFmtCover(){
        $ret = '';
        if($this->cover){
            $ret = Func::staticPath($this->cover);
        }
        return $ret;
    }


}
