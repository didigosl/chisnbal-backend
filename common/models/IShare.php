<?php

namespace Common\Models;

use kotchuprik\short_id\ShortId;

class IShare extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $share_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=9, nullable=true)
     */
    public $code;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $status;

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
        $this->setSource("i_share");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_share';
    }

    static public function getPkCol(){
        return 'share_id';
    }

    public function afterCreate(){

        $shortId = new ShortId();
        $code = $shortId->encode($this->user_id.$this->share_id, 8);
        $this->getDi()->get('db')->updateAsDict('i_share',['code'=>$code],'share_id='.$this->share_id);
    }
}
