<?php
namespace Common\Components\SuhoExpress;

use Phalcon\Mvc\User\Component;
use Common\Components\Log;
use Phalcon\Logger\Adapter\File as FileAdapter;
use GuzzleHttp\Client as HttpClient;
use \Curl\Curl;

class Express extends Component {

    //价格接口
    static public $priceUrl= 'https://www.suhoexpress.com/api/weight';

    static public function fee($area_id,$params=[]){

        $log = (new Log())->init('suhoexpress.txt');
        // $log->write('phone:'.$phone);
        // $log->write('content:'.$content);

        /* $Http = new HttpClient();
        $response = $Http->post(self::$priceUrl,[
            'body'=>[
                'price'=>1,
                'weight'=>[
                    'chang'=>$params['length'],
                    'kuan'=>$params['width'],
                    'gao'=>$params['height'],
                ]
            ]
        ]);
        
        if($response->getStatusCode()!=200){
            throw new \Exception('运费计算发送意外错误');
        }

        $body = $response->getBody();
        echo ($body); */


        $curl = new Curl();
        $curl->post(self::$priceUrl, [
            'price'=>1724,
            'weight'=>[
                'chang'=>$params['length'],
                'kuan'=>$params['width'],
                'gao'=>$params['height'],
            ],
          
        ]);
        if ($curl->error) {
            throw new \Exception('计算运费发生意外 '.$curl->errorMessage);
            // echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
           
            if(!isset($curl->response->err)){
                // $price = $curl->response->price;
                return $curl->response;
            }
            else{
                throw new \Exception('计算运费发生意外 '.$curl->response->msg);
            }
        }

        // return fmtPrice($price);
        
    }
	
}
