<?php


namespace Common\Models;


class SConf extends Model
{
    public $id;

    public $name;

    public $value;

    public $text;

    public $intro;

    public $type;

    public $options;

    public $update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 's_conf';
    }

}