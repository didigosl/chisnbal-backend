<?php

namespace Common\Models;

class IMember extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $member_id;

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
    public $user_id;

  
    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_member");
        $this->belongsTo('shop_id', 'Common\Models\IShop', 'shop_id', ['alias' => 'Shop']);
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_member';
    }

    static public function getPkCol(){
        return 'member_id';
    }

    public function beforeSave(){
        $this->shop_id = (int)$this->shop_id;
        $this->user_id = (int)$this->user_id;
    }

}
