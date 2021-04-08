<?php
namespace Admin\Controllers;

use Common\Models\IFeedback;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 用户反馈
 * @acl shopadmin,superadmin
 * @aclCustom super,single_shop
 */
class IFeedbackController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '用户反馈';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 列表
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IFeedback')
                ->where($conditionSql,$params)
                ->orderBy(IFeedback::getPkCol().' DESC');

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
			'controller_name'=>$this->controller_name,
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
			],
		]);

	}

	/**
	 * @aclDesc 查看
	 */
	public function viewAction(){
		$id = $this->request->getQuery('id','int');

		$M = IFeedback::findFirst($id);
		if(!$M){
			$M = new IFeedback;
		}
		
		$this->view->setVars([
			'M'=>$M
			]);		
		
		if($this->request->isAjax()){	

			$this->view->disable();
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),'view');
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);
		}
	}
}