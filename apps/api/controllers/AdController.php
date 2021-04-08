<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IAd;
use Common\Models\ICategory;
use Common\Libs\Func;

class AdController extends ControllerBase {




	public function getAction(){

		$category_id = (int)$this->post['category_id'];
		$shop_id = (int)$this->post['shop_id'];
		$sort_id = (int)$this->post['sort_id'];

		if(!$this->conf['enable_multi_shop']){
			$shop_id = 1;
		}

		$type = $this->post['type'];
		$type = in_array($type, ['index','category']) ? $type : 'category';

		$conditions = [];
		$params = [];

		$conditions[] = 'shop_id=:shop_id:'; 
		$params['shop_id'] = $shop_id;
		$conditions[] = 'status=3';
		$conditions[] = 'position_type=:type:';
		$params['type'] = $type;

		if($category_id){
			$conditions[] = '(category_id=0 or category_id=:category_id:)';
			$params['category_id'] = $category_id;
		}
		elseif($sort_id){
			$conditions[] = '(sort_id=0 or sort_id=:sort_id:)';
			$params['sort_id'] = $sort_id;
		}
		elseif('category'==$type){
			if($shop_id){
				$conditions[] = 'category_id=0 ';
			}
			else{
				$conditions[] = 'sort_id=0 ';
			}
		}

		$conditions_sql = implode(' AND ',$conditions);

		$ads = IAd::find([
			$conditions_sql,
			'bind'=>$params,
			'order'=>' ad_id DESC '
		]);

		$list = [];
		if($ads){
			foreach ($ads as $Ad) {
				$list[] = [
					'ad_id'=>$Ad->ad_id,
					'ad_name'=>$Ad->ad_name,
					'img'=>Func::staticPath($Ad->img),
					'link_type'=>$Ad->link_type,
					'link_id'=>$Ad->link_id,
					'link_url'=>$Ad->link_url,
				];
			}
		}
		else{
			$list = null;
		}

		$this->sendJSON([
			'data'=>$list
		]);
	}

	public function getForPcAction(){

		$type = trim($this->post['type']);
		$sort_id = (int)$this->post['sort_id'];
		$category_id = (int)$this->post['category_id'];
		$shop_id = (int)$this->post['shop_id'];

		if(!$this->conf['enable_multi_shop']){
			$shop_id = 1;
		}

		if(!in_array($type, ['index','category'])){
			throw new \Exception("请指定type参数", 1);
			
		}

		if(empty($shop_id)){
			if($type=='category' && empty($sort_id)){
				throw new \Exception("请指定sortId", 1);
				
			}
		}
		else{
			if($type=='category' && empty($category_id)){
				throw new \Exception("请指定categoryId", 1);
				
			}
		}

		

		//获取PC端广告位信息
		$tmp_poses = $this->db->fetchAll("SELECT * from i_ad_pos WHERE position_type in ('pc_index','pc_category')");
		$poses = [];
		foreach ($tmp_poses as $k => $v) {
			$poses[$v['position_type'].$v['ad_pos_id']] = $v;
		}
		unset($tmp_poses);

		$ret = [];

		


		$conditions = [];
		$params = [];

		if($shop_id && $shop_id!=1){
            $conditions[] = 'shop_id=:shop_id';
            $params['shop_id'] = $shop_id;
        }


		//$conditions[] = 'ad_pos_id>0';

		if($type == 'index'){
//			$conditions[] = "position_type='pc_index'";
			$conditions[] = "position_type='index'";
		}
		elseif($type == 'category'){

			$conditions[] = "position_type='pc_category'";
		}

		$conditions_sql = implode(' AND ',$conditions);

		$sql = "SELECT * FROM i_ad WHERE $conditions_sql ORDER BY ad_id DESC";
		$ads = $this->db->fetchAll($sql,\Phalcon\Db::FETCH_ASSOC,$params);

		foreach($ads as $v){
			if(isset($ret[$v['position_type'].$v['ad_pos_id']])){
				$ret[$v['position_type'].$v['ad_pos_id']]['ads'][] = [
					'ad_id'=>$v['ad_id'],
					'ad_name'=>$v['ad_name'],
					'img'=>Func::staticPath($v['img']),
                    'link_url'=>$v['link_url'],
                    'link_type'=>$v['link_type'],
                    'link_id'=>$v['link_id'],
				];
			}
			else{
				$ret[$v['position_type'].$v['ad_pos_id']] = [
					'pos_name'=>$poses[$v['position_type'].$v['ad_pos_id']]['name'],
					'ads'=>[
						0=>[
							'ad_id'=>$v['ad_id'],
							'ad_name'=>$v['ad_name'],
							'img'=>Func::staticPath($v['img']),
							'link_url'=>$v['link_url'],
                            'link_type'=>$v['link_type'],
					        'link_id'=>$v['link_id'],
						]
					]

				];
			}
			
		}

		$this->sendJSON([
			'data'=>$ret
		]);

	}


}
