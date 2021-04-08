<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\ICategory;
use Common\Libs\Func;

class CategoryController extends ControllerBase {


	public function listAction(){

		$shop_id = (int)$this->post['shop_id'];
		$shop_id = $shop_id ? $shop_id : 1;
		$parent_id = (int)$this->post['parent_id'];
		$type = $this->post['type'];
		$type = in_array($type, ['son','children']) ? $type : 'children';

		if(!$this->conf['enable_multi_shop']){
			$shop_id = 1;
		}

		$data = $this->getSons($shop_id,$parent_id,$type);

		$this->sendJSON([
			'data'=>$data
		]);
	}

	protected function getSons($shop_id,$parent_id,$type){

		$categories = ICategory::find([
			'shop_id=:shop_id: AND parent_id=:parent_id:',
			'bind'=>[
				'shop_id'=>$shop_id,
				'parent_id'=>$parent_id
			],
			'order'=>'seq ASC,category_id ASC'
		]);

		$data = [];
		if($categories){
			foreach($categories as $k => $Item){				

				$data[$k] = [
					'category_id'=>$Item->category_id,
					'category_name'=>$Item->category_name,
					'category_cover'=>Func::staticPath($Item->category_cover),
				];

				if($type=='children' && $Item->sons){
					$data[$k]['sons'] = $this->getSons($shop_id,$Item->category_id,$type);
				}

				$data[$k]['sons'] = $data[$k]['sons'] ? $data[$k]['sons'] : null;
			}
		}

		return $data;
	}

}
