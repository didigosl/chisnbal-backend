<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IOrderComment;
use Common\Models\IOrder;

class OrderCommentController extends ControllerBase {

	/**
	 * 评论列表
	 * @param integer $spu_id
	 * @param integer $page
	 * @param integer $page_limit
	 * @return [type] [description]
	 */
	public function listAction(){

		if(!$this->post['spu_id']){
			throw new \Exception("必须提供商品ID", 2001);
			
		}

		$data = IOrderComment::getComments($this->post['spu_id'],$this->post['page'],$this->post['page_limit']);

		$this->sendJSON([
			'data'=>$data
		]);
	}

	

}
