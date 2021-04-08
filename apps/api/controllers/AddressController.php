<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IAddress;
use Common\Models\IArea;
use Common\Libs\Func;
use Common\Components\ValidateMsg;

class AddressController extends ControllerAuth {

	public function listAction(){

		$adds = IAddress::find([
			'user_id=:user_id:',
			'bind'=>['user_id'=>$this->User->user_id],
			'order'=>'default_flag DESC,update_time DESC'
		]);

		if($adds){
			foreach ($adds as $Address) {
                $Address->afterFind();
				$data = [
					'address_id'=>$Address->address_id,
					'man'=>$Address->man,
					'phone'=>$Address->phone,
					'area_id'=>$Address->area_id,
                    'area'=>$Address->area,
                    'area_json'=>json_decode($Address->area_json,JSON_UNESCAPED_UNICODE),
					'city_name'=>$Address->city_name,
					'address'=>$Address->address,
					'postcode'=>$Address->postcode,
					'default_flag'=>$Address->default_flag
				];

				if($Address->area_id){
					if($Address->Area->level==2){
						$data['province_id'] = $Address->Area->area_id;
						$data['province'] = $Address->Area->name;
						$data['city_id'] = null;
						$data['city'] = null;
					}
					elseif($Address->Area->level==3){
						$data['province_id'] = $Address->Area->Parent->area_id;
						$data['province'] = $Address->Area->Parent->name;					
						$data['city_id'] = $Address->Area->area_id;
						$data['city'] = $Address->Area->name;
					}
				}


				$list[] = $data;
			}
		}

		$this->sendJSON([
			'data'=>$list
			
		]);

	}

	public function getDefaultAction(){

		$Address = IAddress::findFirst([
			'user_id=:user_id: AND default_flag=1',
			'bind'=>['user_id'=>$this->User->user_id],
		]);

		if(!$Address){
			$data = null;
		}
		else{
			$data = [
				'address_id'=>$Address->address_id,
				'man'=>$Address->man,
				'phone'=>$Address->phone,
				'area_id'=>$Address->area_id,
                'area'=>$Address->area,
                'area_json'=>json_decode($Address->area_json,JSON_UNESCAPED_UNICODE),
				'city_name'=>$Address->city_name,
				'address'=>$Address->address,
				'postcode'=>$Address->postcode,
				'default_flag'=>$Address->default_flag
			];

			if($Address->area_id){
				if($Address->Area->level==2){
					$data['province_id'] = $Address->Area->area_id;
					$data['province'] = $Address->Area->name;
					$data['city_id'] = null;
					$data['city'] = null;
				}
				elseif($Address->Area->level==3){
					$data['province_id'] = $Address->Area->Parent->area_id;
					$data['province'] = $Address->Area->Parent->name;					
					$data['city_id'] = $Address->Area->area_id;
					$data['city'] = $Address->Area->name;
				}
			}
		}

		

		$this->sendJSON([
			'data'=>$data
		]);
	}


	public function updateAction(){

		$city_id = $this->post['city_id'];
        $province_id = $this->post['province_id'];
        $area_id = $this->post['area_id'];

        if(empty($area_id)){
            $area_id = $city_id ? $city_id : ($province_id ? $province_id : 0);
        }
        
        $area_arr = [];
        $area_json = $this->post['area_json'];
        if(!empty($area_json)){
            $area_arr = json_decode($area_json,JSON_UNESCAPED_UNICODE);
            if($area_arr){
                $area_json = json_encode($area_arr,JSON_UNESCAPED_UNICODE);
            }
            else{
                $area_json = '';
            }
        }
        else{
            $area_json = '';
        }

        if($area_id && empty($area_json)){
            $Area = IArea::findFirst($area_id);
            if($Area){
                $area_arr = $Area->getParentNames();
                if($area_arr){
                    $area_json = json_encode($area_arr,JSON_UNESCAPED_UNICODE);
                }
                else{
                    $area_json = '';
                }
            }
        }

		$data = [
            'area_id'=>$area_id,
            'area_json'=>$area_json,
            'area'=>implode(' ',$area_arr),
			'city_name'=>$this->post['city_name'],
			'address'=>$this->post['address'],
			'postcode'=>$this->post['postcode'],
			'man'=>$this->post['man'],
			'phone'=>$this->post['phone'],
			'default_flag'=>(int)$this->post['default_flag'],
		];
		$address_id = $this->post['address_id'];
		
		if($address_id){
			$Address = IAddress::findFirst($address_id);
			if(!$Address){
				throw new \Exception('地址信息不存在', 2002);
				
			}

			if($Address->user_id != $this->User->user_id){
				throw new \Exception("没有权限，地址修改失败", 2004);
				
			}

		}
		else{

			$Address = new IAddress;
			$data['user_id'] = $this->User->user_id;

		}
		
		$Address->assign($data);
		if($Address->save()){
			
			$this->sendJSON([]);;
		}
		else{

			throw new \Exception($Address->getErrorMsg(), 2001);
			
		}

	}


	public function setDefaultAction(){
		$address_id = $this->post['address_id'];

		if($address_id){
			$Address = IAddress::findFirst($address_id);
			if(!$Address){
				throw new \Exception('地址信息不存在', 2002);
				
			}

			if($Address->user_id != $this->User->user_id){
				throw new \Exception("没有权限，地址修改失败", 2004);
				
			}

			if($Address->setDefault($this->User->user_id)){
				$this->sendJSON([]);
			}
		}
		else{
			throw new \Exception("缺少参数", 2001);
			
		}
	}

	public function deleteAction(){

		$address_id = $this->post['address_id'];
		$Address = IAddress::findFirst($address_id);
		if(!$Address){
			throw new \Exception("地址信息不存在", 1);
			
		}

		$this->db->begin();
		if($Address->delete()){
			$this->db->commit();
			$this->sendJSON([
				'data'=>null,
			]);
		}
		else{
			$this->db->rollback();
			throw new \Exception("Error Processing Request", 1);
			
		}
	}

}
