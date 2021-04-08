<?php

namespace Common\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Uniqueness;

class SSetting extends Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $value;

    /**
     *
     * @var string
     */
    public $text;

    /**
     *
     * @var string
     */
    public $intro;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var string
     */
    public $options;

    /**
     *
     * @var integer
     */
    public $update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 's_setting';
    }

    /*public function validation() {

        $this->validate(
            new PresenceOf(
                array(
                    'field' => 'name',
                    'required' => true,
                )
            )
        );

        $this->validate(
            new PresenceOf(
                array(
                    'field' => 'value',
                    'required' => true,
                )
            )
        );

        $this->validate(
            new Uniqueness(
                array(
                    'field' => 'name',
                    'required' => true,
                )
            )
        );


        if ($this->validationHasFailed() == true) {
            return false;
        }

        return true;
    }*/
}
