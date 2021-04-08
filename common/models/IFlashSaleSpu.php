<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class IFlashSaleSpu extends Model
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
    public $sale_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $spu_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sale_price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sale_stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $per_limit;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_flash_sale_spu");
        $this->belongsTo('sale_id', 'Common\Models\IFlashSale', 'sale_id', ['alias' => 'FlashSale']);
        $this->belongsTo('spu_id', 'Common\Models\IGoodsSpu', 'spu_id', ['alias' => 'Spu']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_flash_sale_spu';
    }

    public function validation() {

        $validator = self::validator();
        
        $validator->add(
            ['sale_id','spu_id'],
            new Uniqueness()
        );

        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('spu_id',$cols)){
            $validator->add(
                'spu_id',
                new PresenceOf()
            );
        }
        
        if($cols_length==0 OR in_array('sale_price',$cols)){
            $validator->add(
                'sale_price',
                new PresenceOf()
            );
        }
        
        if($cols_length==0 OR in_array('sale_stock',$cols)){
            $validator->add(
                'sale_stock',
                new PresenceOf()
            );
        }

        // var_dump($validator->getValue('spu_id'));exit;
       
        return $validator;
    }

    static public $attrNames = [
        'sale_price'=>'抢购价',
        'sale_stock'=>'抢购数量',
        'per_limit'=>'每人允许购买数量'
    ];

    public function beforeSave(){
        $this->sale_id = (int)$this->sale_id;
        $this->spu_id = (int)$this->spu_id;
        $this->sale_price = (int)$this->sale_price;
        $this->sale_stock = (int)$this->sale_stock;
        $this->per_limit = (int)$this->per_limit;
    }

    
}
