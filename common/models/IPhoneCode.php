<?php

namespace Common\Models;

use Common\Components\ValidateMsg;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\StringLength;


class IPhoneCode extends Model
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
    public $phone;

    public $country_code;

    /**
     *
     * @var string
     * @Column(type="string", length=6, nullable=true)
     */
    public $code;

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
        $this->setSource("i_phone_code");
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
        return 'i_phone_code';
    }

    static public function getPkCol(){
        return 'code_id';
    }


    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('phone',$cols)){
            $validator->add(
                'phone',
                new PresenceOf()
            );

            /*$validation->add(
                'phone',
                new Numericality([
                    'allowEmpty' => true,
                ])
            );*/

            $validator->add(
                'phone',
                new StringLength([
                    'max' => 16,
                    'min' => 6,
                    'messageMaximum'=>'##手机号码长度必须在6-16位数字之间',
                    'messageMinimum'=>'##手机号码长度必须在6-16位数字之间',
                ])
            );
        }
        
        if($cols_length==0 OR in_array('phone',$cols)){
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
        $conf = $this->getDi()->get('conf');
        $this->expired_time = time() + 60 * (int)$conf['phone_code_expired_time'];
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

    static public function verify($data=[]){

        $validator = self::validator();

        $messages = $validator->validate($data);
        $msgs = ValidateMsg::fmt(__CLASS__,$messages);

        if (count($msgs)) {
           
            $msg = implode("\r\n",$msgs);
            throw new \Exception($msg, 2001);  
        }

        $conditions = [];
        $params = [];

        $conditions[] = 'phone=:phone:';
        $params['phone'] = $data['phone'];

        if($data['country_code']){
            $conditions[] = 'country_code=:country_code:';
            $params['country_code'] = $data['country_code'];
        }
        else{
            $conditions[] = "country_code=''";
        }

        $conditions[] = 'status=1';
        $conditions[] = 'expired_time>:now:';
        $params['now'] = time();

        $conditionSql = implode(' AND ', $conditions);

        $Code = self::findFirst([
            $conditionSql,
            'bind'=>$params,
            'order'=>'code_id DESC'
        ]);

        if(!$Code OR $Code->code!=$data['code']){
            file_put_contents(SITE_PATH.'/logs/verify_code.txt',var_export($data,true));
            throw new \Exception("无效的验证码", 2002);            
        }
        else{
            if($Code->useIt()){
                return true;
            }
            else{
                throw new \Exception("Error Processing Request", 1002);
                
            }
        }
    }
}
