<?php

namespace Admin\Controllers;
use Admin\Components\ControllerAuth;
use Common\Models\IArea;
use Common\Models\SAdminLog;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Mvc\View;
/**
 * @acl *
 * @aclCustom false
 */
class IAreaController extends ControllerAuth {

	public $controller_name = '地区';

	/**
	 * @aclDesc 查看
	 * @return [type] [description]
	 */
	public function indexAction() {

		$this->breadcrumbs[] =[
			'text'=>'全部地区',
			'url'=>$this->url->get('/i_area/index')
		];

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$parent_id = $this->request->getQuery('parent_id');

		$conditions = [];
		$params = [];

		if($parent_id){
			$conditions[] = 'parent_id=:parent_id:';
			$params['parent_id'] = $parent_id;

			$Parent = IArea::findFirst($parent_id);

			$parent_names = explode('/',$Parent->getFullName());
			$parent_ids = array_reverse($Parent->getParents());
			$parents = array_combine($parent_ids,$parent_names);

			foreach($parents as $k=>$v){
				$this->breadcrumbs[] =[
					'text'=>$v,
					'url'=>$this->url->get('/i_area/index',['parent_id'=>$k])
				];
			}

			

			// var_dump($this->breadcrumbs);exit;
		}
		else{
			$conditions[] = 'level=1';
		}
		
		// $conditions[] = 'status=1';

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IArea')
                ->where($conditionSql,$params)
                ->orderBy('status DESC,first_letter ASC,'.IArea::getPkCol().' ASC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));
		$this->view->setVars([
			'controller_name'=>'地区',
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'parent_id'=>$parent_id
			],
			'Parent'=>$Parent,
		]);

	}

	/**
	 * @aclDesc 新增
	 * @return [type] [description]
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
		$parent_id = $this->request->getQuery('parent_id','int');

		$M = IArea::findFirst($id);
		if(!$M){
			$M = new IArea;

			if($parent_id){
				$M->parent_id = $parent_id;
			}

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
		$this->view->disable();
		if($this->request->isPost()){
			$this->cacheFormData();
			$id = $this->request->getPost('area_id','int');
			$data['name'] = $this->request->getPost('name');
            $data['parent_id'] = $this->request->getPost('parent_id');
            $data['status'] = $this->request->getPost('status');

			if($id){
				$Model = IArea::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new IArea;
			}

			$Model->assign($data);

			if($Model->save()===false){

				throw new \Exception($Model->getErrorMsg(), 1);
			}
			else{
				SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->area_id,$Model->name);
				$this->clearCachedFormData();
				$this->flashSession->success("数据提交成功");
				$data = [
					'status'=>'1',
					'code'=>'',
				];
				$this->sendJSON($data);
			}

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
			$M = IArea::findFirst($id);
			
			if($M->delete()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->area_id,$M->name);
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

	public function genMergerAction(){

		$page = $this->request->getQuery('page');
		$page = $page?$page:1;
		$offset = 50;
		$skip = ($page-1)*$offset;

		$list = IArea::find([
			'parent_id>0',
			'offset'=>$skip,
			'limit'=>$offset
		]);

		foreach($list as $Area){
			// unset($parents);
			$parents = $Area->getParents();
	
			$parents = array_reverse($parents);
			unset($parents[count($parents)-1]);

			if(count($parents)){
				$merger = ','.implode(',', $parents).',';
				$Area->merger = $merger;
				$Area->save();
			}
			unset($Area);
		}

		$page++;
		$this->view->setVars([
			'url'=>$this->url->get($this->base_url."/genMerger", ['page' => $page])
		]);
	}
	
}
