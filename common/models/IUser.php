<?php

namespace Common\Models;


use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;

class IUser extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $phone;

    public $country_code;

    public $email;

     /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $password;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $gender;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $age;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $avatar;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $wx_avatar;

    /**
     *
     * @var string
     * @Column(type="string", length=120, nullable=true)
     */
    public $wx_nickname;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $wx_openid;    

    public $wx_session_id;

    public $wx_session_key;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $level_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total_rebate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $money;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $buy_total;


    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $parent_id;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=true)
     */
    public $parent_merger;
   
    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=true)
     */
    public $token;

     /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=true)
     */
    public $secret_key;

    public $shop_id;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=true)
     */
    public $stripe_customer;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $remove_flag;

    public $kf_admin_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $update_time;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_user");
        $this->hasMany('user_id', 'Common\Models\IAddress', 'user_id', ['alias' => 'IAddress']);
        $this->hasMany('user_id', 'Common\Models\ICart', 'user_id', ['alias' => 'ICart']);
        $this->hasMany('user_id', 'Common\Models\IOrder', 'user_id', ['alias' => 'IOrder']);
        $this->belongsTo('level_id', 'Common\Models\IUserLevel', 'level_id', ['alias' => 'UserLevel']);
        $this->belongsTo('parent_id', 'Common\Models\IUser', 'user_id', ['alias' => 'Parent']);
        $this->belongsTo('kf_admin_id', 'Common\Models\SAdmin', 'id', ['alias' => 'kf']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_user';
    }
    
    static public function getPkCol(){
        return 'user_id';
    }

    public function getPhone(){
        return preg_replace('/(<\d+>)/','',$this->phone);
    }

    public function getEmail(){
        return preg_replace('/(<\d+>)/','',$this->email);
    }

    public function validation() {

        $validator = new Validation();

        $conf = \Phalcon\Di::getDefault()->get('conf');
        if($conf['platform']!='mp'){
            if($conf['login_method']=='phone'){
                $validator->add(
                    'phone',
                    new PresenceOf()
                );
                $validator->add(
                    ['phone','country_code'],
                    new Uniqueness([
                        'message'=>'手机号码已经存在不可重复'
                    ])
                );
        
                $validator->add(
                    'phone',
                    new StringLength([
                        'max' => 30,
                        'min' => 4,
                        'message'=>'手机号码长度必须在6-16位数字之间'
                    ])
                );
            }
            if($conf['login_method']=='email'){
                $validator->add(
                    'email',
                    new PresenceOf()
                );
                $validator->add(
                    'email',
                    new Uniqueness([
                    ])
                );
            }
        }
        

        return $this->validate($validator);

    }

    static public $attrNames = [
        'user_id'=>'用户',
        'phone'=>'联系电话',
        'name'=>'姓名',
        'gender'=>'性别',
        'age'=>'年龄',
        'total_rebate'=>'总返利金额',
        'money'=>'账户金额',
        'buy_total'=>'购买次数',
        'level_id'=>'会员等级',
        'create_time'=>'注册时间',
        'status'=>'状态',
        'kf_admin_id'=>'专属客服'
    ];

    public function beforeSave(){
        $this->age = (int)$this->age;
        $this->gender = (int)$this->gender;
        $this->level_id = (int)$this->level_id;
        $this->total_rebate = (int)$this->total_rebate;
        $this->money = (int)$this->money;
        $this->buy_total = (int)$this->buy_total;
        $this->parent_id = (int)$this->parent_id;
        $this->status = (int)$this->status;
        $this->remove_flag = (int)$this->remove_flag;
        $this->kf_admin_id = (int)$this->kf_admin_id;
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
        return md5('SDFG2#$'.$this->country_code.'-'.$this->phone.$this->email.time());
    }

    public function beforeCreate(){
        $this->level_id = 1;
        
        $this->money = 0;
        $this->total_rebate = 0;
        $this->buy_total = 0;
        $this->token = $this->genToken();
        $this->create_time = date('Y-m-d H:i:s');

        $conf = conf();
        if($conf['register_audit']){
            $this->status = 0;
        }
        else{
            $this->status = 1;
        }

        if($this->parent_id){
            $this->parent_merger = ','.trim($this->Parent->merger,',').','.$this->parent_id;
        }
    }

    public function afterCreate(){
        // $this->genFakeData();
    }

    /**
     * 软删除
     * @return [type] [description]
     */
    public function remove(){
        $this->remove_flag = 1;

        if($this->phone){
            $this->phone = $this->phone.'[deleted]';
        }
        if($this->email){
            $this->email = $this->email.'[deleted]';
        }
        
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'delete',$this->user_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }

    public function freeze(){
        $this->status = -1;
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'freeze',$this->user_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }

    public function unfreeze(){
        $this->status = 1;
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'unfreeze',$this->user_id,$this->phone.'('.$this->name.')');
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
            SAdminLog::add($this->getSource(),'audit',$this->user_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }


    public function resetpsw(){
        // $this->status = 1;
        $this->password = self::hash('123456');
        // exit($this->password);
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'resetpsw',$this->user_id,$this->phone.'('.$this->name.')');
        }
        return $ret;
    }

    public function setParent($parent_id){
        if($this->parent_id>0){
            throw new \Exception("不可重复设置推荐人", 1);
            
        }

        $this->parent_id = $parent_id;
        $this->parent_merger = $parent_id;
        if($this->Parent->parent_merger){
            $this->parent_merger = trim($this->Parent->parent_merger,',').','.$parent_id;
        }
        else{
            $this->parent_merger = $parent_id;
        }
        // var_dump($this->parent_id,$this->parent_merger);exit;
        return $this->save();
    }

    public function genSecretKey(){

        $this->secret_key = $this->di->getSecurity()->hash($this->user_id.'-'.$this->phone.'-'.time().rand());
        if(!$this->save()){
            throw new \Exception($this->getErrorMsg(), 2002);
            
        }
    }

    static function register($data){
        $check_exists = \Phalcon\Di::getDefault()->get('db')->fetchColumn('SELECT user_id FROM i_user WHERE phone=:phone AND country_code=:country_code',[
            'phone'=>$data['phone'],
            'country_code'=>$data['country_code']
        ]);
        if($check_exists){
            throw new \Exception("此手机号码已经注册过了", 1);
            
        }
        $User = new self;
        $User->phone = $data['phone'];
        $User->country_code = $data['country_code'];

        if($data['name']){
            $User->name = $data['name'];
        }
        
        if($data['password']){
            $User->password = security()->hash($data['password']);
        }

        if($User->save()){
            $User->genSecretKey();

            return $User;
            
        }
        else{
            throw new \Exception($User->getErrorMsg(), 2001);
            
        }
    }

    static function registerByEmail($data){

        $check_exists = db()->fetchColumn('SELECT user_id FROM i_user WHERE email=:email',[
            'email'=>$data['email'],
        ]);
        if($check_exists){
            throw new \Exception("此邮箱已经注册过了", 1);
        }
        $data['password'] = security()->hash($data['password']);
        $User = new self;
        $User->assign($data);
        if($User->save()){
            $User->genSecretKey();

            return $User;
            
        }
        else{
            throw new \Exception($User->getErrorMsg(), 2001);
            
        }
    }

    static function updatePwd($email,$newPwd){
        $check_exists = db()->fetchColumn('SELECT user_id FROM i_user WHERE email=:email',[
            'email'=>$email,
        ]);
        if(!$check_exists){
            throw new \Exception("邮箱不存在", 1);
        }
        $password=security()->hash($newPwd);
        db()->query("update i_user set password=:password",['password'=>$password]);
        return json_encode(['status'=>'SUCCESS','msg'=>'修改成功']);
    }

    static function login($phone,$country_code=''){

        $User = self::findFirst(['phone=:phone: AND country_code=:country_code:','bind'=>[
            'phone'=>$phone,
            'country_code'=>$country_code,
        ]]);
        if(!$User){
            throw new \Exception("帐号不存在", 1);
        }
        if($User->status<0){
            throw new \Exception("帐号已经被冻结", 1);            
        }
        $User->genSecretKey();

        return $User;
        
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

    public function genFakeData(){

        //生成moneylog
        for($i=0; $i<10; $i++){
            $amount = rand(100,500);
            $MoneyLog = new IMoneyLog;
            $MoneyLog->assign([
                'user_id'=>$this->user_id,
                'amount'=>$amount,
                'type'=>'rebate',
                'remark'=>'模拟返利',

            ]);
            $MoneyLog->save();
        }

        for($i=0; $i<10; $i++){
            $amount = rand(100,300);
            $MoneyLog = new IMoneyLog;
            $MoneyLog->assign([
                'user_id'=>$this->user_id,
                'amount'=>$amount,
                'type'=>'draw',
                'remark'=>'模拟提现',

            ]);
            $MoneyLog->save();
        }
    }
}
