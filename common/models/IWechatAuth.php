<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;

class IWechatAuth extends Model
{

    public $user_id;

    public $access_token;

    public $refresh_token;

    public $expired_at;

    public function initialize()
    {
        
    }

    public function getSource()
    {
        return 'i_wechat_auth';
    }

    static public function getPkCol(){
        return 'user_id';
    }

}
