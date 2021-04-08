<?php

namespace Common\Models;

class IKeyword extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $keyword_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $content;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total;

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

        $this->setSource("i_keyword");
    }

    static public function getPkCol(){
        return 'keyword_id';
    }

    public function beforeSave(){
        $this->total = (int)$this->total;
    }
}
