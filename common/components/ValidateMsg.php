<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use Phalcon\Exception;

class ValidateMsg extends Component {


    static public function fmt($model,$messages){
        
        $msg = [];
        if(count($messages)){
            foreach ($messages as $message) {
                $field_name = [];
                $field = $message->getField();
                if(is_array($field)){
                    foreach ($field as $k => $v) {
                        $field_name[] = '"'.$model::getAttr($v).'"';
                    }
                }
                else{
                    $field_name[] = '"'.$model::getAttr($message->getField()).'"';
                }
                $field_name = implode('，', $field_name);
                $m = $message->getMessage();

                if(strpos($m,'##')!==false) {
                    $msg[] = ltrim($m,'#');
                }
                else{
                    switch ($message->getType()) {
                        case 'InvalidCreateAttempt':
                            $msg[] = 'The record cannot be created because it already exists';
                            break;

                        case 'InvalidUpdateAttempt':
                            $msg[] = "The record cannot be updated because it doesn't exist";
                            break;

                        case 'PresenceOf':
                            $msg[] = '“' . $field_name . '”必须提供';
                            break;

                        case 'Uniqueness':
                            $msg[] = '“' . $field_name . '”已经存在，不可重复';
                            break;

                        case 'TooLong':
                            $msg[] = '“' . $field_name . '”超出了最大长度限制';
                            break;

                        case 'TooShort':
                            $msg[] = '“' . $field_name . '”超出了最小长度限制';
                            break;

                        default:
                            $msg[] = $message->getMessage();
                            break;
                    }
                }

            }
        }
        return $msg;
    }


	static public function run($model,$messages){
        $msg = self::fmt($model,$messages);
        if(!empty($msg)){
            throw new \Exception(implode('<br>', $msg), 2001);
            
        }
    }
}
