<?php

namespace Common\Models;

class StatDaySale extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $day_sale_id;

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
    public $sku_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $num;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $day;

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
        $this->setSource("stat_day_sale");
        $this->belongsTo('sku_id', 'Common\Models\IGoodsSku', 'sku_id', ['alias' => 'Sku']);
        $this->belongsTo('spu_id', 'Common\Models\IGoodsSpu', 'spu_id', ['alias' => 'Spu']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'stat_day_sale';
    }

    static public function getPkCol(){
        return 'day_sale_id';
    }

    public function beforeSave(){
        $this->spu_id = (int)$this->spu_id;
        $this->sku_id = (int)$this->sku_id;
        $this->num = (int)$this->num;
    }

}
