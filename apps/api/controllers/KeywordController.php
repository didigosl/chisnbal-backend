<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IKeyword;

class KeywordController extends ControllerBase {


	public function listAction(){


		$list = IKeyword::find([
			'order'=>'total DESC,keyword_id ASC',
			'limit'=>10,
		]);

		$data = [];
		if($list){
			foreach($list as $Item){
				$data[] = [
					'content'=>$Item->content,
					'total'=>$Item->total,
				];
			}
		}

		$this->sendJSON([
			'data'=>$data
		]);
	}

	

}
