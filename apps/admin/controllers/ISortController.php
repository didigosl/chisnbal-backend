<?php
namespace Admin\Controllers;

use Common\Models\ISort;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Admin\Components\FileSys;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 平台分类
 * @acl superadmin
 * @aclCustom super
 */
class ISortController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '平台分类';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$sorts = ISort::find([$conditionSql,'bind'=>$params,'order'=>'rank asc']);
	
		// var_dump($rebates);exit;
		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'action_name'=>'列表',
			'sorts' =>	$sorts,
			'vars' => [
			],
		]);

	}

	/**
	 * @aclDesc 新增
	 */
	public function createAction(){
		$this->modify();
	}

	/**
	 * @aclDesc 修改
	 * @return [type] [description]
	 */
	public function updateAction(){
		$this->modify();
	}

	protected function form(){
		if($this->flashSession->has('error')){
			$cached_data = $this->getCachedFormData();
		}
		$id = $this->request->getQuery('id','int');

		$M = ISort::findFirst($id);
		if(!$M){
			$M = new ISort;
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

	protected function modify(){
		
		if($this->request->isPost()){
			$this->cacheFormData();
			$id = $this->request->getPost('sort_id','int');
			$data['sort_name'] = $this->request->getPost('sort_name');
			$data['parent_id'] = $this->request->getPost('parent_id');
			$data['seq'] = $this->request->getPost('seq','int');
			$data['recommend_flag'] = $this->request->getPost('recommend_flag','int');

			if($id){
				$Model = ISort::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new ISort;
			}

			$upload_dir = 'shop'.$this->auth->getShopId().'/image';
			$path = FileSys::upload($upload_dir, ['sort_cover']);
			if ($path) {
				$data['sort_cover'] = $path;
			}

			if($data['recommend_flag']){
				$path = FileSys::upload($upload_dir, ['recommend_pic']);
				if ($path) {
					$data['recommend_pic'] = $path;
				}
			}
			
		
			$Model->assign($data);

			if($Model->save()===false){

				throw new \Exception($Model->getErrorMsg(), 1);
			}
			else{
				
				SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->sort_id,$Model->sort_name);
				$this->flashSession->success("数据提交成功");
				$this->clearCachedFormData();
				$this->jump($this->url->get($this->base_url."/index"));

				/*$data = [
					'status'=>'1',
					'code'=>'',
				];*/
				
			}

			// $this->sendJSON($data);

		}
		else{
			$this->form();
		}
	}

	/**
	 * @aclDesc 删除
	 * @return [type] [description]
	 */
	public function deleteAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = ISort::findFirst($id);
			// var_export($M);exit;
			if(!$M){
				throw new \Exception("分类不存在", 1);
				
			}
			if($M->delete()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->sort_id,$M->sort_name);
				$this->flashSession->success("成功删除了一个分类");
				$data = [
					'status'=>'1',
					'code'=>'',
				];
			}
			else{
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}
	}

}