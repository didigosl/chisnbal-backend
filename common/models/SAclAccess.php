<?php
namespace Common\Models;

class SAclAccess extends Model
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
    public $role_id;

    /**
     *
     * @var string
     */
    public $role_name;

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
     * @var integer
     */
    public $action_id;

    /**
     *
     * @var string
     */
    public $action_name;

    /**
     *
     * @var integer
     */
    public $allow_flag;

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
        return 's_acl_access';
    }

    public function beforeSave(){
        $this->role_id = (int)$this->role_id;
        $this->resource_id = (int)$this->resource_id; 
        $this->action_id = (int)$this->action_id; 
    }
}
