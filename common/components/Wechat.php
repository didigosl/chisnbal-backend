<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use \Curl\Curl;
use Common\Models\IUser;
use Common\Models\IWechatAuth;

class Wechat extends Component
{
    static public function auth($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.conf('wx_open_appid').'&secret='.conf('wx_open_secret').'&code=' . $code . '&grant_type=authorization_code';

        $curl = new Curl();
        $curl->get($url);

        if ($curl->error) {
            throw new \Exception('auth ERROR:'.$curl->errorMessage,$curl->errorCode);
            echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            $response = json_decode($curl->response);
            if(!$response){
                throw new \Exception('微信授权失败');
            }
            // var_dump($curl->response);exit;
            if($response->errcode>0){
                throw new \Exception('微信授权失败 '.$response->errmsg);
            }

            $openid = $response->openid;
            if(!$openid){
                throw new \Exception('微信授权失败 '.$response->errmsg);
            }
            $data = [
                'access_token'=>$response->access_token,
                'refresh_token'=>$response->refresh_token,
                'expired_at'=>time()+$response->expires_in,
            ];

            $userInfo = self::getUserInfo($openid,$data['access_token']);

            file_put_contents(APP_PATH.'/logs/wx_user_info.txt',var_export($data,true).PHP_EOL.var_export($userInfo,true));

            $user_data = [
                'wx_openid'=>$openid,
                'name'=>$userInfo->nickname,
                'gender'=>$userInfo->sex,
                'avatar'=>$userInfo->headimgurl,
                'wx_avatar'=>$userInfo->headimgurl,
                'wx_unionid'=>$userInfo->unionid,
                'wx_nickname'=>$userInfo->unionid,
            ];

            $User = IUser::findFirst([
                'wx_openid=:openid:',
                'bind'=>[
                    'openid'=>$openid
                ]
            ]);

            if(!$User){
                $User = new IUser;
            }

            $User->assign($user_data);
            if(!$User->save()){
                throw new \Exception($User->getErrorMsg());
            }

            /* $data['user_id'] = $User->user_id;
            $WechatAuth = new IWechatAuth;
            $WechatAuth->assign($data);
            $WechatAuth->save(); */

            $User->genSecretKey();
            
            return $User;
            
        }

        
    }

    static public function refreshAcessToken()
    {
        $now = time();
        if($data && $data->expired_at > $now+60 ){
            $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.conf('wx_open_appid').'&grant_type=refresh_token&refresh_token='.$data->refresh_token;

            $curl = new Curl();
            $curl->get($url);

            if ($curl->error) {
                throw new \Exception('refreshAcessToken ERROR:'.$curl->errorMessage,$curl->errorCode);
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            } else {
                $response = json_decode($curl->response);
                $data = [
                    'access_token'=>$response->access_token,
                    'expires_in'=>$response->expires_in,
                    'refresh_token'=>$response->refresh_token,
                    'expired_at'=>time()+$response->expires_in,
                    'scope'=>$response->scope,
                ];

                
                $Cache->save('access_token',json_encode($data));

                return $data;
                
            }
        }
        else{
            throw new \Exception('refresh_token expired');
        }
        
    }

    static public function getUserInfo($openid,$access_token){

        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid;

        $curl = new Curl();
        $curl->get($url);

        if ($curl->error) {
            throw new \Exception('getUserInfo ERROR:'.$curl->errorMessage,$curl->errorCode);
            echo 'getUserInfo Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            $response = json_decode($curl->response);
            return $response;
        }
    }

}
