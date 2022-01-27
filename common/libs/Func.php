<?php
namespace Common\Libs;

class Func {

    static public function staticPath($url) {
        $request = \Phalcon\Di::getDefault()->get('request');
        if(strpos($url,'http://')!==false || strpos($url,'https://')!==false){
            return $url;
        }
        else{
            $conf = \Phalcon\Di::getDefault()->get('conf');
            $static_domain = $conf['static_domain'];
            if(empty($static_domain)){
                $static_domain = $request->getHttpHost();
            }
            return $url ? $request->getScheme ().'://'.$static_domain.$url : null;
        }
        
    }

    /**
     * 给富文本内容中的图片添加完整路径
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    static public function contentStaticPath($content){

        $conf = \Phalcon\Di::getDefault()->get('conf');
        $static_domain = $conf['static_domain'];
        if(empty($static_domain)){
            $static_domain = request()->getHttpHost();
        }

        return preg_replace('/src="(\S+?)"/', 'src="'.request()->getScheme ().'://'.$static_domain.'$1"', $content);
    }

    static public function makeOrderNum(){

        if(php_sapi_name() == "cli") {
            $ip = '127.0.0.1';
        }
        else{
            $request = \Phalcon\Di::getDefault()->get('request');
            $ip = $request->getClientAddress();
        }
    
    	return date('ymd').substr(time(),6).substr( ip2long($ip), -6).rand(1000,9999);
    }

    static public function makeNum(){

    	return substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 10);
    }

    static public function http_request( $url, $post = [], $timeout = 5 ){
        if( empty( $url ) ){
            return ;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if( $post != '' && !empty( $post ) ){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: '.strlen($post)));
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}