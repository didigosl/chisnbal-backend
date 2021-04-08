<?php
namespace Admin\Controllers;

use Admin\Components\FileSys;
use Common\Models\IArticle as Article;
use Common\Models\SAdminLog;
use Common\Components\Content;
use Admin\Components\ControllerAuth;
use Phalcon\Exception;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * @aclDesc 文章
 * @acl superadmin,shopadmin
 * @aclCustom super,single_shop
 */
class IArticleController extends ControllerAuth {

	public function initialize() {
		parent::initialize();
		$this->controller_name = '文章';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * 列表
	 */
	public function indexAction() {
		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$search = trim($this->request->getQuery('search'));
		$search_word = trim($this->request->getQuery('search_word'));

		$article_id = $this->request->getQuery('id');
		$article_menu_id = $this->request->getQuery('article_menu_id');

		$conditions = [];
		$params = [];

		if ('title' == $search and !empty($search_word)) {
			$conditions[] = 'title like :title:';
			$params['title'] = '%' . $search_word . '%';
		}

		if ('id' == $search and !empty($search_word)) {
			$conditions[] = 'article_id=:article_id:';
			$params['article_id'] = (int)$search_word;
		}

		if ($article_menu_id) {
			$conditions[] = 'article_menu_id=:article_menu_id:';
			$params['article_menu_id'] = (int)$article_menu_id;
		}

		if ($article_id) {
			$conditions[] = 'article_id=:article_id:';
			$params['article_id'] = $article_id;

			$Article = Article::findFirst($article_id);

			unset($Article);
		}

		
		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IArticle')
                ->where($conditionSql,$params)
                ->orderBy(Article::getPkCol().' ASC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 10,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'action_name' => '列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'search' => htmlspecialchars($search),
				'search_word' => htmlspecialchars($search_word),
				'article_menu_id'=>$article_menu_id,
			],
			'Menu' => $Menu,
			'SubMenu' => $SubMenu,
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

	public function form(){
		if($this->flashSession->has('error')){
			$cached_data = $this->getCachedFormData();
		}
		$id = $this->request->getQuery('id','int');
		$article_menu_id = $this->request->getQuery('article_menu_id','int');

		$M = Article::findFirst($id);
		
		if(!$M){
			$M = new Article;
			$M->article_menu_id = $article_menu_id;
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
			$M = Article::findFirst($id);		
			if(!$M){
				$M = new Article;
			}
			
			$data['article_menu_id'] = $this->request->getPost('article_menu_id');
			$data['title'] = $this->request->getPost('title');
			$data['author'] = $this->request->getPost('author');
			$data['intro'] = $this->request->getPost('intro');
			$data['content'] = $this->request->getPost('content');
			$data['content'] = str_ireplace('white-space: nowrap;', '', $data['content']);		
            $data['publish_datetime'] = $this->request->getPost('publish_datetime', 'string');
            
            $upload_dir = 'shop'.$this->auth->getShopId().'/image';
			$path = FileSys::upload($upload_dir, ['cover_path']);
			if ($path) {
				$data['cover_path'] = $path;
			}

			$this->db->begin();
			$M->assign($data);

			if ($M->save()) {
				$this->db->commit();

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->article_id,$M->title);
				$this->flashSession->success("数据提交成功");
				$this->clearCachedFormData();
				$this->jump($this->url->get($base_url."/index", [], false));

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

		$M = Article::findFirst($id);
		
		if(!$M){
			$M = new Article;


		}
		else{
			$M->videos = json_decode($M->videos);
			$M->audios = json_decode($M->audios);
			if (!$M) {
				throw new \Exception("数据不存在", 1);

			}
		}
		
		$this->view->setVars([
			'M'=>$M
			]);		
		
		if($this->request->isAjax()){	

			$this->view->disable();
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),$this->dispatcher->getActionName());
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);
		}
		else{

			$this->breadcrumbs[] = [
				'text'=>$this->controller_name,
				'url'=>$this->url->get($this->dispatcher->getControllerName().'/index',['menu_id'=>$M->menu_id,'sub_menu_id'=>$M->sub_menu_id])
			];
		}
	}

	
	/**
	 * @aclDesc 删除
	 */
	public function deleteAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			
			$id = $this->request->getQuery('id','int');

			$Model = Article::findFirst($id);

			if(!$Model){
				throw new \Exception("您操作的数据不存在", 1);
				
			}

			try{
				if($Model->delete()){
					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->article_id,$Model->title);
					$data = [
						'status'=>'1',
						'code'=>'',
					];
				}
				else{
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e){
				throw new \Exception($e->getMessage(), 1);
			}
			
			$this->sendJSON($data);

		}
		else{
			throw new \Exception("非法请求", 1);
		}
	}

	
	public function statusAction(){
		$this->view->disable();
		if ($this->request->isPost()) {

			$id = $this->request->getPost('id', 'int');
			$status = $this->request->getPost('status','int');

			
			if ($id) {
				$Model = Article::findFirst($id);
				if (!$Model) {
					throw new \Exception("您操作的对象不存在", 1);
				}
			} else {
				throw new \Exception("没有指定操作对象", 1);
				
			}

			$Model->status = $status;

			try {
				if ($Model->save()) {
					
					$data = [
						'status' => '1',
						'code' => '',
					];
					$this->sendJSON($data);
					
				} else {
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e) {
				throw new \Exception($e->getMessage(), 1);
			}
		}
	}


}