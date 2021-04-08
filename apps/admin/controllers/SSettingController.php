<?php
namespace Admin\Controllers;

use Common\Models\SSetting;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Common\Components\Cache;

/**
 * @aclDesc 系统设置
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class SSettingController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		
		$this->controller_name = '参数设置';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 更新
	 */
	public function settingAction(){

        $conf = conf();
		if($this->request->isPost()){
			$this->view->disable();
			$group = $this->request->getPost('group');
			$data = $this->request->getPost();
			unset($data['group']);

			foreach ($data as $v) {
				if(mb_strlen($v)>1000){
					throw new \Exception("填写的内容不能超出1000个字符", 1);
					
				}
			}

			if('rebate'==$group){
				foreach($data as $v){
					if(strlen($v)==0 OR !is_numeric($v)){
						throw new \Exception("所有返利等级都必须填写，且只可为数字！", 1);
						
					}
				}
			}
			// var_dump($data);exit;
			if('bank'==$group){
				foreach($data as $k=>$v){
					$data[$k] = trim($v);
					if(strlen($v)==0){
						throw new \Exception("请完整填写所有汇款资料", 1);
						
					}

					if($k=='bank' and mb_strlen($v)>100){
						throw new \Exception("开户行内容不能超过100字", 1);
						
					}

					if($k=='bank_account' and mb_strlen($v)>100){
						throw new \Exception("汇款账号内容不能超过100字", 1);
						
					}

					if($k=='bank_intro' and mb_strlen($v)>500){
						throw new \Exception("开户行内容不能超过500字", 1);
						
					}
				}
            }

            if('other'==$group){
                if(isset($data['min_order_amount'])){
                    if(strlen($data['min_order_amount'])==0 OR !is_numeric($data['min_order_amount'])){
                        throw new \Exception("订单最小金额只可为数字！", 1);
                        
                    }
                }
				if(!isset($data['user_review'])){
					$data['user_review'] = 0;
				}

				
			}

			$this->db->begin();
			/*
			try{
				foreach ($data as $k => $v) {
					$Setting = SSetting::findFirst(['name=:name:','bind'=>['name'=>$k]]);
					if(!$Setting){
						throw new \Exception("设置参数不存在", 1);
					}

					$Setting->value = $v;
					if(!$Setting->save()){
						throw new \Exception($Setting->getErrorMsg(), 1);
						
					}

				}
				
				if($group=='delivery_fee'){
					SAdminLog::add('s_setting','setting','','全局运费');
				}
				elseif($group=='bank'){
					SAdminLog::add('s_setting','setting','','线下付款银行账号信息');
				}
				elseif($group=='delivery_fee'){
					SAdminLog::add('s_setting','setting','','多级会员返利');
				}
				else{
					SAdminLog::add('s_setting','setting','','');
				}
				$this->db->commit();

				$this->flashSession->success("系统参数更新成功");
				$this->sendJSON([
						'status'=>1,
					]);
				}
				// exit;
				if($this->request->isAjax()){
					var_dump();
					
				else{
					$this->jump($this->url->get($this->request->getHTTPReferer()));
				}
				
				// exit;
			} catch (\Exception $e){

				$this->db->rollback();
				$this->sendJSON([
						'status'=>0,
						'msg'=>$e->getMessage()
					]);
				}
			}*/

			try{
				foreach ($data as $k => $v) {
					$Setting = SSetting::findFirst(['name=:name:','bind'=>['name'=>$k]]);
					if(!$Setting){
						throw new \Exception("设置参数不存在", 1);
					}

					$Setting->value = $v;
					if(!$Setting->save()){
						throw new \Exception($Setting->getErrorMsg(), 1);
						
					}

				}
				unlink(SITE_PATH.'/../runtime/cache/settings');

				if($group=='delivery_fee'){
					SAdminLog::add('s_setting','setting','','全局运费');
				}
				elseif($group=='bank'){
					SAdminLog::add('s_setting','setting','','线下付款银行账号信息');
				}
				elseif($group=='delivery_fee'){
					SAdminLog::add('s_setting','setting','','多级会员返利');
				}
				else{
					SAdminLog::add('s_setting','setting','','');
				}

				$this->db->commit();
				$this->flashSession->success("系统参数更新成功");

				if($this->request->isAjax()){
					$this->sendJSON(['status'=>1]);
				}	
				else{
					$this->jump($this->url->get($this->request->getHTTPReferer()));
				}
				

			} catch (\Exception $e){
				$this->db->rollback();
				$this->sendJSON(['status'=>0,'msg'=>$e->getMessage()]);
			}
			exit;
		}
		else{
			$list = SSetting::find();
			$this->view->setVars([
				'list'=>$list
			]);
		}
	}

	/**
	 * @aclDesc 汇款设置
	 */
	public function bankAction(){
		$this->controller_name = '汇款设置';
		$list = SSetting::find(['name like "bank%"']);
		$this->view->setVars([
			'list'=>$list,
			'controller_name'=>$this->controller_name,
			'group'=>'bank',
		]);

		$this->view->pick($this->controller.'/setting');
	}

	/**
	 * @aclDesc 返利设置
	 */
	public function rebateAction(){
		$this->controller_name = '多级返利设置';
		$list = SSetting::find(['name like "rebate%"']);
		$this->view->setVars([
			'list'=>$list,
			'controller_name'=>$this->controller_name,
			'group'=>'rebate',
		]);

		$this->view->pick($this->controller.'/setting');
    }

    /**
	 * @aclDesc APP分享设置
	 */
	public function AppShareAction(){
        $this->controller_name = 'APP分享设置';
        
        $names = [];
        $names[] = 'ios_download_url';
        $names[] = 'ios_scheme';
        $names[] = 'android_download_url';
        $names[] = 'android_scheme';


		$list = SSetting::find([
            'name in ({names:array})',
            'bind'=>['names'=>$names]
        ]);

		$this->view->setVars([
			'list'=>$list,
			'controller_name'=>$this->controller_name,
			'group'=>'appshare',
		]);

		$this->view->pick($this->controller.'/setting');
    }
    
    /**
	 * @aclDesc 其他设置
	 */
	public function otherAction(){
        $this->controller_name = '其他设置';
        
        $conf = conf();
        $names = [];
        if($conf['enable_min_order_amount']){
            $names[] = 'min_order_amount';
        }

        if($conf['enable_order_notify']){
            $names[] = 'email_for_order_notify';
            $names[] = 'content_of_order_notify';
        }

        if($conf['enable_mail']){
            $names[] = 'mail_smtp_server';
            $names[] = 'mail_smtp_port';
            $names[] = 'mail_account';
            $names[] = 'mail_password';
        }
        
        if($conf['enable_low_stock_warning']){
            $names[] = 'low_stock_amount';
        }

        $names[] = 'order_pay_expired';
        $names[] = 'order_finish_expired';
        //注册用户需要通过审核
		$names[] = 'user_review';
        $list = [];
        if(!empty($names)){
            $list = SSetting::find([
                'name in ({names:array})',
                'bind'=>['names'=>$names]
            ]);
        }
		
		$this->view->setVars([
			'list'=>$list,
			'controller_name'=>$this->controller_name,
			'group'=>'other',
		]);

		$this->view->pick($this->controller.'/setting');
	}

	public function testAction(){
		// $data = Cache::init()->getSetting('app_name');
		// var_dump($data);
		var_dump($this->glbs->settings);
		exit;
	}
	
}