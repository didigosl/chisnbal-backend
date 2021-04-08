<?php

namespace Common\Models;

class IDiscountCategory extends Model
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
    public $discount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $discount_type;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_discount_category");
        $this->belongsTo('category_id', 'Common\Models\ICategory', 'category_id', ['alias' => 'Category']);
        $this->belongsTo('level_id', 'Common\Models\IUserLevel', 'level_id', ['alias' => 'UserLevel']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_discount_category';
    }

    public function beforeSave(){
        $this->level_id = (int)$this->level_id;
        $this->discount_type = (int)$this->discount_type;
    }


}
