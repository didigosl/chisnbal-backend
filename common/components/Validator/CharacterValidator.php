<?php
namespace Common\Components\Validator;

use Phalcon\Mvc\Model\Validator;
use Phalcon\Mvc\Model\ValidatorInterface;
use Phalcon\Mvc\EntityInterface;
use Phalcon\Mvc\ModelInterface;

class CharacterValidator extends Validator implements ValidatorInterface
{

    public function validate(\Phalcon\Mvc\EntityInterface $model)
    {
        $field = $this->getOption('field');

        $min   = $this->getOption('min');
        $max   = $this->getOption('max');

        $value = $model->$field;

        $char_length = strlen(iconv('utf-8','gbk',$value));

        if ($max  and $max < $char_length) {
            $this->appendMessage(
                $model->getAttr($field).':最多不超过'.floor($max/2).'汉字或'.$max.'个英文字母',
                $field,
                "CharacterValidator"
            );

            return false;
        }

        if ($min and $min > $char_length) {
            $this->appendMessage(
                $model->getAttr($field).':最少不少于'.floor($min/2).'汉字或'.$min.'个英文字母',
                $field,
                "CharacterValidator"
            );

            return false;
        }

        return true;
    }
}