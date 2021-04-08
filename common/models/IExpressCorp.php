<?php

namespace Common\Models;

class IExpressCorp extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $express_corp_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $corp_name;

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

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_express_corp");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_express_corp';
    }

    static public function getPkCol(){
        return 'express_corp_id';
    }

    static public $attrNames = [
        'corp_name'=>'快递公司名称',

    ];
}
