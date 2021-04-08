<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IUserKeyword;

class UserKeywordController extends ControllerAuth
{
	public function listAction()
	{
		// var_dump($this->User->user_id);exit;
		$list = IUserKeyword::find([
			'user_id=:user_id:',
			'bind' => [
				'user_id' => $this->User->user_id
			],
			'order' => 'update_time DESC,user_keyword_id DESC',
			'limit' => 10,
		]);

		$data = [];
		if ($list) {
			foreach ($list as $Item) {
				$data[] = [
					'content' => $Item->content,
					'total'=>$Item->total,
					'update_time' => $Item->update_time,
				];
			}
		}

		$this->sendJSON([
			'data' => $data
		]);
	}

	public function clearAction()
	{

		$res = $this->db->delete(
			'i_user_keyword',
			'user_id=:user_id',
			[
				'user_id' => $this->User->user_id
			]
		);

		if($res){
			$this->sendJSON([]);
		}
		else{
			throw new \Exception('清空搜索词失败', 2002);
		}
	}

}
