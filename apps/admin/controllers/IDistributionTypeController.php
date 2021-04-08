<?php
namespace Admin\Controllers;

use Common\Models\IDistributionType;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 配货类型
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class IDistributionTypeController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '配货类型';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 * @return [type] [description]
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$search = trim($this->request->getQuery('search'));
		$search_word = trim($this->request->getQuery('search_word'));

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditions[] = 'shop_id=:shop_id: AND delete_flag=0';
		$params['shop_id'] = $this->auth->getShopId();

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IDistributionType')
                ->where($conditionSql,$params)
                ->orderBy(IDistributionType::getPkCol().' DESC');

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

		$id = $this->request->getQuery('id','int');

		$M = IDistributionType::findFirst($id);
		if(!$M){
			$M = new IDistributionType;
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
			
			$id = $this->request->getPost('distribution_type_id','int');
			$data['name'] = $this->request->getPost('name');

			if($id){
				$Model = IDistributionType::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new IDistributionType;
				$Model->shop_id = $this->auth->getShopId();
				$Model->shop_id = $Model->shop_id ? $Model->shop_id : 1;
			}
			$Model->assign($data);

            if($Model->save()===false){

                throw new \Exception($Model->getErrorMsg(), 1);
            }
            else{
                SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->distribution_type_id,$Model->name);
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
			$M = IDistributionType::findFirst($id);
			$M->delete_flag = 1;
			if($M->save()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->distribution_type_id,$M->name);
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