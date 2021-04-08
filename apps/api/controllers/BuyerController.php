<?php

namespace Api\Controllers;

use Api\Components\ControllerBuyer;
use Common\Models\IBuyer;
use Common\Libs\Func;
use Common\Components\ValidateMsg;
use EasyWeChat\Foundation\Application;

class BuyerController extends ControllerBuyer {

    public function listAction(){
        $this->auth();

        $buyers = IBuyer::find([
            'shop_id=:shop_id:',
            'bind'=>[
                'shop_id'=>$this->Buyer->shop_id
            ]
        ]);

        $list = [];
        foreach($buyers as $item){
            $list[] = [
                'buyer_id'=>$item->buyer_id,
                'username'=>$item->username,
                'phone'=>$item->phone,
                'country_code'=>$item->country_code,
                'name'=>$item->name,
				'gender'=>$item->gender,
                'gender_text'=>$item->gender ? $item->getGenderContext($item->gender) : '',
                'create_time'=>$item->create_time,
            ];

        }
        $this->sendJSON([
            'data'=>$list
        ]);
    }

	public function getInfoAction(){

        $this->auth();
		$this->sendJSON([
			'data'=>[
                'buyer_id'=>$this->Buyer->buyer_id,
                'username'=>$this->Buyer->username,
                'phone'=>$this->Buyer->phone,
                'country_code'=>$this->Buyer->country_code,
				// 'avatar'=>Func::staticPath($this->Buyer->avatar),
				'name'=>$this->Buyer->name,
				'gender'=>$this->Buyer->gender,
                'gender_text'=>$this->Buyer->gender ? $this->Buyer->getGenderContext($this->Buyer->gender) : '',
                'create_time'=>$item->create_time,
			]
			
		]);

	}

	public function updateAction(){

        $this->auth();

		$data = [];	
		if($this->post['name']){
			$data['name'] = $this->post['name'];
		}

		if($this->post['gender']){
			$data['gender'] = $this->post['gender'];
        }
        
		$this->Buyer->assign($data);
		if(!$this->Buyer->save()){
			throw new \Exception($this->Buyer->getErrorMsg(), 2002);
			
		}
		else{
			$this->sendJson([]);
		}
    }
    
    public function loginAction(){
        $username = trim($this->post['username']);
        $password = trim($this->post['password']);

        $Buyer = IBuyer::findFirst([
            'username=:username: AND status>0',
            'bind'=>[
                'username'=>$username,
            ]
        ]);

        if(!$Buyer){
            throw new \Exception('账号或密码错误');
        }

        if(!security()->checkHash($password,$Buyer->password)){
            throw new \Exception('账号或密码错误');
        }

        if($Buyer->status<0){
            throw new \Exception("帐号已经被冻结", 1);            
        }

        $this->sendJSON([
            'data'=>[
                'act'=>'login',
                'phone'     	=>  $Buyer->phone,
                'country_code'  =>  $Buyer->country_code,
                'token'			=>	$Buyer->token,
                'secret_key'    =>  $Buyer->secret_key,
                'name'          => $Buyer->name
            ]
            
        ]);
    }
}
