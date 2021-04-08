<?php
namespace Admin\Controllers;

use JPush\Client as JPush;
use Common\Models\IPush;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 系统推送
 * @aclCustom super,single_shop
 */
class IPushController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '系统推送';
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

		if($id){
			$conditions[] = 'push_id=:id:';
			$params['push_id'] = $id;
		}

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IPush')
                ->where($conditionSql,$params)
                ->orderBy(IPush::getPkCol().' DESC');

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

		$id = $this->request->getQuery('id','int');

		$M = IPush::findFirst($id);
		if(!$M){
			$M = new IPush;
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
			
			$id = $this->request->getPost('push_id','int');
			$data['content'] = $this->request->getPost('content');

			if($id){
				$Model = IPush::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new IPush;
			}
			
				$Model->assign($data);

				if($Model->save()===false){

					throw new \Exception($Model->getErrorMsg(), 1);
				}
				else{
					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->push_id,$Model->content);
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
			$M = IPush::findFirst($id);
			
			if($M->delete()){
				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->push_id,$M->content);
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
    
    public function testAction(){
        
        $conf = conf();

        $client = new JPush($conf['jiguang_app_key'], $conf['jiguang_secret']);
        $pusher = $client->push();
        $pusher->setPlatform('all');
        $pusher->addAlias('d4f5bbde16d6e2144053338edeef79fc');
        $pusher->iosNotification('test cotnent',[
            'badge' => '+1',
        ]);
        $pusher->androidNotification('test cotnent',[]);
        $pusher->options([
            'apns_production'=>true,
        ]);
        $pusher->send();

        //推开发环境
        $client = new JPush($conf['jiguang_app_key'], $conf['jiguang_secret']);
        $pusher = $client->push();
        $pusher->setPlatform('all');
        $pusher->addAlias('d4f5bbde16d6e2144053338edeef79fc');
        $pusher->iosNotification('test cotnent',[
            'badge' => '+1',
        ]);
        $pusher->androidNotification('test cotnent',[]);
        $pusher->options([
            'apns_production'=>false,
        ]);
        $pusher->send();
       
    }

}