<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IFeedback;
use Common\Libs\Func;
use Common\Components\ValidateMsg;

class FeedbackController extends ControllerBase {

	public function addAction(){

		$data = [
			'title'=>$this->post['title'],
            'content'=>$this->post['content'],
            'nickname'=>$this->post['nickname'],
            'phone'=>$this->post['phone'],
            'email'=>$this->post['email'],
		];	

        $Feedback = new IFeedback;

        $this->auth();
        if($this->User){
            $data['user_id'] = $this->User->user_id;
        }
		
		
		$Feedback->assign($data);
		if($Feedback->save()){
			$this->sendJSON([]);;
		}
		else{

			throw new \Exception($Feedback->getErrorMsg(), 2001);
			
		}

	}
}
