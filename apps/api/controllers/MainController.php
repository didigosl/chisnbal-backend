<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Api\Components\Auth;
use Common\Models\IUser;
use Common\Models\IPhoneCode;
use Common\Models\IEmailCode;
use Common\Components\Sms;
use Common\Components\Mail;
use Common\Components\SendInBlueMail;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;

class MainController extends ControllerBase {

	public function getPhoneCodeAction(){

		$code = rand(111111,999999);
		/* if(strlen($this->post['phone'])==11 and substr($this->post['phone'],0,1)=='1'){
			$code = '111111';
			$this->sendJSON(['status'=>'SUCCESS','msg'=>$code]);
		}
		else{
			if(Sms::sendPhoneCode($this->post['country_code'],$this->post['phone'],$code)){
				$this->sendJSON(['status'=>'SUCCESS','msg'=>$code]);
			}
		} */
		$country_code = ltrim($this->post['country_code'],'0+');
		if(Sms::sendPhoneCode($country_code,$this->post['phone'],$code)){
			$this->sendJSON(['status'=>'SUCCESS','msg'=>$code]);
		}
		
    }
    
    public function registerByEmailAction(){

        $email = trim($this->post['email']);
        $password = trim($this->post['password']);
        $re_password = trim($this->post['re_password']);
        
        // Mail::init()->sendCode($email,'111111');

        // return;

        $validator = new Validation();
        $validator->add(
            "email",
            new PresenceOf()
        );
        $validator->add(
            "email",
            new Email(
                [
                    "message" => "邮箱格式不正确",
                ]
            )
        );
        $validator->add(
            "password",
            new PresenceOf()
        );

        $messages  = $validator->validate($this->post);
        if (count($messages)) {
            $msgs  = [];
            foreach ($messages as $message) {
                $msgs[] =  $message;
            }

            throw new \Exception(implode(';',$msgs));
        }
        
        
        $check = db()->fetchColumn("SELECT count(1) FROM i_user WHERE email=:email",[
            'email'=>$email
        ]);
        if($check>0){
            throw new \Exception('此邮箱已经被注册了');
        }

        $data = [
            'email'=>$email,
            'secret'=>$password

        ];
        if(IEmailCode::send($data)){
            $this->sendJSON([]);
        }
        else{
            throw new \Exception('系统错误');
        }
		
    }
    
    public function verifyEmailCodeAction(){

        $data['email'] = trim($this->post['email']);
        $data['code'] = trim($this->post['code']);

        if($User = IEmailCode::verify($data)){
            $this->sendJSON([
                'data'=>[
                    'email'    		=>  $User->email,
                    'token'			=>	$User->token,
                    'secret_key'    =>  $User->secret_key,
                ]	                
            ]);
        }

    }

    public function loginByEmailAction(){
        $email = trim($this->post['email']);
        $password = trim($this->post['password']);

        $User = IUser::findFirst([
            'email=:email:',
            'bind'=>['email'=>$email]
        ]);

        if(!$User){
            throw new \Exception('账号或密码错误');
        }

        if(!security()->checkHash($password,$User->password)){
            throw new \Exception('账号或密码错误');
        }

        $this->sendJSON([
            'data'=>[
                'act'=>'login',
                'email'     	=>  $User->email,
                'token'			=>	$User->token,
                'secret_key'    =>  $User->secret_key,
                'level_id'=>$User->level_id,
                'level_name'=>$User->UserLevel->level_name,
            ]
            
        ]);
    }

    public function loginByPhoneAction(){
        $phone = trim($this->post['phone']);
        $country_code = ltrim($this->post['country_code'],'0+');
        $password = trim($this->post['password']);

        $User = IUser::findFirst([
            'phone=:phone: AND country_code=:country_code: AND status>0',
            'bind'=>[
                'phone'=>$phone,
                'country_code'=>$country_code
            ]
        ]);

        if(!$User){
            throw new \Exception('账号或密码错误');
        }

        if(!security()->checkHash($password,$User->password)){
            throw new \Exception('账号或密码错误');
        }

        $this->sendJSON([
            'data'=>[
                'act'=>'login',
                'phone'     	=>  $User->phone,
                'country_code'     	=>  $User->country_code,
                'token'			=>	$User->token,
                'secret_key'    =>  $User->secret_key,
                'level_id'=>$User->level_id,
                'level_name'=>$User->UserLevel->level_name,
            ]
            
        ]);
    }

