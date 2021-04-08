<?php
namespace Common\Models;

class SAclAction extends Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $controller_id;

    /**
     *
     * @var string
     */
    public $controller_name;

    /**
     *
     * @var string
     */
    public $action_name;

    /**
     *
     * @var string
     */
    public $intro;

    static public function getPkCol(){
        return 'id';
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 's_acl_action';
    }

    public function initialize() {
        $this->useDynamicUpdate(true);

        $this->belongsTo('resource_id', 'Common\Models\SAclResource', 'id', ['alias' => 'resource']);
    }

    public function beforeSave(){
        $this->resource_id = (int)$this->resource_id; 
    }
}
