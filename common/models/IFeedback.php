<?php

namespace Common\Models;

class IFeedback extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $feedback_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $title;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $content;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_id;

    public $nickname;

    public $phone;

    public $email;

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
        $this->setSource("i_feedback");
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_feedback';
    }

    static public function getPkCol(){
        return 'feedback_id';
    }

    static public $attrNames = [
        'title'=>'标题',
        'content'=>'内容',
        'user_id'=>'用户',
        'nickname'=>'昵称',
        'phone'=>'电话',
        'email'=>'邮箱',
        'create_time'=>'反馈时间'

    ];

    public function beforeSave(){
        $this->user_id = (int)$this->user_id;
    }
}
