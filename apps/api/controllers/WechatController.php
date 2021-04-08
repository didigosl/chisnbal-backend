<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IUser;
use Common\Models\IUserLevel;
use Common\Models\IShare;
use Common\Models\IVipPayment;
use Common\Models\IArticle;
use Common\Libs\Func;
use Common\Components\ValidateMsg;
use Common\Components\Braintree;
use EasyWeChat\Foundation\Application;
use Common\Components\Wechat;

class WechatController extends ControllerBase {

    public function wxSessionAction(){

        $conf = \Phalcon\Di::getDefault()->get('conf');
        $options = [
            'mini_program' => [
                'app_id'   => $conf['wechat_mp_app_id'],
                'secret'   => $conf['wechat_mp_secret'],
                // 'token'    => 'component-token',
                // 'aes_key'  => 'component-aes-key'
                ],
        ];
        
        $app = new Application($options);
        $mp = $app->mini_program;

        $login_code = $this->post['login_code'];
        $nickname = $this->post['nickname'];
        $avatar = $this->post['avatar'];

        $session = $mp->sns->getSessionKey($login_code);

        if($session['openid']){
            $session_id = uniqid();
            $User = IUser::findFirst([
                'wx_openid=:openid:',
                'bind'=>[
                    'openid'=>$session['openid']
                ]
                
            ]);

            if(!$User){
                
                $User = new IUser;
                $User->assign([
                    'name'=>$nickname,
                    'avatar'=>$avatar,
                    'wx_nickname'=>$nickname,
                    'wx_avatar'=>$avatar,
                    'wx_openid'=>$session['openid'],
                    'wx_session_key'=>$session['session_key'],
                    'wx_session_id'=>$session_id,
                ]);

                if(!$User->save()){
                    throw new \Exception('用户保存失败 '.$User->getErrorMsg());
                }
            }
            else{
                $User->name = $nickname;
                $User->avatar = $avatar;
                $User->wx_session_id = $session_id;
                $User->wx_session_key = $session['session_key'];
                $User->save();
            }
        }
        else{
            throw new \Exception('解析微信数据失败');
        }

        $this->sendJSON([
            'data'=>[
                'session_id'=>$session_id,
                'user_id'=>$User->user_id,
                'level_id'=>$User->level_id,
                'token'			=>	$User->token,
                'secret_key'    =>  $User->secret_key,
                'level_id'=>$User->level_id,
                'level_name'=>$User->UserLevel->level_name,
            ],
        ]);
    }

    public function wxConnectAction(){
        $session_id = $this->post['session_id'];

        if($session_id){
            $User = IUser::findFirst([
                "wx_session_id like :session_id:",
                'bind'=>[
                    'session_id'=>$session_id
                ]
                
            ]);

            // var_dump($User->toArray());exit;
        }        

        if($User){
            $this->sendJSON([
                'data'=>[
                    'session_id'=>$User->wx_session_id,
                    'user_id'=>$User->user_id,
                    'level_id'=>$User->level_id,
                ],
            ]);
        }
        else{
            $this->sendJSON([]);
        }
    }

    public function appAuthAction(){

        $code = $this->post['code'];

        if(empty($code)){
			throw new \Exception('必须提供code参数',2001);
        }
        
        $User = Wechat::auth($code);

        if($User){
            $this->sendJSON([
                'data'=>[
                    'act'=>'login',
                    'token'			=>	$User->token,
                    'secret_key'    =>  $User->secret_key,
                    'level_id'=>$User->level_id,
                    'level_name'=>$User->UserLevel->level_name,
                ]
                
            ]);
        }
        else{
            throw new \Exception('授权登录失败');
        }
    }
}
