<?php
namespace Admin\Controllers;
use \Common\Components\Assets;
use \Common\Libs\DocParser;
use \Common\Libs\Str;
use \Common\Models\SAclResource;
use \Common\Models\SAclAction;
use \Common\Models\SAclAccess;
use Admin\Components\ControllerAuth;

/**
 * @desc ACL基础管理
 * @acl super
 * @aclCustom false
 */
class AclController extends ControllerAuth {

	public $access_roles = [''=>'*',2=>'superadmin',3=>'shopadmin'];

	public function initAction(){
		$this->db->execute("DELETE FROM s_acl_resource");
		$this->db->execute("DELETE FROM s_acl_action");
		$this->db->execute("DELETE FROM s_acl_access");

		$this->db->insertAsDict('s_acl_resource',[
			'id'=>1,
			'name'=>'acl',
			'desc'=>'',
			'intro'=>'',
			'custom_flag'=>0,

		]);

		$this->db->insertAsDict('s_acl_resource',[
			'id'=>2,
			'name'=>'dashboard',
			'desc'=>'',
			'intro'=>'',
			'custom_flag'=>0,
			
		]);

		$this->db->insertAsDict('s_acl_action',[
			'id'=>1,
			'name'=>'generate',
			'desc'=>'',
			'resource_id'=>1,
			'resource_name'=>'acl',
			'custom_flag'=>0,

		]);

		$this->db->insertAsDict('s_acl_action',[
			'id'=>2,
			'name'=>'init',
			'desc'=>'',
			'resource_id'=>1,
			'resource_name'=>'acl',
			'custom_flag'=>0,

		]);

		$this->db->insertAsDict('s_acl_action',[
			'id'=>3,
			'name'=>'index',
			'desc'=>'',
			'resource_id'=>2,
			'resource_name'=>'dashboard',
			'custom_flag'=>0,

		]);

		$this->db->insertAsDict('s_acl_action',[
			'id'=>4,
			'name'=>'error',
			'desc'=>'',
			'resource_id'=>2,
			'resource_name'=>'dashboard',
			'custom_flag'=>0,

		]);

		$this->db->insertAsDict('s_acl_access',[
			'id'=>1,
			'role_id'=>1,
			'role_name'=>'super',
			'resource_id'=>null,
			'resource_name'=>'*',
			'action_id'=>null,
			'action_name'=>'*',
			'allow_flag'=>1

		]);

	}

	public function generateAction(){

		$dir = new \DirectoryIterator(dirname(__FILE__));
		foreach ($dir as $fileinfo) {
		    if (!$fileinfo->isDot()) {
		    	$file = $fileinfo->getFilename();
		    	$file_name = strstr($file,'.',true);
		    	
		    	if(preg_match('/Controller$/', $file_name)){
		    		$this->_parse('Admin\\Controllers\\'.$file_name);
		    	}
		        
		    }
		}
		// $this->_parse('Admin\\Controllers\\IndexController');
	}

