<?php

namespace Common\Models;

use Common\Components\ValidateMsg;
use Phalcon\Di;
use Phalcon\Exception;


class Model extends \Phalcon\Mvc\Model {

	static public $attrNames = [];

	static public function getPkCol(){
        return 'id';
    }

	public function format() {
		$return = [];
		foreach ($this as $key => $value) {
			$return[$key] = $this->$key;
		}
		return $return;
	}

	public function getErrors()
    {
        /*$messages = [];
        foreach (parent::getMessages() as $message) {
            switch ($message->getType()) {
                case 'InvalidCreateAttempt':
                    $messages[] = 'The record cannot be created because it already exists';
                    break;

                case 'InvalidUpdateAttempt':
                    $messages[] = "The record cannot be updated because it doesn't exist";
                    break;

                case 'PresenceOf':
                    $messages[] = '必须提供“' . $this->getAttr($message->getField()) . '”';
                    break;

                case 'Uniqueness':
                    $messages[] = '“' . $this->getAttr($message->getField()) . '”已经存在，不可重复';
                    break;

                case 'TooLong':
                    $messages[] = '“' . $this->getAttr($message->getField()) . '”超出了最大字符长度限制';
                    break;

                case 'TooShort':
                    $messages[] = '“' . $this->getAttr($message->getField()) . '”超出了最小字符长度限制';
                    break;

                case 'Numericality':
                    $messages[] = '“' . $this->getAttr($message->getField()) . '”必须为数字';
                    break;

                 default:
                 	 $messages[] = $message->getMessage();
                 	break;
            }
        }

        return $messages;*/
        // var_dump(get_class($this));exit;
        return ValidateMsg::fmt(get_class($this),parent::getMessages());
    }

	
	public function getErrorMsg(){

		$messages = $this->getErrors();

		return implode('<br>', $messages);
	}

	public static function findFirst($parameters = null)
    {
    	if(empty($parameters)){
    		return null;
    	}

    	$Model = parent::findFirst($parameters);
    	// $Model->afterFindFirst();
        return $Model;
    }

    static public function getAttr($attr){
    	
    	if(isset(static::$attrNames[$attr])){
    		$return = static::$attrNames[$attr];
    	}
    	else{
    		$return = $attr;
    	}
    	return $return;
    }

    public function beforeCreate(){
    	if(!isset($this->create_time)){
    		$this->create_time = date('Y-m-d H:i:s');
    	}
    }

    
}
