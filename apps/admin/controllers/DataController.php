<?php

namespace Admin\Controllers;

use Admin\Components\ControllerBase;
use Common\Models\ICategory;
use Common\Models\ISort;
use Common\Models\IArea;
use Common\Models\ISpec;
use Common\Models\IRebateCategory;

/**
 * @acl *
 * @aclCustom false
 */
class DataController extends ControllerBase {


	public function getAreaAction() {
		//$level_type = $this->request->getQuery();
		$province_id = $this->request->getQuery('province_id','int');
		$city_id = $this->request->getQuery('city_id','int');

		$conditions = [];
		$params = [];

		$level_type = 1;

		if($province_id){
			$conditions[] = ' parent_id=:parent_id ';
			$params['parent_id'] = $province_id;
			$level_type = 2;
		}

		if($city_id){
			$conditions[] = ' parent_id=:parent_id ';
			$params['parent_id'] = $city_id;
			$level_type = 3;
		}

		$conditions[] = ' level_type=:level_type ';
		$params['level_type'] = $level_type;
	
		$condition = implode(' AND ', $conditions);
		//var_dump($condition);exit;

		$list = $this->db->fetchAll("select area_id,name from tbl_area where ".$condition,\Phalcon\Db::FETCH_ASSOC,$params);
		$areas = [];
		if(is_array($list)){
			foreach ($list as $v) {
				$areas[$v['area_id']] = $v['name'];
			}
		}

		$this->sendJSON($areas);
		
	}

	public function getSortsAction(){
		$ret = [];
		$parent_id = $this->request->getQuery('parent_id','int');

		$tree = ISort::getTree($parent_id);
		$tree = json_encode($tree,JSON_UNESCAPED_UNICODE);
		$ret = "var sorts = $tree;";
		echo $ret;
		exit;
	}

	public function getCategoriesAction(){
		$ret = [];
		$parent_id = $this->request->getQuery('parent_id','int');
		$shop_id = $this->request->getQuery('shop_id','int');

		$tree = ICategory::getTree($parent_id,$shop_id);
		$tree = json_encode($tree,JSON_UNESCAPED_UNICODE);
		$ret = "var categories = $tree;";
		echo $ret;
		exit;
		/*$this->sendJSON([
			'status'=>'SUCCESS',
			'code'=>0,
			'msg'=>'',
			'data'=>$ret,
		]);*/
	}

	public function getCategoriesForZtreeAction(){
		$category_id = $this->request->getQuery('category_id','int');
		$shop_id = $this->request->getQuery('shop_id','int');
		$related_model = $this->request->getQuery('related_model','trim');

		if($category_id){
			$category_data = $this->db->fetchOne('SELECT * FROM i_category WHERE category_id=:category_id',\Phalcon\Db::FETCH_ASSOC,['category_id'=>$category_id]);
			$merger = $category_data['merger'] ? explode(',',trim($category_data['merger'],',')) : [];
		}
		else{
			$merger = [];
		}
		
		$nodes = [];
		$list = $this->db->fetchAll('SELECT * FROM i_category WHERE shop_id=:shop_id ORDER BY rank asc',\Phalcon\Db::FETCH_ASSOC,['shop_id'=>$shop_id]);
		foreach($list as $item){
			if($related_model=='spu'){
				$name = $item['category_name'].'('.$item['spu_total'].')';
				$url = $this->url->get('admin/i_goods_spu/index',['category_id'=>$item['category_id']]);
			}
			elseif($related_model=='spec'){
				$name = $item['category_name'].'('.$item['spec_total'].')';
				$url = $this->url->get('admin/i_spec/index',['category_id'=>$item['category_id']]);
			}
			else{
				$name = $item['category_name'];
			}

			$nodes[] = [
				'id'=>$item['category_id'],
				'pId'=>$item['parent_id'],
				'checked'=>true,
				'name'=>$name,
				'url'=>$url,
				// 'open'=> in_array($item['category_id'],$merger) ? true : false,
				'open'=>true,
				'target'=>"_self"
			];
		}

		$ret = 'var zNodes = '.json_encode($nodes,JSON_UNESCAPED_UNICODE);
		echo $ret;
		exit;
	}

	public function getAreasAction(){
		$ret = [];
		$parent_id = (int)$this->request->getQuery('parent_id','int');
		// var_dump($parent_id);
		// exit;
		$tree = IArea::getTree($parent_id);
		// var_dump($tree);exit;
		$tree = json_encode($tree,JSON_UNESCAPED_UNICODE);
		$ret = "var areas = $tree;";
		echo $ret;
		exit;
		/*$this->sendJSON([
			'status'=>'SUCCESS',
			'code'=>0,
			'msg'=>'',
			'data'=>$ret,
		]);*/
	}

	/**
	 * 获取分类的相关信息如返利、规格
	 * @return [type] [description]
	 */
	public function getCategoryInfosAction(){

		$category_id = $this->request->getQuery('category_id');

		$spec_collects = ISpec::find([
			'category_id=:category_id:',
			'bind'=>['category_id'=>$category_id],
		]);

		$specs = [];
		foreach($spec_collects as $Spec){
			$specs[] = [
				'spec_id'=>$Spec->spec_id,
				'spec_name'=>$Spec->spec_name,
				'specs'=>$Spec->getArrSpecs(),
			];
		}

		$this->sendJSON([
			'status'=>1,
			'data'=>[
				'specs'=>$specs
			]
		]);
	}
	
}
