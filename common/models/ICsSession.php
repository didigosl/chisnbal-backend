<?php

namespace Common\Models;

class ICsSession extends Model
{

    public $cs_session_id;

    public $user_id;

    public $admin_id;

    public $lastest_msg;

    public $lastest_from;

    public $msg_total;

    public $user_total;

    public $admin_total;

    public $admin_unread_total;

    public $user_unread_total;

    public $shop_id;

    public $create_time;

    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_cs_session");

        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
        $this->belongsTo('admin_id', 'Common\Models\SAdmin', 'id', ['alias' => 'Admin']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_cs_session';
    }

    static public function getPkCol(){
        return 'cs_session_id';
    }

    static public $attrNames = [
        'user_id'=>'用户',
        'admin_id'=>'管理员',
        'lastest_msg'=>'最后消息',
        'msg_total'=>'消息总数',
        'update_time'=>'最后消息时间',
    ];

    public function beforeCreate(){
        parent::beforeCreate();

        $this->update_time = $this->create_time;
    }
}
