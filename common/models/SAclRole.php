<?php
namespace Common\Models;

use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation;

class SAclRole extends Model
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
    public $intro;

    /**
     *
     * @var integer
     */
    public $custom_flag;

    /**
     *
     * @var integer
     */
    public $shop_id;


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
        return 's_acl_role';
        $this->belongsTo('shop_id', 'Common\Models\IShop', 'shop_id', ['alias' => 'Shop']);
    }

    static public $attrNames = [
        'id'=>'ID',
        'name'=>'角色标识',
        'intro'=>'角色名称',
        'shop_id'=>'店铺',
    ];

    public function validation() {

        $validator = new Validation();

        /*$validator->add(
            'name',
            new PresenceOf()
        );

        $validator->add(
            'name',
            new Uniqueness()
        );

        $validator->add(
            'name',
            new StringLength([
                'max' => 15,
                'min' => 2,
                'message'=>'角色标识必须在2-15位数字之间'
            ])
        );
*/
        $validator->add(
            'intro',
            new StringLength([
                'max' => 15,
                'min' => 2,
                'message'=>'角色名称必须在2-15位数字之间'
            ])
        );

        $validator->add(
            'intro',
            new PresenceOf()
        );

        $validator->add(
            ['shop_id','intro'],
            new Uniqueness()
        );

        return $this->validate($validator);
    }

    public function afterCreate(){
        $db = $this->getDi()->get('db');
        if($this->shop_id){
            $role_total = $db->fetchColumn("SELECT count(1) FROM s_acl_role WHERE shop_id=:shop_id",['shop_id'=>$this->shop_id]);
            if($role_total>1){
                $name = 'shopadmin'.$this->id;
            }
            else{
                $name = 'shopadmin';
            }
        }
        else{
            $name = 'superadmin'.$this->id;
        }

       
        $db->execute("UPDATE s_acl_role SET name=:name WHERE id=:id",[
            'name'=>$name,
            'id'=>$this->id,
        ]);
    }

    public function afterSave(){
        //角色名称更新后同时更新access表
        $db = $this->getDi()->get('db');
        $res = $db->execute('UPDATE s_acl_access SET role_name=:name WHERE role_id=:id',[
            'name'=>$this->name,
            'id'=>$this->id
        ]);

    }

    public function afterDelete(){
        $db = $this->getDi()->get('db');

        $db->execute('DELETE FROM s_acl_access WHERE role_name=:role_name',['role_name'=>$this->name]);
    }


}
