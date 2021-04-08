<?php
namespace Common\Models;

class SAclResource extends Model
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
    public $desc;

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
        return 's_acl_resource';
    }


    public function afterDelete()
    {
        $this->getDi()->get('db')->delete('acl_access',"resource_id=:id",array(':id'=>$this->id));
        $this->getDi()->get('db')->delete('acl_action',"resource_id=:id",array(':id'=>$this->id));
    }
}
