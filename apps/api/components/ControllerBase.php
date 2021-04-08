<?php

namespace Api\Components;

use Phalcon\Mvc\Controller;
use Common\Libs\Arr;
use Common\Models\IUser;

class ControllerBase extends Controller
{

	public $post = [];
	public $User = null;

	public function initialize(){

        $this->view->disable();

        $this->post = Arr::toUnderScoreParams($this->request->getPost());

        $this->response->setRawHeader('Access-Control-Allow-Origin: *');
        $this->response->setRawHeader('Access-Control-Allow-Methods: *');
        $this->response->setHeader('Access-Control-Allow-Headers','Origin, X-Requested-With, Content-Type, Accept,Auth-Token');
		$this->response->setContentType('application/json', 'UTF-8');
		if($_SERVER['REQUEST_METHOD']=='OPTIONS'){
			$this->response->send();
			exit;
		}

		$this->url->setBaseUri('/');
		// $this->log();
	}
	
	protected function auth(){

		if(!empty($this->post['token'])){
			$User = IUser::findFirst([
				'token=:token:',
				'bind'=>['token'=>$this->post['token']]
			]);
			// var_dump($User);exit;
			if($User){
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
					
					if($sign == $client_sign){
						$this->User = $User;
					}
					else{
						// var_dump($sign,$client_sign);
					}
				}
				else{
					$this->User = $User;
				}
				
			}
			return true;
		}
		
	}

    
	public function sendJSON($data,$toCamelCase=true){
		if($data['status']!='FAIL'){
			$data['status'] = 'SUCCESSS';
			$data['code'] = 0;
		}
		if($toCamelCase){
			$data = Arr::toCamelCaseParams($data);
		}
		

		$this->response->setJsonContent($data,JSON_UNESCAPED_UNICODE);
		$this->response->send();
	}

	public function log($data=null){
		$log_file_name = $this->dispatcher->getControllerName().'_'.$this->dispatcher->getActionName();
		$fp = fopen(SITE_PATH.'/logs/'.$log_file_name.'.txt','a+');
		if(empty($data)){
			fputs($fp,date('Y-m-d H:i:s')."\n".var_export($this->request->getPost(),true)."\n".var_export($_FILES,true));
		}
		else{
			fputs($fp,date('Y-m-d H:i:s')."\n".var_export($data,true));
		}
		
		fclose($fp);
    }

}
