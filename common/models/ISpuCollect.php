<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Common\Libs\Func;

class ISpuCollect extends Model
{

    /**
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $collect_id;

    /**
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $spu_id;

    /**
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_id;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $collect_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_spu_collect");
        $this->belongsTo('spu_id', 'Common\Models\IGoodsSpu', 'spu_id', ['alias' => 'Spu']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_spu_collect';
    }

    static public function getPkCol(){
        return 'collect_id';
    }

}
