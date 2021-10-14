<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;


class IGoodsSku extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $sku_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $spu_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $sku_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $spec_info;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $price;

    public $weigh_flag;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $default_flag;

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
     * @Column(type="string", nullable=false)
     */
    public $update_time;
    public $num;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_goods_sku");
        $this->hasMany('sku_id', 'Common\Models\ICart', 'sku_id', ['alias' => 'carts']);
        $this->hasMany('sku_id', 'Common\Models\IOrderSku', 'sku_id', ['alias' => 'IOrderSku']);
        $this->belongsTo('spu_id', 'Common\Models\IGoodsSpu', 'spu_id', ['alias' => 'Spu']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_goods_sku';
    }

    static public function getPkCol(){
        return 'sku_id';
    }

    static public $attrNames = [
        'spec_info'=>'规格信息',
        'stock'=>'库存',
        'price'=>'价格',
        'weigh_flag'=>'需要称重出售',
        'status'=>'状态'
    ];

    public function validation() {

        $validator = new Validation();

        if(!empty($this->sku_sn)){
            $validator->add(
                'sku_sn',
                new StringLength([
                    'max' => 120,
                    'min' => 1,
                ])
            );
        }
       
        return $this->validate($validator);
        
    }

    public function beforeCreate(){
        parent::beforeCreate();
        $this->shop_id = $this->Spu->shop_id;
    }

    public function beforeSave(){
        $this->spu_id = (int)$this->spu_id;
        $this->stock = (int)$this->stock;
        $this->price = (int)$this->price;
        $this->status = (int)$this->status;

        if(!$this->sku_sn){
            $this->sku_sn = $this->Spu->sn;
            $spec_info = explode(',',$this->spec_info);
            foreach($spec_info as $spec){
                $spec = explode(':',$spec);
                $this->sku_sn = $this->sku_sn.'-'.$spec[1];

            }
            
        }
    }

    
    public function getSpecMode(){
        $ret = '';
        $specs = $this->Spu->getFmtSpecData();
        $fmtSpecs = [];
        foreach ($specs as $v) {
            $fmtSpecs[$v['spec_name']] = $v['specs'];
        }

        $mode = []; //记录当前sku的各个规格mode值
        if ($this->spec_info !== 'default') {
            $spec_info = explode(',', $this->spec_info);
            foreach ($spec_info as $spec) {
                $spec = explode(':', $spec);
                // var_dump($spec);exit;
                //将sku的规格值和spec的规格值比较，确定mode值
                if (is_array($fmtSpecs[$spec[0]])) {
                    foreach ($fmtSpecs[$spec[0]] as $v) {
                        if ($v['value'] == $spec[1]) {
                            $mode[] = $v['mode'];
                        }
                    }
                }

            }
        }

        $ret = sprintf("%06s", implode('', $mode));

        return $ret;
    }

}
