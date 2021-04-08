<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;

class IDistributionType extends Model
{

    public $distribution_type_id;

    public $name;

    public $seq;

    public $shop_id;

    public $delete_flag;

    public $create_time;

    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_distribution_type");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_distribution_type';
    }

    static public function getPkCol(){
        return 'distribution_type_id';
    }

    static public $attrNames = [
        'name'=>'名称',
    ];

    public function validation() {
        $validator = new Validation();
        $validator->add(
            'name',
            new PresenceOf()
        );
        $validator->add(
            ['name'],
            new Uniqueness([
                'message'=>'##已经存在同名的名称'
            ])
        );

        if(!empty($this->label_name)){
            $validator->add(
                'name',
                new StringLength([
                    'max' => 20,
                    'min' => 1,
                ])
            );
        }

        return $this->validate($validator);
        
    }

    public function beforeCreate(){
        parent::beforeCreate();
        $this->shop_id = auth()->getShopId();
    }
    
}
