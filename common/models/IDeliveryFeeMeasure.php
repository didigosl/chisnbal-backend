<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Exception;


class IDeliveryFeeMeasure extends Model
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
    public $basic_fee;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $basic_measure;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $step_fee;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $step_measure;

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
        $this->setSource("i_delivery_fee_measure");
        $this->belongsTo('area_id', 'Common\Models\IArea', 'area_id', ['alias' => 'Area']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_delivery_fee_measure';
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
        
        if($cols_length==0 OR in_array('basic_fee',$cols)){
             $validator->add(
                'basic_fee',
                new PresenceOf()
            );
        }  

        return $validator;
    }

    static public $attrNames = [
        'area_id'=>'地区',
        'basic_fee'=>'基础运费',
        'step_fee'=>'递增运费',
    ];


    //获取运费
    static function getFeeAmount($area_id,$shop_id=1,$weight=0){
        $ret = 0;
        $conf = conf();

        $default_fee = db()->fetchOne('SELECT * FROM i_delivery_fee_measure WHERE shop_id=:shop_id AND area_id=0 ',\Phalcon\Db::FETCH_ASSOC,['shop_id'=>$shop_id]);
        $default_fee = $default_fee ? $default_fee : 0;

        $Area = IArea::findFirst($area_id);
        if($Area){
            $parents = $Area->getParents();
            $parents = implode(',',$parents);

            $fee = db()->fetchOne('SELECT m.* FROM i_delivery_fee_measure as m join i_area as a on m.area_id=a.area_id WHERE shop_id=:shop_id AND m.area_id in ('.$parents.')  ORDER BY a.level DESC',\Phalcon\Db::FETCH_ASSOC,['shop_id'=>$shop_id]);
            $area_fee = $fee;
            if(!$fee){
                $fee = $default_fee;
            }
            
        }
        else{
            $fee = $default_fee;
        } 

        if($conf['delivery_fee_type']=='measure'){               

            $weight = ceil($weight);
        
            if($weight>1){
                $ret = $fee['basic_fee'] + $fee['step_fee'] * ($weight-1);

                if($ret<0){
                    $ret = 0;
                }
            }
            else{
                $ret = $fee['basic_fee'];
            }
        }
        elseif($conf['delivery_fee_type']=='percent'){
            if($weight>$fee['basic_measure']){
                if($fee['step_measure']>0){
                    $ret = $fee['basic_fee'] + $fee['step_fee'] * ceil(ceil($weight-$fee['basic_measure'])/$fee['step_measure']);
                    if($ret<0){
                        $ret = 0;
                    }
                }
                else{
                    $ret = $fee['basic_fee'];
                }
                
            }
            else{
                $ret = $fee['basic_fee'];
            }

            file_put_contents(SITE_PATH.'/logs/fee.txt',"fee:\n".var_export($fee,true)."\n area_fee:".var_export($area_fee,true)." \n weight:".var_.$weight."\n parents:".$parents);
        }
          
        
        return $ret;
        
    }
}
