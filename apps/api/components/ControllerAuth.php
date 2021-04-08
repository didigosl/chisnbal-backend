<?php

namespace Api\Components;

use Common\Models\IUser;
use Common\Libs\Arr;

class ControllerAuth extends ControllerBase
{

	public $User = null;

	public function initialize(){

      	parent::initialize();

        if($this->post['session_id']){
            $this->wxmpAuth();
        }
        else{
            if(empty($this->post['sign']) or empty($this->post['token'])){
                throw new \Exception('身份验证失败', 2003);
                
            }	
            else{
                $this->auth();
            }
        }
      	
    }

    /**
     * 微信小程序sessionid auth
     */
    protected function wxmpAuth(){

        $User = IUser::findFirst([
			'wx_session_id=:session_id:',
			'bind'=>['session_id'=>$this->post['session_id']]
		]);

		if(!$User){
			throw new \Exception("用户信息不存在", 2003);
			
		}

		if($User->status<0){
			throw new \Exception("帐号已经被冻结", 2003);
        }
        
        if($User->wx_session_id!=$this->post['session_id']){
            throw new \Exception("session 验证失败", 2003);
        }

        $this->User = $User;
        // $this->isWxmp = 1;
        $this->session->set('isWxmp', 1);
		return true;
    }
    
	protected function auth(){
		/* $User = IUser::findFirst([
			'token=:token:',
			'bind'=>['token'=>$this->post['token']]
		]);

		if(!$User){
			throw new \Exception("用户信息不存在", 2003);
			
		}

		if($User->status<0){
			throw new \Exception("帐号已经被冻结", 2003);
		}

		$client_sign = $this->post['sign'];
		$time = $this->post['time'];
		$token = $this->post['token'];
		if($client_sign != 'test'){
			unset($_POST['sign']);
			unset($_POST['time']);
			unset($_POST['token']);
			$params = [];
			if(count($_POST)){
				Arr::ksort($_POST);

				foreach ($_POST as $key => $value) {
					// $params[] = $key.'='.$value;
					if(is_array($value)){
						$value = trim(json_encode($value,JSON_UNESCAPED_UNICODE),'"');
					}
					
					$params[] = $key.'='.$value;
				}
			}

			$params = implode('&',$params);
			$s = $params.$User->secret_key.$time;
			$sign = md5($s);
			$fp = fopen(SITE_PATH.'/logs/authlog.txt','a+');
			fputs($fp,date('Y-m-d H:i:s').' '.$this->dispatcher->getControllerName().'_'.$this->dispatcher->getActionName().' '.$s.' / '.$token.' / '.$time.' / '.$client_sign.' / '.$sign."\n");
			fclose($fp);

			if($sign != $client_sign){
				throw new \Exception("身份验证失败.", 2003);
				
			}
		}
		
        $this->User = $User;
        // $this->isWxmp = 0;
        $this->session->set('isWxmp', 0);
        return true; */
        
        $this->User = apiAuth();
        if($this->User){
            $this->session->set('isWxmp', 0);
            return true;
        }
	}

	public function checkOwner($user_id){
		if($this->User->user_id != $user_id){
			throw new \Exception("非法操作", 2004);
			
		}
	}

}
