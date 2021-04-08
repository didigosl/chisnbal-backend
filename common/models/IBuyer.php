<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;

class IBuyer extends Model
{
    public $buyer_id;

    public $username;

    public $password;

    public $name;

    public $phone;

    public $country_code;

    public $gender;

    public $shop_id;

    public $token;

    public $secret_key;

    public $status;

    public $create_time;

    public $update_time;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_buyer");
        $this->hasMany('shop_id', 'Common\Models\IShop', 'shop_id', ['alias' => 'Shop']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_buyer';
    }
    
    static public function getPkCol(){
        return 'buyer_id';
    }

    public function getPhone(){
        return preg_replace('/(<\d+>)/','',$this->phone);
    }

    public function getEmail(){
        return preg_replace('/(<\d+>)/','',$this->email);
    }

    public function validation() {

        $validator = new Validation();

        $validator->add(
            'username',
            new PresenceOf()
        );
        $validator->add(
            'name',
            new PresenceOf()
        );
        /* if($conf['login_method']=='phone'){
            $validator->add(
                'phone',
                new PresenceOf()
            );
            $validator->add(
                ['phone','country_code'],
                new Uniqueness([
                    // 'filed'=>'phone',
                ])
            );
    
            $validator->add(
                'phone',
                new StringLength([
                    'max' => 16,
                    'min' => 6,
                    'message'=>'手机号码长度必须在6-16位数字之间'
                ])
            );
        } */
        
        return $this->validate($validator);

    }

    static public $attrNames = [
        'buyer_id'=>'ID',
        'username'=>'账号',
        'password'=>'密码',
        'phone'=>'联系电话',
        'name'=>'姓名',
        'gender'=>'性别',
        'create_time'=>'注册时间',
        'status'=>'状态',
    ];

    public function beforeSave(){
        $this->gender = (int)$this->gender;
        $this->status = (int)$this->status;
    }

    public static function getGenderContext($var = null) {
        $data = [
            0  => '未知',
            1  => '男',
            2  => '女',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public static function getStatusContext($var = null) {
        $data = [
            -1  => '冻结',
            0 => '待审',
            1   => '正常',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function genToken(){
        return md5('BUYER#$'.$this->username.'-'.$this->phone.$this->email);
    }

    public function beforeCreate(){
        
        if(empty($this->password)){
            throw new \Exception('必须设置密码');
        }
        $this->create_time = date('Y-m-d H:i:s');
        $this->status = 1;
        $this->password = self::hash($this->password);
        $this->token = $this->genToken();
       
    }

    public function afterCreate(){
        // $this->genFakeData();
        $this->genSecretKey();
    }

    /**
     * 软删除
     * @return [type] [description]
     */
    public function remove(){
        $this->remove_flag = 1;

        if($this->phone){
            $this->phone = $this->phone.'[deleted]('.time().')';
        }
        
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'delete',$this->buyer_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }

    public function freeze(){
        $this->status = -1;
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'freeze',$this->buyer_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }

    public function unfreeze(){
        $this->status = 1;
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'unfreeze',$this->buyer_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }

    public function audit(){
        if($this->status!=0){
            throw new \Exception('不可审核非待审的账号');
        }
        $this->status = 1;
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'audit',$this->buyer_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }


    public function resetpsw(){
        // $this->status = 1;
        $this->password = self::hash('123456');
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'resetpsw',$this->buyer_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }

    public function genSecretKey(){

        $secret_key = security()->hash($this->buyer_id.'-'.$this->username.'-'.time().rand());
        db()->updateAsDict('i_buyer',[
            'secret_key'=>$secret_key,
        ],'buyer_id='.$this->buyer_id);
       
    }


    static public function hash($str){
        $ret = '';
        $conf = conf();
        if($conf['user_hash_method']=='md5'){
            $ret = md5($str);
        }
        else{
            $ret = security()->hash($str);
        }
        return $ret;
    }

    

}
