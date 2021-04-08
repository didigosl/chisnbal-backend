<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Exception;

class IDeliveryFee extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $fee;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $area_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $level;

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
    public $create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_delivery_fee");
        $this->belongsTo('area_id', 'Common\Models\IArea', 'area_id', ['alias' => 'Area']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_delivery_fee';
    }

    public function validation() {
        $validator = self::validator();        
        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);

        if($cols_length==0 OR in_array('area_id',$cols)){
            $validator->add(
                'area_id',
                new PresenceOf([
                    "message" => "必须指定地区",
                ])
            );
        }

        if($cols_length==0 OR in_array('area_id',$cols)){
            $validator->add(
                ['area_id','shop_id'],
                new Uniqueness([
                    "message" => "此地区已经设置过运费了",
                ])
            );
        }
        
        if($cols_length==0 OR in_array('fee',$cols)){
             $validator->add(
                'fee',
                new PresenceOf()
            );
        }  

        /*if($cols_length==0 OR in_array('fee',$cols)){
             $validator->add(
                'fee',
                new Between([
                    'minimum'=>1,
                    "message" => "运费金额必须大于0.01元",
                ])
            );
        }   */
       

        return $validator;
    }

    
    static public $attrNames = [
        'area_id'=>'地区',
        'fee'=>'运费',
    ];

     public function beforeCreate(){
        parent::beforeCreate();
        $this->shop_id = $this->getDi()->get('auth')->getShopId();
    }

    public function beforeSave(){
        $this->fee = (int)$this->fee;
        $this->area_id = (int)$this->area_id;
        $this->level = (int)$this->level;
    }

    //获取运费
    static function getFeeAmount($area_id,$shop_id=1){

        $ret = 0;
        $Area = IArea::findFirst($area_id);
        if(!$Area){
            $ret = 0;
            // throw new \Exception("地区信息不正确", 2001);
            
        }
        else{
            $parents = $Area->getParents();
            // var_dump($parents);exit;
            $parents = implode(',',$parents);
            $db = \Phalcon\Di::getDefault()->get('db');
            // $settings = \Phalcon\Di::getDefault()->get('settings');
            $default_fee = $db->fetchColumn('SELECT fee FROM i_delivery_fee WHERE shop_id=:shop_id AND area_id=0 ',['shop_id'=>$shop_id]);
            $default_fee = $default_fee ? $default_fee : 0;
            
            $fee = $db->fetchColumn('SELECT fee FROM i_delivery_fee WHERE shop_id=:shop_id AND area_id in ('.$parents.')  ORDER BY level DESC',['shop_id'=>$shop_id]);
            if($fee){
                $ret = $fee;
            }
            else{
                // $ret = $settings['delivery_fee'];
                $ret = $default_fee;
            }
        }
        

        return $ret;
        
    }

}
