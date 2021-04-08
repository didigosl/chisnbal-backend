<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\ISort;
use Common\Libs\Func;

class SortController extends ControllerBase {


	public function listAction(){

		$parent_id = (int)$this->post['parent_id'];
		$type = $this->post['type'];
		$type = in_array($type, ['son','children']) ? $type : 'children';

		$data = $this->getSons($parent_id,$type);

		$this->sendJSON([
			'data'=>$data
		]);
	}

	protected function getSons($parent_id,$type){

		$categories = ISort::find([
			'parent_id=:parent_id:',
			'bind'=>[
				'parent_id'=>$parent_id
			],
			'order'=>'seq ASC,sort_id ASC'
		]);

		$data = [];
		if($categories){
			foreach($categories as $k => $Item){				

				$data[$k] = [
					'sort_id'=>$Item->sort_id,
					'sort_name'=>$Item->sort_name,
					'sort_cover'=>Func::staticPath($Item->sort_cover),
				];

				if($type=='children' && $Item->sons){
					$data[$k]['sons'] = $this->getSons($Item->sort_id,$type);
				}

				$data[$k]['sons'] = $data[$k]['sons'] ? $data[$k]['sons'] : null;
			}
		}

		return $data;
	}

}
