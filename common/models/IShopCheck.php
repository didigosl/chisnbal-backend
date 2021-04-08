<?php

namespace Common\Models;

class IShopCheck extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $shop_check_id;

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
    public $admin_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $check_result;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $reason;

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

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_shop_check");
        $this->belongsTo('shop_id', 'Common\Models\IShop', 'shop_id', ['alias' => 'Shop']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_shop_check';
    }

    static public function getPkCol(){
        return 'shop_check_id';
    }

    public function beforeSave(){
        $this->shop_id = (int)$this->shop_id;
        $this->admin_id = (int)$this->admin_id;
        $this->check_result = (int)$this->check_result;
    }

}
