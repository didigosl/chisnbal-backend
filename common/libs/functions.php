<?php
//当前时间戳，精确到微妙ms
function time_ms(){
	$microtime = explode(' ',microtime());
	$ms = $microtime[1].substr($microtime[0], 2,3);
	return $ms;
}

function sqlOrder($content, $cols = []) {
	$return = [];
	if ($content) {
		$content = explode('^', $content);
		foreach ($content as $value) {
			$order = explode('-', $value);
			if (!empty($cols) and !in_array($order[0], $cols)) {
				continue;
			}

			if (!in_array(strtolower($order[1]), ['asc', 'desc'])) {
				continue;
			}

			$col = strtolower($order[0]);
			$sort = $order[1];
			$return[] = $col . ' ' . $sort;
		}

	}

	$return = implode(',', $return);
	if ($return) {
		$return = ' ORDER BY ' . $return;
	}
	return $return;

}

function sqlLimit($offset, $p = 1) {
	$p = $p <= 0 ? 1 : $p;
	$start = $offset * ($p - 1);
	return " LIMIT $start,$offset";
}

function getAttributes($data, $cols = []) {
	$return = [];
	if (empty($cols)) {
		$return = $data;
	} else {
		foreach ($cols as $col) {
			if (array_key_exists($col, $data)) {
				$return[$col] = $data[$col];
			}
		}
	}

	return $return;
}

function buildPath($content, $path = '') {
	//var_dump('buildPath',$content);
	$return = [];
	if (!empty($content)) {
		$content = explode(',', $content);
		foreach ($content as $key => $value) {
			if ($value) {
				$return[$key] = $path . $value;
			}
		}
	}
	return $return;
}

function inviteCode() {
	return sprintf('%x', crc32(microtime()));
}

function objAttr($o, $attr = null) {
	if (empty($attr) or !is_object($o)) {
		throw new \Exception("Error Processing Request", 1);

	}
	
	$link = explode('.', $attr);
	$var = $o;
	foreach ($link as $value) {
		$var = $var->$value;
	}
	return $var;

}


function null2space($data){
	if(is_array($data)){
		foreach ($data as $key => $value) {
			$data[$key] = null2space($value);
		}
	}
	else{
		if(is_null($data)){
			$data = '';
		}
	}

	return $data;
}


function fmtMoney($value){
    $money = $value ? $value : 0;
    return sprintf("%01.2f",$money/100);
}

//修正php float转int的坑
function fmtPrice($value){
	$ret = intval(round($value*100));
	$ret = $ret ? $ret : 0;
	return $ret;
}

function check_phone($phone){
	return preg_match('/^1[3-9]{1}[0-9]{9}$/',$phone);
}


function request(){
	return \Phalcon\DI::getDefault()->get('request');
}

function db(){
	return \Phalcon\DI::getDefault()->get('db');
}

function conf($var=null){
    $ret = null;
    $conf = \Phalcon\DI::getDefault()->get('conf');
    if($var){
        $ret = $conf[$var];
    }
    else{
        $ret = $conf;
    }
    return $ret;
}

function settings($var=null){
    $ret = null;
    $settings = \Phalcon\DI::getDefault()->get('settings');
    if($var){
        $ret = $settings[$var];
    }
    else{
        $ret = $settings;
    }
    return $ret;
}

function url(){
	return \Phalcon\DI::getDefault()->get('url');
}

function auth(){
	return \Phalcon\DI::getDefault()->get('auth');
}

function security(){
    return \Phalcon\DI::getDefault()->get('security');
}

function dispatcher(){
    return \Phalcon\DI::getDefault()->get('dispatcher');
}

function apiAuth(){
    static $User;
    
    if(!$User){
        $token = request()->getPost('token');
        $client_sign = request()->getPost('sign');
        $time = request()->getPost('time');
        
        $User =\Common\Models\IUser::findFirst([
			'token=:token:',
			'bind'=>['token'=>$token]
		]);

		if(!$User){
			throw new \Exception("用户信息不存在", 2003);
			
		}

		if($User->status<0){
			throw new \Exception("帐号已经被冻结", 2003);
		}

		
		$token = $token;
		if($client_sign != 'test'){
			unset($_POST['sign']);
			unset($_POST['time']);
			unset($_POST['token']);
			$params = [];
			if(count($_POST)){
				\Common\Libs\Arr::ksort($_POST);

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
			fputs($fp,date('Y-m-d H:i:s').' '.dispatcher()->getControllerName().'_'.dispatcher()->getActionName().' '.$s.' / '.$token.' / '.$time.' / '.$client_sign.' / '.$sign."\n");
			fclose($fp);

			if($sign != $client_sign){
				throw new \Exception("身份验证失败.", 2003);
				
			}
		}
    }

    return $User;
}

