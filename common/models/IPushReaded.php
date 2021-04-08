<?php
namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;

class IPushReaded extends Model
{

    public $push_readed_id;

    public $push_id;

    public $user_id;

    public $create_time;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_push_readed");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_push_readed';
    }

    static public function getPkCol(){
        return 'push_readed_id';
    }
    
}