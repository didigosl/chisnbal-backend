<?php

namespace Common\Models;

use Phalcon\Di;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Exception;

class SAdmin extends Model {

	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var string
	 */
	public $username;

	/**
	 *
	 * @var string
	 */
	public $password;

	/**
	 *
	 * @var string
	 */
	public $salt;

	/**
	 *
	 * @var integer
	 */
	public $acl_role_id;

	/**
	 *
	 * @var integer
	 */
	public $shop_id;

	/**
	 *
	 * @var string
	 */
	public $name;

	/**
	 *
	 * @var integer
	 */
	public $flag;

	/**
	 *
	 * @var integer
	 */
	public $create_time;

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
	public function getSource() {
		return 's_admin';
	}

	public function validation()
    {
        $validator = new Validation();

        /*if($this->aclRole->shop_id>0){
        	$validator->add(
	            'shop_id',
	            new PresenceOf()
	        );
        }
*/
        $validator->add(
            'username',
            new PresenceOf()
        );

        $validator->add(
            'username',
            new Uniqueness([
            	'message' => '此账号名已经存在，请更换一个其他账号',
            	])
        );

     
        return $this->validate($validator);
    }

	public function initialize() {
		$this->useDynamicUpdate(true);
		$this->skipAttributesOnUpdate(array('create_time'));
		$this->belongsTo('acl_role_id', 'Common\Models\SAclRole', 'id', ['alias' => 'aclRole']);
		$this->belongsTo('shop_id', 'Common\Models\IShop', 'shop_id', ['alias' => 'Shop']);
	}

	public function beforeCreate()
    {
    	$this->flag = 1;
    	$this->shop_id = $this->aclRole->shop_id;
    	/*if($this->password != $_POST['repassword']){
    		throw new \Exception("两次输入的密码不相同", 1);
    	}
        $this->password = $this->di->getSecurity()->hash($this->password);;*/
    }

    public function beforeSave(){
        $this->acl_role_id = (int)$this->acl_role_id; 
    }


	public function checkPassword($password){
		// if (!$this->get('security')->checkHash($password, $this->password)) {
        if (!SAdmin::checkHash($password, $this->password)) {
			throw new \Exception('帐号或密码不正确',1);
		}
		return true;
	}

	public function changePassword($password,$repassword){
		if($password!=$repassword){
			throw new \Exception("两次输入的密码不相同", 1);
			
		}
		if(strlen($password)<6){
			throw new \Exception("密码长度不能少于6位字符", 1);
		}

        // $this->password = $this->di->getSecurity()->hash($password);
        $this->password = SAdmin::hash($password);
		if(!$this->save()){
			throw new \Exception("Error Processing Request", 1);
			
		}
		return true;
    }
    
    static public function hash($str){
        $ret = '';
        $conf = DI::getDefault()->get('conf');
        if($conf['admin_hash_method']=='md5'){
            $ret = md5($str);
        }
        else{
            $ret = DI::getDefault()->get('security')->hash($str);
        }
        return $ret;
    }

    static public function checkHash($source,$hashed){
        $conf = DI::getDefault()->get('conf');
        if($conf['admin_hash_method']=='md5'){
            $ret = md5($source) == $hashed;
        }
        else{
            $ret = DI::getDefault()->get('security')->checkHash($source, $hashed);
        }
        return $ret;
    }
}

