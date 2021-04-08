<?php

namespace Common\Models;

use Common\Components\ValidateMsg;
use Common\Components\Mail;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Email;

class IEmailCode extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $code_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $email;

    /**
     *
     * @var string
     * @Column(type="string", length=6, nullable=true)
     */
    public $code;

    public $secret;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $verify_time;

    /**
     *
     * @var integer
     * @Column(type="integer", nullable=true)
     */
    public $expired_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_email_code");
    }

    public function validation()
    {
        $validator = self::validator();
        
        return $this->validate($validator);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_email_code';
    }

    static public function getPkCol(){
        return 'code_id';
    }


    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('email',$cols)){
            $validator->add(
                'email',
                new PresenceOf()
            );

            $validator->add(
                'email',
                new Email()
            );

            $validator->add(
                'code',
                new PresenceOf()
            );

            $validator->add(
                'code',
                new Numericality([
                    'allowEmpty' => true,
                ])
            );
        }
     
        return $validator;
    }

    public function beforeCreate(){
        parent::beforeCreate();        
        // $conf = $this->getDi()->get('conf');
        // $this->expired_time = time() + 60 * (int)$conf['phone_code_expired_time'];
    }

    /**
     * 使用验证码
     * @return [type] [description]
     */
    public function useIt(){
        $this->status = 2;
        $this->verify_time = date('Y-m-d H:i:s');
        return $this->save();
    }

    static public function send($data=[]){
        // var_dump($data);exit;
        $code = rand(111111,999999);

        $Code = self::findFirst([
            'email=:email: AND status=1',
            'bind'=>[
                'email'=>$data['email'],
            ],
            'order'=>'code_id DESC'
        ]);

        if(!$Code){
            $Code = new IEmailCode;
            $Code->email = $data['email'];
        }

        $Code->code = $code;
        $Code->secret = $data['secret'];

        if(Mail::init()->sendCode($Code->email,$code)){
            if($Code->save()){
                return true;
            }
            else{
                throw new \Exception('系统错误');
            }
        }
        else{
            throw new \Exception('邮件发送失败 ');
        }
    }

    static public function verify($data=[]){

        $validator = self::validator();

        $messages = $validator->validate($data);
        $msgs = ValidateMsg::fmt(__CLASS__,$messages);

        if (count($msgs)) {
           
            $msg = implode("\r\n",$msgs);
            throw new \Exception($msg, 2001);  
        }

        $Code = self::findFirst([
            'email=:email: AND status=1',
            'bind'=>[
                'email'=>$data['email'],
            ],
            'order'=>'code_id DESC'
        ]);

        if(!$Code OR $Code->code!=$data['code']){
            file_put_contents(SITE_PATH.'/logs/email_verify_code.txt',var_export($data,TRUE));
            throw new \Exception("无效的验证码", 2002);            
        }
        else{
            if($Code->useIt()){
                $User = IUser::registerByEmail([
                    'email'=>$Code->email,
                    'password'=>$Code->secret
                ]);
                if($User){
                    return $User;
                }
                else{
                    throw new \Exception('验证失败');
                }
            }
            else{
                throw new \Exception("Error Processing Request", 1002);
                
            }
        }
    }
}