	private function _parse($file){

		$Reflection = new \ReflectionClass($file);
		$methods = $Reflection->getMethods (\ReflectionMethod::IS_PUBLIC);
		$doc = $Reflection->getDocComment ();
		$parsed_doc = DocParser::getInstance()->parse($doc);

		// var_dump($parsed_doc);exit;

		$class_name = Str::toUnderScore(substr(basename($Reflection->getName()),0,-10));
		$class_desc = $parsed_doc['aclDesc'] ? $parsed_doc['aclDesc']:'';
		$class_acl = $parsed_doc['acl'] ? $parsed_doc['acl']:'';
		if(!empty($class_acl)){
			$class_acl = explode(',', $class_acl);
		}

		//哪些权限可以自由定制
		if(!empty($parsed_doc['aclCustom'])){
			if($parsed_doc['aclCustom']=='false'){
				// $class_custom = 0;
				$class_super_custom = 0;
				$class_single_shop_custom = 0;
				$class_multi_shop_custom = 0;
			}
			else{
				$class_super_custom = 0;
				$class_single_shop_custom = 0;
				$class_multi_shop_custom = 0;

				$custom_arr = explode(',', $parsed_doc['aclCustom']);

				if(in_array('super', $custom_arr)){
					$class_super_custom = 1;
				}
				if(in_array('single_shop', $custom_arr)){
					$class_single_shop_custom = 1;
				}
				if(in_array('multi_shop', $custom_arr)){
					$class_multi_shop_custom = 1;
				}

			}
		}
		
		// $class_custom = $parsed_doc['aclCustom']=='false' ? 0 : 1;
		
		
		echo $file.'<br>';
		echo 'class_acl:'.$class_acl.'<br>';
		
		$Resource = SAclResource::findFirst([
			'name=:name:',
			'bind'=>[
				'name'=>$class_name,
			]
		]);
		
		if(!$Resource){
			$Resource = new SAclResource;
			$Resource->assign([
				'name'=>$class_name,
				'desc'=>$class_desc,
				// 'custom_flag'=>$class_custom
				'super_custom_flag'=>$class_super_custom,
				'single_shop_custom_flag'=>$class_single_shop_custom,
				'multi_shop_custom_flag'=>$class_multi_shop_custom,
			]);
			$Resource->save();
		}
		else{
			$Resource->desc = $class_desc;
			// $Resource->custom_flag = $class_custom;
			$Resource->super_custom_flag = $class_super_custom;
			$Resource->single_shop_custom_flag = $class_single_shop_custom;
			$Resource->multi_shop_custom_flag = $class_multi_shop_custom;
			$Resource->save();
		}

		if(is_array($class_acl)){
			foreach ($class_acl as $acl) {
				if(in_array($acl, $this->access_roles)){
					echo 'in array <br>';
					$role_id = array_search($acl, $this->access_roles);
					$Access = SAclAccess::findFirst([
						'role_name=:role_name: AND resource_name=:resource_name: AND action_name="*"',
						'bind'=>[
							'role_name'=>$acl,
							'resource_name'=>$Resource->name,

						]
					]);
					if(!$Access){
						$Access = new SAclAccess;
						$Access->assign([
							'role_id'=>$role_id,
							'role_name'=>$acl,
							'resource_id'=>$Resource->id,
							'resource_name'=>$Resource->name,
							'action_name'=>'*',
							'allow_flag'=>1
						]);
						$Access->save();
					}
				}
			}
		}

		foreach ($methods as $key => $method) {

			if(preg_match('/Action$/', $method->name)){
				$method_name = substr($method->name,0,-6);
				echo 'method_name:'.$method_name.'<br>';
				$method_doc = '';
				$method_doc = $method->getDocComment();
				$parsed_method_doc = DocParser::getInstance()->parse($method_doc);
				//var_dump($parsed_method_doc);
				$method_desc = $parsed_method_doc['aclDesc'] ? $parsed_method_doc['aclDesc'] : '';
				$method_acl = $parsed_method_doc['acl'] ? $parsed_method_doc['acl'] : '';

				echo 'method_acl:'.$method_acl.'<br>';
				if(!empty($method_acl)){
					$method_acl = explode(',', $method_acl);
				}

				if($class_custom===0){
					$method_custom = 0;
				}
				else{
					$method_custom = $parsed_method_doc['aclCustom']=='false' ? 0 : 1;
				}

				if(!empty($parsed_method_doc['aclCustom'])){
					if($parsed_method_doc['aclCustom']=='false'){
						// $class_custom = 0;
						$method_super_custom = 0;
						$method_single_shop_custom = 0;
						$method_multi_shop_custom = 0;
					}
					else{
						$method_super_custom = 0;
						$method_single_shop_custom = 0;
						$method_multi_shop_custom = 0;

						$custom_arr = explode(',', $parsed_method_doc['aclCustom']);

						if(in_array('super', $custom_arr)){
							$method_super_custom = 1;
						}
						if(in_array('single_shop', $custom_arr)){
							$method_single_shop_custom = 1;
						}
						if(in_array('multi_shop', $custom_arr)){
							$method_multi_shop_custom = 1;
						}

					}
				}
				else{
					$method_super_custom = $class_super_custom;
					$method_single_shop_custom = $class_single_shop_custom;
					$method_multi_shop_custom = $class_multi_shop_custom;
				}
				

				$Action = SAclAction::findFirst([
					"resource_id=:resource_id: AND name=:name:",
					'bind'=>[
						'resource_id'=>$Resource->id,
						'name'=>$method_name,
					]
				]);

				if(!$Action){
					$Action = new SAclAction;
					$Action->assign([
						'resource_id'=>$Resource->id,
						'resource_name'=>$Resource->name,
						'name'=>$method_name,
						'desc'=>$method_desc,
						'super_custom_flag'=>$method_super_custom,
						'single_shop_custom_flag'=>$method_single_shop_custom,
						'multi_shop_custom_flag'=>$method_multi_shop_custom,

					]);
					$Action->save();
				}
				else{
					$Action->assign([
						'desc'=>$method_desc,
						// 'custom_flag'=>$method_custom,
						'super_custom_flag'=>$method_super_custom,
						'single_shop_custom_flag'=>$method_single_shop_custom,
						'multi_shop_custom_flag'=>$method_multi_shop_custom,
					]);
					$Action->save();
				}
				
				if(empty($class_acl) and !empty($method_acl)){
					if(is_array($method_acl)){
						foreach ($method_acl as $acl) {
							if(in_array($acl, $this->access_roles)){
								$role_id = array_search($acl, $this->access_roles);
								$Access = SAclAccess::findFirst([
									'role_name=:role_name: AND resource_name=:resource_name: AND (action_name="*" OR action_name=:action_name:)',
									'bind'=>[
										'role_name'=>$acl,
										'resource_name'=>$Resource->name,
										'action_name'=>$Action->name,
									]
								]);
								if(!$Access){
									$Access = new SAclAccess;
									$Access->assign([
										'role_id'=>$role_id,
										'role_name'=>$acl,
										'resource_id'=>$Resource->id,
										'resource_name'=>$Resource->name,
										'action_id'=>$Action->id,
										'action_name'=>$Action->name,
										'allow_flag'=>1
									]);
									$Access->save();
								}
							}
						}
					}
					
					
				}
				
			}
		
		}
		echo '<hr>';
		//exit;
	}

	public function testAction(){
		/*$s = '';
		var_dump(explode(',', $s));exit;*/

		$s = 'BAdmin';
		var_dump(Str::toUnderScore($s));
		exit;
	}

}