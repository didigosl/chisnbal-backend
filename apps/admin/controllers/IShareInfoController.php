<?php
namespace Admin\Controllers;

use Admin\Components\FileSys;
use Common\Models\IShareInfo;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Exception;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * @aclDesc 分享信息
 * @acl superadmin,shopadmin
 * @aclCustom super,single_shop
 */
class IShareInfoController extends ControllerAuth {

	public function initialize() {
		parent::initialize();
		$this->controller_name = '分享信息';
		$this->view->setVar('controller_name',$this->controller_name);
	}


	/**
	 * @aclDesc 修改
	 * @return [type] [description]
	 */
	public function updateAction(){
		$this->modify();
	}

	public function form(){
		if($this->flashSession->has('error')){
			$cached_data = $this->getCachedFormData();
		}
		$id = $this->request->getQuery('id','int');

		$M = IShareInfo::findFirst($id);
		
		if(!$M){
			$M = new IShareInfo;
			if($cached_data){
				$M->assign($cached_data);
			}
		}
		
		$this->view->setVars([
			'M'=>$M,
		]);		
		
		if($this->request->isAjax()){	

			$this->view->disable();
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),'form');
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			
			$this->sendJSON($data);
		}
		else{
			$this->view->pick($this->dispatcher->getControllerName().'/form');
		}
	}

	public function modify(){

		if ($this->request->isPost()) {	
			$this->cacheFormData();
			$id = $this->request->getPost('id');		
			$M = IShareInfo::findFirst($id);		
			if(!$M){
				$M = new IShareInfo;
			}
			
			$data['title'] = $this->request->getPost('title');
			$data['sub_title'] = $this->request->getPost('sub_title');
			$data['app_name'] = $this->request->getPost('app_name');
            $data['app_sub_name'] = $this->request->getPost('app_sub_name');
            $data['ios_url'] = $this->request->getPost('ios_url');
            $data['android_url'] = $this->request->getPost('android_url');
            $data['intro'] = $this->request->getPost('intro');
            
            $upload_dir = 'shop'.$this->auth->getShopId().'/image';
			$path = FileSys::upload($upload_dir, ['logo']);
			if ($path) {
				$data['logo'] = $path;
            }
            
            $pics = $this->request->getPost('pics');
			if(count($pics)){
				$data['pics'] = implode(',', $pics);
			}
			else{
				$data['pics'] = '';
			}

			$this->db->begin();
			$M->assign($data);

			if ($M->save()) {
				$this->db->commit();

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->id,$M->title);
				$this->flashSession->success("数据提交成功");
				$this->clearCachedFormData();
				$this->jump($this->url->get($base_url."/form", [], false));

			} else {
				$this->db->rollback();
				throw new \Exception($M->getErrorMsg(), 1);

			}
		}
		else{
			$this->form();
		}
	}

	/**
	 * 表单
	 */
	public function formAction(){

        $id = $this->request->getQuery('id','int');
        
        $id = 1;

		$M = IShareInfo::findFirst($id);
		
		if(!$M){
			$M = new IShareInfo;
		}
		else{
			$M->videos = json_decode($M->videos);
			$M->audios = json_decode($M->audios);
			if (!$M) {
				throw new \Exception("数据不存在", 1);

			}
		}
        $this->breadcrumbs[] = [
            'text'=>$this->controller_name,
            'url'=>''
        ];

		$this->view->setVars([
			'M'=>$M
		]);		
		
           
	}


}