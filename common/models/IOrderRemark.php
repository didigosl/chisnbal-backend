<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class IOrderRemark extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $order_remark_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $order_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $content;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $admin_id;

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

    public $import_mode = false;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_order_remark");
        $this->belongsTo('admin_id', 'Common\Models\SAdmin', 'id', ['alias' => 'Admin']);
        $this->belongsTo('order_id', 'Common\Models\IOrder', 'order_id', ['alias' => 'Order']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_order_remark';
    }

    static public function getPkCol(){
        return 'order_remark_id';
    }

    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('content',$cols)){
            $validator->add(
                'content',
                new PresenceOf()
            );
        }
        
        return $validator;
    }

    public function beforeCreate(){
        parent::beforeCreate();

        if(!$this->import_mode){
            $this->admin_id = $this->getDi()->get('auth')->getUser()->id;
        }
        
    }

    public function beforeSave(){
        $this->order_id = (int)$this->order_id;
        $this->admin_id = (int)$this->admin_id;

    }
}
