<?php
namespace Admin\Controllers;

use Common\Models\IUserLevel;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 会员等级
 * @acl shopadmin,superadmin
 * @aclCustom super,single_shop
 */
class IUserLevelController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '会员等级';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 * @return [type] [description]
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		if($id){
			$conditions[] = 'level_id=:id:';
			$params['level_id'] = $id;
		}

		$conditions[] = 'level_id>1';
		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IUserLevel')
                ->where($conditionSql,$params)
                ->orderBy('seq ASC, level_id ASC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'search'=>htmlspecialchars($search),
				'search_word'=>htmlspecialchars($search_word),
			],
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
		/*if($this->flashSession->has('error')){
			$cached_data = $this->getCachedFormData();
		}*/
		$id = $this->request->getQuery('id','int');

		$M = IUserLevel::findFirst($id);
		if(!$M){
			$M = new IUserLevel;

			/*if($cached_data){
				$M->assign($cached_data);
			}*/
		}
		
		$this->view->setVars([
			'M'=>$M
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
	}

	protected function modify(){
		$this->view->disable();
		if($this->request->isPost()){
			// $this->cacheFormData();
			
			$id = $this->request->getPost('level_id','int');
			$level_name = $this->request->getPost('level_name');
			$data['price'] = $this->request->getPost('price');
            $data['price'] = fmtPrice($data['price']);

			$data['discount_type'] = $this->request->getPost('discount_type');
            $data['discount'] = $this->request->getPost('discount');
            
			if($level_name){
				$data['level_name'] = $level_name;
			}

			if($id){
				$Model = IUserLevel::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new IUserLevel;
			}

		
			$Model->assign($data);

			if($Model->save()===false){

				throw new \Exception($Model->getErrorMsg(), 1);
			}
			else{

				SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->level_id,$Model->level_name);
				// $this->clearCachedFormData();
				$data = [
					'status'=>'1',
					'code'=>'',
				];
				
			}

			$this->sendJSON($data);

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
			$M = IUserLevel::findFirst($id);
			
			if($M->delete()){
				
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->level_id,$M->level_name);
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