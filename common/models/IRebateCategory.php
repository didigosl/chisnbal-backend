<?php

namespace Common\Models;

class IRebateCategory extends Model
{


    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $category_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $level_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $rebate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $rebate_type;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_rebate_category");
        $this->belongsTo('category_id', 'Common\Models\ICategory', 'category_id', ['alias' => 'ICategory']);
        $this->belongsTo('level_id', 'Common\Models\IUserLevel', 'level_id', ['alias' => 'IUserLevel']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_rebate_category';
    }

    static public function getPkCol(){
        return 'order_sku_id';
    }

    public function beforeSave(){
        $this->level_id = (int)$this->level_id;
        $this->rebate_type = (int)$this->rebate_type;
    }

    static public function getRebateTypeContext($var=null){
        $data = [
            1 => '金额',
            2 => '百分比',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }
}
