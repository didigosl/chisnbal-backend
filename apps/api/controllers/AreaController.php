<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IArea;

class AreaController extends ControllerBase {


	public function listAction(){

		$parent_id = (int)$this->post['parent_id'];

		if($this->conf['default_area_id']){
			$parent_id = (int)$this->conf['default_area_id'];
		}
		
		$type = $this->post['type'];
		$type = in_array($type, ['son','children']) ? $type : 'children';

		$data = $this->getSons($parent_id,$type);

		$this->sendJSON([
			'data'=>$data
		]);
	}

	protected function getSons($parent_id,$type){

		$list = IArea::find([
			'parent_id=:parent_id: AND status=1',
			'bind'=>['parent_id'=>$parent_id],
			'order'=>'first_letter ASC,seq ASC,area_id ASC'
		]);

		$data = [];
		if($list){
			foreach($list as $k => $Item){				

				$data[$k] = [
					'area_id'=>$Item->area_id,
					'name'=>$Item->name,
				];

				if($type=='children' && $Item->sons){
					$data[$k]['sons'] = $this->getSons($Item->area_id,$type);
				}

				$data[$k]['sons'] = $data[$k]['sons'] ? $data[$k]['sons'] : null;
			}
		}

		return $data;
	}

}
