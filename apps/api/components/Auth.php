<?php
namespace Api\Components;

use Common\Models\IUser;
use Common\Models\IUserLoginLog;
use Phalcon\Mvc\User\Component;

class Auth extends Component {

	protected $User = null;

	/**
	 * Checks the user data
	 *
	 * @param array $data
	 * @return boolan
	 */
	public function check($data) {

		$this->User = IUser::findFirstByPhone($data['phone']);
		if ($this->User == false) {
			// $this->log(['phone'=>$data['phone'],'result'=>'fail','reason'=>1]);
			throw new \Exception('帐号或密码不正确',2002);
		}

		if (!$this->security->checkHash($data['password'], $this->User->password)) {
			// $this->log(['phone'=>$data['phone'],'user_id'=>$this->User->id,'result'=>'fail','reason'=>2]);
			throw new \Exception('帐号或密码不正确',2002);
		}

		if ($this->User->flag == 2) {
			// $this->log(['phone'=>$data['phone'],'user_id'=>$this->User->id,'result'=>'fail','reason'=>3]);
			throw new \Exception('帐号停用',1003);
		}

		$this->User->genToken();
		return $this;
		//$this->log(['phone'=>$data['phone'],'user_id'=>$this->User->id,'result'=>'success']);

	}

	public function log($data) {
        $Log = new IUserLoginLog;
        if(!$Log->save($data,['phone','user_id','result','reason'])){
            $messages = $Log->getMessages();
            throw new \Exception($messages[0]);
        }

	}


	public function authUserById($id) {
		
		$this->User = IUser::findFirst($id);
		if ($this->User == false) {
			throw new \Exception('The user does not exist',2002);
		}

		// $this->checkUserFlags($this->User);
		$this->User->genToken();
		return $this;
	}

	public function authUserByToken($token) {

		$this->User = IUser::findFirstByToken($token);
		if ($this->User == false) {
			throw new \Exception('The user does not exist',2002);
		}

		// $this->checkUserFlags($this->User);
		$this->User->genToken();
		return $this;
	}


	public function getUser() {		

		return $this->User;
	}
}