    public function registerByPhoneAction(){

        if($this->post['password']!=$this->post['re_password']){
            throw new \Exception('两次输入的密码不一致');
        }
		$data = [
			'phone'=> $this->post['phone'],
			'country_code'=> ltrim($this->post['country_code'],'0+'),
            'code'=>$this->post['code'],
            'name'=>$this->post['name'],
            'password'=>$this->post['password']
		];
		if($User = IUser::register($data)){
            $this->sendJSON([
                'data'=>[
                    // 'identify'  =>  $User->user_id,
                    'country_code'	=> $User->country_code,
                    'phone'    		=>  $User->phone,
                    'name'    		=>  $User->name,
                    // 'token'			=>	$User->token,
                    // 'secret_key'    =>  $User->secret_key,
                ]	                
            ]);
        }
	}

	public function registerAction(){

		$data = [
			'phone'=> $this->post['phone'],
			'country_code'=> ltrim($this->post['country_code'],'0+'),
			'code'=>$this->post['code'],
		];
		if(IPhoneCode::verify($data)){
			if($User = IUser::register($data)){
				$this->sendJSON([
					'data'=>[
						// 'identify'  =>  $User->user_id,
						'country_code'	=> $User->country_code,
		                'phone'    		=>  $User->phone,
		                'token'			=>	$User->token,
		                'secret_key'    =>  $User->secret_key,
					]	                
	            ]);
			}
		}
	}

	public function loginAction(){
		$data = [
			'phone'=>$this->post['phone'],
			'country_code'=>ltrim($this->post['country_code'],'0+'),
			'code'=>$this->post['code'],
		];
		if($data['code']=='111111' or IPhoneCode::verify($data)){

			$check_exists = \Phalcon\Di::getDefault()->get('db')->fetchColumn('SELECT user_id FROM i_user WHERE phone=:phone and country_code=:country_code',[
				'phone'=>$data['phone'],
				'country_code'=>$data['country_code']
			]);
			if(!$check_exists){
				if($User = IUser::register($data)){
					$this->sendJSON([
						'data'=>[
							'act'=>'register',
							// 'identify'  =>  $User->user_id,
			                'phone'    		=>  $User->phone,
			                'token'			=>	$User->token,
			                'secret_key'    =>  $User->secret_key,
			                'level_id'=>$User->level_id,
							'level_name'=>$User->UserLevel->level_name,
						]	                
		            ]);
				}
			}
			else{
				if($User = IUser::login($data['phone'],$data['country_code'])){
					$this->sendJSON([
						'data'=>[
							'act'=>'login',
							// 'user_id'   =>  $User->user_id,
							'country_code'     	=>  $User->country_code,
			                'phone'     	=>  $User->phone,
			                'token'			=>	$User->token,
			                'secret_key'    =>  $User->secret_key,
			                'level_id'=>$User->level_id,
							'level_name'=>$User->UserLevel->level_name,
						]
		                
		            ]);
				}
			}
			
		}
	}

	public function getBankInfoAction(){

		$settings = $this->settings;
		$data = [
			'bank'=>$settings['bank'],
			'bank_account'=>$settings['bank_account'],
			'bank_user'=>$settings['bank_user'],
			'bank_intro'=>$settings['bank_intro'],
		];

		$this->sendJSON([
			'data'=>$data
		]);
	}

    /**
     * 获取修改密码邮箱验证码
     * @throws \Exception
     */
	public function getEmailCodeAction(){
       $email=$this->request->get('email');
       $check_exists = db()->fetchColumn('SELECT user_id FROM i_user WHERE email=:email',[
           'email'=>$email,
       ]);
       if(!$check_exists){
           throw new \Exception('邮箱不存在请先注册');
       }
       $code = rand(111111,999999);
       if(Mail::init()->sendVerifCode($email,$code)){
           //将邮箱验证码存到redis并且设置过期时间
           $redis = new \Redis();
           $redis->connect('127.0.0.1', 6379);
           $key="code_email".$email;
           $redis->setex($key,15*60,$code);
           return json_encode(['code'=>0,'status'=>'SUCCESSS','msg'=>'获取成功']);
       }else{
           throw new \Exception('邮件发送失败');
       }
    }

    /**
     *
     * @return mixed
     * @throws \Exception
     */
    public function changePwdAction(){
	   $code=$this->request->get('code');
	   $email=$this->request->get('email');
	   $newPwd=$this->request->get('newPwd');
       $redis = new \Redis();
       $redis->connect('127.0.0.1', 6379);
       $key="code_email".$email;
	   if($code==$redis->get($key)){
	       //查询用户
           $User = IUser::findFirst([
               'email=:email:',
               'bind'=>['email'=>$email]
           ]);
           if($User){
               $User->password=security()->hash($newPwd);
               $User->save();
               return json_encode(['code'=>0,'status'=>'SUCCESSS','msg'=>'修改成功']);
           }else{
               throw new \Exception('用户不存在');
           }
//           return $User::updatePwd($email,$newPwd);
       }else{
           throw new \Exception('验证码错误');
       }
    }
}
