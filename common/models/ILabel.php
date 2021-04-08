<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;

class ILabel extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $label_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $label_name;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $shop_id;


     /*
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

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        // $this->setSource("i_label");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_label';
    }

    static public function getPkCol(){
        return 'label_id';
    }

    static public $attrNames = [
        'label_name'=>'商品标签',

    ];

    public function validation() {
        $validator = new Validation();
        $validator->add(
            'label_name',
            new PresenceOf()
        );
        $validator->add(
            ['shop_id','label_name'],
            new Uniqueness([
                'message'=>'##已经存在同名的标签'
            ])
        );

        if(!empty($this->label_name)){
            $validator->add(
                'label_name',
                new StringLength([
                    'max' => 4,
                    'min' => 1,
                ])
            );
        }

        return $this->validate($validator);
        
    }

    public function beforeCreate(){
        // $this->shop_id = $this->getDi()->get('auth')->getShopId();
    }

    
}
