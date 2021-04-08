<?php
namespace Admin\Components;

use Common\Models\SAdmin;
use Common\Models\SAdminLoginLog;
use Phalcon\Mvc\User\Component;

/**
 * Vokuro\Auth\Auth
 * Manages Authentication/Identity Management in Vokuro
 */
class Auth extends Component {

	/**
	 * Checks the user data
	 *
	 * @param array $data
	 * @return boolan
	 */
	public function check($data) {
		// Check if the user exist
		if($data['shop_flag']){
			$user = SAdmin::findFirst([
				'shop_id>0 AND username like :username:',
				'bind'=>['username'=>$data['username']]
			]);
		}
		else{
			$user = SAdmin::findFirst([
				'shop_id=0 AND username like :username:',
				'bind'=>['username'=>$data['username']]
			]);
        }
        
        
		// $user = SAdmin::findFirstByUsername($data['username']);
		if ($user == false) {
			$this->log(['username'=>$data['username'],'result'=>'fail','reason'=>1]);
			throw new \Exception('帐号和密码不正确');
		}

		// Check the password
		// if (!$this->security->checkHash($data['password'], $user->password)) {
        if (!SAdmin::checkHash($data['password'], $user->password)) {
			$this->log(['username'=>$data['username'],'admin_id'=>$user->id,'result'=>'fail','reason'=>2]);
			throw new \Exception('帐号和密码错误');
		}

		if ($user->flag == 2) {
			$this->log(['username'=>$data['username'],'admin_id'=>$user->id,'result'=>'fail','reason'=>3]);
			throw new \Exception('帐号停用');
		}

		$this->log(['username'=>$data['username'],'admin_id'=>$user->id,'result'=>'success']);
        /*
		// Check if the remember me was selected
		if (isset($data['remember'])) {
			$this->createRememberEnviroment($user);
		}
        */


		$this->session->set('auth', array(
			'id' => $user->id,
			'name' => $user->username,
			'acl_role_id'=>$user->acl_role_id,
			'role_name'=>$user->aclRole->intro,
			'shop_id'=>(int)$user->shop_id,
			//'profile' => $user->profile->name,
		));
	}

	public function log($data) {
        $Log = new SAdminLoginLog;
        if(!$Log->save($data,['username','admin_id','result','reason'])){
            $messages = $Log->getMessages();
            throw new \Exception($messages[0]);
        }

	}


	/**
	 * Implements login throttling
	 * Reduces the efectiveness of brute force attacks
	 *
	 * @param int $userId
	 */
	public function registerUserThrottling($userId) {
		$failedLogin = new FailedLogins();
		$failedLogin->usersId = $userId;
		$failedLogin->ipAddress = $this->request->getClientAddress();
		$failedLogin->attempted = time();
		$failedLogin->save();

		$attempts = FailedLogins::count(array(
			'ipAddress = ?0 AND attempted >= ?1',
			'bind' => array(
				$this->request->getClientAddress(),
				time() - 3600 * 6,
			),
		));

		switch ($attempts) {
			case 1:
			case 2:
				// no delay
				break;
			case 3:
			case 4:
				sleep(2);
				break;
			default:
				sleep(4);
				break;
		}
	}

	/**
	 * Creates the remember me environment settings the related cookies and generating tokens
	 *
	 * @param Vokuro\Models\Users $user
	 */
	public function createRememberEnviroment(Users $user) {
		$userAgent = $this->request->getUserAgent();
		$token = md5($user->email . $user->password . $userAgent);

		$remember = new RememberTokens();
		$remember->usersId = $user->id;
		$remember->token = $token;
		$remember->userAgent = $userAgent;

		if ($remember->save() != false) {
			$expire = time() + 86400 * 8;
			$this->cookies->set('RMU', $user->id, $expire);
			$this->cookies->set('RMT', $token, $expire);
		}
	}

	/**
	 * Check if the session has a remember me cookie
	 *
	 * @return boolean
	 */
	public function hasRememberMe() {
		return $this->cookies->has('RMU');
	}

	/**
	 * Logs on using the information in the coookies
	 *
	 * @return Phalcon\Http\Response
	 */
	public function loginWithRememberMe() {
		$userId = $this->cookies->get('RMU')->getValue();
		$cookieToken = $this->cookies->get('RMT')->getValue();

		$user = Users::findFirstById($userId);
		if ($user) {

			$userAgent = $this->request->getUserAgent();
			$token = md5($user->email . $user->password . $userAgent);

			if ($cookieToken == $token) {

				$remember = RememberTokens::findFirst(array(
					'usersId = ?0 AND token = ?1',
					'bind' => array(
						$user->id,
						$token,
					),
				));
				if ($remember) {

					// Check if the cookie has not expired
					if ((time() - (86400 * 8)) < $remember->createdAt) {

						// Check if the user was flagged
						$this->checkUserFlags($user);

						// Register identity
						$this->session->set('auth-identity', array(
							'id' => $user->id,
							'name' => $user->name,
							'shop_id' => $user->shop_id,
						));

						// Register the successful login
						$this->saveSuccessLogin($user);

						return $this->response->redirect('users');
					}
				}
			}
		}

		$this->cookies->get('RMU')->delete();
		$this->cookies->get('RMT')->delete();

		return $this->response->redirect('session/login');
	}


	/**
	 * Returns the current identity
	 *
	 * @return array
	 */
	public function getIdentity() {
		return $this->session->get('auth');
	}

	/**
	 * Returns the current identity
	 *
	 * @return string
	 */
	public function getName() {
		$identity = $this->session->get('auth');
		return $identity['name'];
	}

	public function getRole() {
		$identity = $this->session->get('auth');
		return $identity['acl_role_id'];
	}

	public function getRoleName() {
		$identity = $this->session->get('auth');
		return $identity['role_name'];
	}

	public function getShopId() {
		$identity = $this->session->get('auth');
		return (int)$identity['shop_id'];
	}

	public function hasWeixin() {
		$identity = $this->session->get('auth');
		return $identity['has_weixin'];
	}

	/**
	 * Removes the user identity information from session
	 */
	public function remove() {
		if ($this->cookies->has('RMU')) {
			$this->cookies->get('RMU')->delete();
		}
		if ($this->cookies->has('RMT')) {
			$this->cookies->get('RMT')->delete();
		}

		$this->session->remove('auth');
	}

	/**
	 * Auths the user by his/her id
	 *
	 * @param int $id
	 */
	public function authUserById($id) {
		$user = SAdmin::findFirstById($id);
		if ($user == false) {
			throw new \Exception('The user does not exist');
		}

		$this->checkUserFlags($user);

		$this->session->set('auth', array(
			'id' => $user->id,
			'name' => $user->name,
			'shop_id' => $user->shop_id,
		));
	}

	/**
	 * Get the entity related to user in the active identity
	 *
	 * @return \Vokuro\Models\Users
	 */
	public function getUser() {
		$identity = $this->session->get('auth');
		if (isset($identity['id'])) {

			$user = SAdmin::findFirstById($identity['id']);
			if ($user == false) {
				throw new \Exception('The user does not exist');
			}

			return $user;
		}

		return false;
	}
}
