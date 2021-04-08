<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IUserLevel;

class UserLevelController extends ControllerBase
{
	public function listAction()
	{
		$list = IUserLevel::find([
            'user_level>1'
        ]);
		$this->sendJSON([
			'data' => $list
		]);
	}

}
