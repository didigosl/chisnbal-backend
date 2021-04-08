<?php

namespace Common\Models;

class StatDayOrder extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $day_order_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $num;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $amount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $per_amount;

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
    public $create_date;

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
        $this->setSource("stat_day_order");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'stat_day_order';
    }

    static public function getPkCol(){
        return 'day_order_id';
    }

    static public $attrNames = [
        'num'=>'订单量',
        'amount'=>'订单金额',
        'per_amount'=>'客单价',
        'day'=>'时间',
    ];

    public function beforeSave(){
        $this->num = (int)$this->num;
        $this->amount = (int)$this->amount;
        $this->per_amount = 0;
        if($this->num){
            $this->per_amount = round($this->amount/$this->num);
        }
        else{
            $this->per_amount = 0;
        }

    }
}
