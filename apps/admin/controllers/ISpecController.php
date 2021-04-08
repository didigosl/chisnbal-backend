<?php
namespace Admin\Controllers;

use Common\Models\ISpec;
use Common\Models\ISpuSpec;
use Common\Models\ICategory;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 商品规格
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class ISpecController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '商品规格';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$id = $this->request->getQuery('id');
		$category_id = $this->request->getQuery('category_id','int');

		$conditions = [];
		$params = [];

		if($id){
			$conditions[] = 'spec_id=:id:';
			$params['spec_id'] = $id;
		}

		$conditions[] = 'category_id=:category_id:';
		$params['category_id'] = $category_id;


		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\ISpec')
                ->where($conditionSql,$params)
                ->orderBy(ISpec::getPkCol().' DESC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 1000,
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
				'category_id'=>$category_id
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
		$category_id = $this->request->getQuery('category_id','int');

		$M = ISpec::findFirst($id);
		if(!$M){
			$M = new ISpec;
			$M->category_id = $category_id;
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
			
			$id = $this->request->getPost('spec_id','int');
			$data['spec_name'] = $this->request->getPost('spec_name');
			$data['specs'] = $this->request->getPost('specs');
			$data['specs'] = ISpec::fmtSpecs($data['specs']);
			$data['category_id'] = $this->request->getPost('category_id','int');


			if($id){
				$Model = ISpec::findFirst($id);
				if(!$Model){
					$data = [
						'status'=>'0',
						'code'=>'',
						'msg'=>'不存在您要操作的数据'
					];
				}
			}
			else{

				$Model = new ISpec;
			}

			try{
				$Model->assign($data);

				if($Model->save()){

					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->spec_id,$Model->spec_name);
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
			$M = ISpec::findFirst($id);
			
			if($M->delete()){

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->spec_id,$M->spec_name);
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

	public function updateCategoryStatAction(){

		$list = ICategory::find();

		foreach ($list as $Category) {
			$total = $this->db->fetchColumn('SELECT count(1) FROM i_spec WHERE category_id=:category_id',['category_id'=>$Category->category_id]);

			$Category->spec_total = $total;
			$Category->save();
		}
	}

	//更新规格关联的商品数量统计
	public function updateTotalAction(){

		$this->db->execute('DELETE FROM i_spu_spec');
		$spec_totals = [];
		$spus = $this->db->fetchAll('SELECT spu_id,spec_data FROM i_goods_spu',\Phalcon\Db::FETCH_ASSOC);
		// var_dump($spus);exit;
		if(is_array($spus)){
			foreach($spus as $spu){
				// var_dump($spu);
				echo 'spu_id:'.$spu['spu_id'].PHP_EOL;
				if($spu['spec_data']){
					$spec_data = json_decode($spu['spec_data']);
					if($spec_data){

						foreach($spec_data as $spec_id => $v){
							echo 'spec_id:'.$spec_id.PHP_EOL;
							$check_spec = $this->db->fetchColumn('SELECT count(1) FROM i_spec WHERE spec_id=:spec_id',['spec_id'=>$spec_id]);
							if($check_spec){
								$SpuSpec = new ISpuSpec;
								$SpuSpec->save([
									'spu_id'=>$spu['spu_id'],
									'spec_id'=>$spec_id
								]);
								$spec_totals[$spec_id] = (int)$spec_totals[$spec_id] + 1;
							}
							else{
								$this->db->execute('UPDATE i_goods_spu set spec_data="" WHERE spu_id=:spu_id',['spu_id'=>$spu['spu_id']]);
							}
						}
						// exit;
						// var_dump($spec_data);exit;
					}
					else{
						echo 'spu_id:'.$spu['spu_id'].' json decode error'.PHP_EOL;
					}
				}
				
				
			}
		}

		foreach($spec_totals as $spec_id=>$total){
			$Spec = ISpec::findFirst($spec_id);
			if($Spec){
				$Spec->total = $total;
				$Spec->save();
			}
		}
		var_dump($spec_totals);
		exit;
	}

}