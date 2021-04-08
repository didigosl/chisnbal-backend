<?php

namespace \Common\Models;

class IAdPos extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $ad_pos_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $position_type;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $num;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("kz_shop");
        $this->setSource("i_ad_pos");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_ad_pos';
    }

    static public function getPkCol(){
        return 'ad_pos_id';
    }

}
