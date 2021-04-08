<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use Phalcon\Exception;
use Common\Models\IPhoneCode;
use Common\Components\Log;
use Qcloud\Sms\SmsSingleSender;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

class Sms extends Component {

    static public $appkey= '';
    static public $secretKey= '';
    static public $templates = [];


    static public function sendPhoneCode($country_code,$phone,$code){

        $Code = new IPhoneCode();
        $Code->assign([
            'phone'=>$phone,
            'country_code'=>$country_code,
            'code'=>$code
        ]);

        $conf = \Phalcon\Di::getDefault()->get('conf');
        $settings = \Phalcon\Di::getDefault()->get('settings');

        if($Code->save()){
            if($conf['sms_serivece']=='sendinblue'){
                // if(self::sendinblueSend($phone,'您好,您在'.$setting['app_name'].'的验证码是：'.$code)){
                //使用配置好的短信模板
                if($conf['sendinblue_phone_code_content']){
                    $content = str_replace('[code]',$code,$conf['sendinblue_phone_code_content']);
                }
                else{
                    $content = 'Hello,the verify code is:'.$code;
                }

                if(self::sendinblueSend($country_code,$phone,$content)){
                    return true;
                }
            }
            else{
                if(self::send($conf['sms_login_tpl'],$country_code,$phone,[$code,'5'])){
                    return true;
                }
            }
            
        }
        else{
            throw new \Exception($Code->getErrorMsg(), 2001);
            
        }
        
    }

    static public function sendinblueSend($country_code,$phone,$content){

        $log = (new Log())->init('sms.txt');
        $log->write('phone:'.$phone);
        $log->write('content:'.$content);

        $conf = \Phalcon\Di::getDefault()->get('conf');
        $settings = \Phalcon\Di::getDefault()->get('settings');

        $country_code = $country_code ? $country_code : $settings['sms_country_code'];
        $country_code = ltrim($country_code,'0+');
        include_once APP_PATH.'/common/libs/sendinblue_sms_api.php';

        $mailin = new \MailinSms($conf['sendinblue_key']);        

        $phone_num = '00'.$country_code.$phone;
        // $mailin->addTo('00'.$settings['sms_country_code'].$phone)
        $mailin->addTo($phone_num)
        // ->setFrom($settings['app_name'])
        ->setFrom('didigoes')
        ->setText($content) // 160 characters per SMS.
        // ->setTag('Your tag name')
        ->setType('marketing'); // Two possible values: marketing or transactional.
        // ->setCallback('http://callbackurl.com/');

        $res = $mailin->send();
        $log->write(var_export($res,true));

        if($res){
            $result = json_decode($res);
            if($result->status=='OK'){
                return true;
            }
            else{
                throw new \Exception($phone_num.$result->description);
            }
        }
        else{
            throw new \Exception('短信调用失败');
        }
    }

    static public function send($tpl,$country_code,$phone,$params){
        if(SERV_ENV=='p'){
            
            try {

                $settings = \Phalcon\Di::getDefault()->get('settings');
                $conf = \Phalcon\Di::getDefault()->get('conf');
                $country_code = $country_code ? $country_code : $settings['sms_country_code'];
                $country_code = $country_code ? $country_code : '86';

                $sms_qq_sign = $conf['sms_qq_sign'] ? $conf['sms_qq_sign'] : '';

                $sender = new SmsSingleSender($conf['sms_qq_app_id'], $conf['sms_qq_app_key']);

                $result = $sender->sendWithParam($country_code, $phone, $tpl, $params,$sms_qq_sign);
//                var_dump($result);die;
//                var_dump(date("Y-m-d H:i:s",time()));die;
                $logger = new FileAdapter(SITE_PATH.'/logs/sms.txt');
                $logger->info($result);

                $rsp = json_decode($result);

                if($rsp->ActionStatus=='FAIL' OR $rsp->result!=0){
                    throw new \Exception("请稍后再试", 1001);
                }
                else{
                    return true;

                }

            } catch(\Exception $e) {
                throw new \Exception("短信发送失败，".$e->getMessage(), 1001);

            }
        }

        return true;

    }


	
}
