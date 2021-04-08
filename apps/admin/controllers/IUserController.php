<?php
namespace Admin\Controllers;

use Common\Models\IUser;
use Common\Models\SAdminLog;
use Common\Models\IAddress;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 会员
 * @acl shopadmin,superadmin
 * @aclCustom super,single_shop
 */
class IUserController extends ControllerAuth
{

	public function initialize()
	{
		parent::initialize();

		$this->controller_name = '会员';
		$this->view->setVar('controller_name', $this->controller_name);
	}

	/**
	 * @aclDesc 列表
	 */
	public function indexAction()
	{

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$name = trim($this->request->getQuery('name'));
		$phone = trim($this->request->getQuery('phone'));
		$level_id = $this->request->getQuery('level_id');
		$status = $this->request->getQuery('status');
		$parent_id = $this->request->getQuery('parent_id');
		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		if ($name) {
			$conditions[] = 'name like :name:';
			$params['name'] = '%' . $name . '%';
		}

		if ($phone) {
			$conditions[] = 'phone like :phone:';
			$params['phone'] = '%' . $phone . '%';
		}

		if ($level_id) {
			$conditions[] = 'level_id=:level_id:';
			$params['level_id'] = $level_id;
		}

		if (is_numeric($status)) {
			$conditions[] = 'status=:status:';
			$params['status'] = $status;
		}

		if ($parent_id) {
			$conditions[] = 'parent_id = :parent_id:';
			$params['parent_id'] =  $parent_id;

			$Parent = IUser::findFirst($parent_id);
			$this->controller_name = '【' . $Parent->name . '】的下级用户';
		}

		if ($id) {
			$conditions[] = 'user_id=:id:';
			$params['id'] = $id;
		}

		$conditions[] = "remove_flag=0";
		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$builder = $this->modelsManager->createBuilder()
			->columns('*')
			->from(['u' => 'Common\Models\IUser'])
			// ->leftJoin('Common\Models\IBeacon','u.user_id=b.user_id','b')
			->where($conditionSql, $params)
			->orderBy('u.user_id DESC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));


		$this->breadcrumbs[] = [
			'text' => $this->controller_name,
		];

		$this->view->setVars([
			'controller_name' => $this->controller_name,
			'action_name' => '列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'name' => htmlspecialchars($name),
				'phone' => htmlspecialchars($phone),
				'level_id' => $level_id,
				'status' => $status,
			],
		]);
	}


	/**
	 * @aclDesc 修改
	 * @return [type] [description]
	 */
	public function updateAction()
	{
		$this->modify();
	}

	protected function form()
	{

		$id = $this->request->getQuery('id', 'int');
		$level_id = $this->request->getQuery('level_id', 'int');

        $addresses = [];

		$M = IUser::findFirst($id);
		if (!$M) {
			$M = new IUser;
			$M->level_id = $level_id;
        }
        else{
            $addresses = db()->fetchAll("SELECT * FROM i_address WHERE user_id=:user_id",\Phalcon\Db::FETCH_ASSOC,[
                'user_id'=>$M->user_id
            ]);
        }

		$this->view->setVars([
            'M' => $M,
            'addresses' => $addresses
		]);

		if ($this->request->isAjax()) {

			$this->view->disable();
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(), 'form');
			$data = [
				'status' => '1',
				'code' => '',
				'data' => $html
			];
			$this->sendJSON($data);
		} else {
			$this->view->pick($this->controller . '/form');
		}
	}

	protected function modify()
	{

		if ($this->request->isPost()) {
			$this->view->disable();
			$id = $this->request->getPost('user_id', 'int');
			$data['name'] = $this->request->getPost('name');
			$data['gender'] = $this->request->getPost('gender');
			$data['age'] = $this->request->getPost('age');
			$data['photo'] = $this->request->getPost('photo');
			$data['level_id'] = $this->request->getPost('level_id');
			$data['area_id'] = $this->request->getPost('area_id');
			$data['address'] = $this->request->getPost('address');
			$data['phone'] = $this->request->getPost('phone');
			$data['kf_admin_id'] = $this->request->getPost('kf_admin_id');

			if ($id) {
				$Model = IUser::findFirst($id);
				if (!$Model) {
					$data = [
						'status' => '0',
						'code' => '',
						'msg' => '不存在您要操作的用户'
					];
				}
			} else {

				$Model = new IUser;
			}

			try {
				$Model->assign($data);

				if ($Model->save()) {

					SAdminLog::add($Model->getSource(), $this->dispatcher->getActionName(), $Model->user_id, $Model->phone . '(' . $Model->name . ')');
					$data = [
						'status' => '1',
						'code' => '',
					];
				} else {
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e) {
				throw new \Exception($e->getMessage(), 1);
			}

			$this->sendJSON($data);
		} else {
			$this->form();
		}
	}

	/*public function viewAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IUser::findFirst($id);
						
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$this->view->setVars([
				'M'=>$M
				]);
			
			$html = $this->view->getRender($this->dispatcher->getControllerName(),$this->dispatcher->getActionName());

			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);

		}

	}*/

	/**
	 * @aclDesc 删除
	 * @return [type] [description]
	 */
	public function deleteAction()
	{
		$this->view->disable();
		if ($this->request->isAjax()) {
			$id = $this->request->getQuery('id', 'int');
			$M = IUser::findFirst($id);
			if (!$M) {
				throw new \Exception("用户不存在", 1);
			}
			if ($M->remove()) {
				$data = [
					'status' => '1',
					'code' => '',
				];
			} else {
				$data = [
					'status' => '0',
					'code' => '',
					'msg' => $M->getErrorMsg()
				];
			}
			$this->sendJSON($data);
		}
	}

	/**
	 * @aclDesc 冻结/解冻
	 * @return [type] [description]
	 */
	public function freezeAction()
	{
		$this->view->disable();
		if ($this->request->isAjax()) {
			$id = $this->request->getQuery('id', 'int');
			$M = IUser::findFirst($id);
			if (!$M) {
				throw new \Exception("用户不存在", 1);
			}
			if ($M->status > 0) {
				$res = $M->freeze();
				$msg = '用户已被冻结';
			} else {
				$res = $M->unfreeze();
				$msg = '用户已解除冻结';
			}
			if ($res) {
				$data = [
					'status' => '1',
					'code' => '',
					'msg' => $msg
				];
			} else {
				$data = [
					'status' => '0',
					'code' => '',
				];
			}
			$this->sendJSON($data);
		}
	}

	/**
	 * @aclDesc 审核通过
	 * @return [type] [description]
	 */
	public function auditAction()
	{
		$this->view->disable();
		if ($this->request->isAjax()) {
			$id = $this->request->getQuery('id', 'int');
			$M = IUser::findFirst($id);
			if (!$M) {
				throw new \Exception("用户不存在", 1);
			}
			$res = $M->audit();
			if ($res) {
				$data = [
					'status' => '1',
					'code' => '',
					'msg' => $msg
				];
			} else {
				$data = [
					'status' => '0',
					'code' => '',
				];
			}
			$this->sendJSON($data);
		}
	}

	/**
	 * @aclDesc 重置密码
	 * @return [type] [description]
	 */
	public function resetpswAction()
	{
		$this->view->disable();
		if ($this->request->isAjax()) {
			$id = $this->request->getQuery('id', 'int');
			$M = IUser::findFirst($id);
			if (!$M) {
				throw new \Exception("用户不存在", 1);
			}
			$res = $M->resetpsw();
			if ($res) {
				$data = [
					'status' => '1',
					'code' => '',
					'msg' => $msg
				];
			} else {
				$data = [
					'status' => '0',
					'code' => '',
				];
			}
			$this->sendJSON($data);
		}
    }
    
    /**
	 * @aclDesc 充值
	 * @return [type] [description]
	 */
	public function rechargeAction()
	{
        $this->view->disable();
        $conf = conf();

        if(!$conf['enable_recharge']){
            throw new \Exception('功能未开通');
        }

		if ($this->request->isAjax()) {
            $id = $this->request->getQuery('id', 'int');
            $amount = $this->request->getQuery('amount');

            if(!is_numeric($amount)){
                throw new \Exception("充值金额必须是数字，最多两位小数", 1);
            }
			$M = IUser::findFirst($id);
			if (!$M) {
				throw new \Exception("用户不存在", 1);
            }

			$res = $M->recharge($amount);
			if ($res) {
				$data = [
					'status' => '1',
					'code' => '',
				];
			} else {
				$data = [
					'status' => '0',
					'code' => '',
				];
			}
			$this->sendJSON($data);
		}
	}

	/**
	 * @aclDesc 搜索
	 */
	public function searchAction()
	{
		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$name = trim($this->request->getQuery('name'));
		$email = trim($this->request->getQuery('email'));
		$phone = trim($this->request->getQuery('phone'));
		$level_id = $this->request->getQuery('level_id');
		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		if ($level_id) {
			$conditions[] = 'level_id=:level_id:';
			$params['level_id'] = $level_id;
		}

        if ($email) {
            $conditions[] = 'email like :email:';
            $params['email'] = '%' . $email . '%';
        }

		if ($name) {
			$conditions[] = 'name like :name:';
			$params['name'] = '%' . $name . '%';
		}

		if ($phone) {
			$conditions[] = 'phone like :phone:';
			$params['phone'] = '%' . $phone . '%';
		}

		if ($id) {
			$conditions[] = 'user_id=:id:';
			$params['user_id'] = $id;
		}

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$builder = $this->modelsManager->createBuilder()
			->columns('*')
			->from('Common\Models\IUser')
			->where($conditionSql, $params)
			->orderBy(IUser::getPkCol() . ' DESC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 10,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));
		$paginate = $paginator->getPaginate();
		unset($paginator);

		$list = [];
		foreach ($paginate->items as $k => $item) {
			$list[$k] = $item->toArray();
		}

		if ($this->request->isAjax()) {
			$this->view->disable();
			$data = [
				'status' => '1',
				'code' => '',
				'data' => [
					'list' => $list,
					'total_pages' => $paginate->total_pages,
				]
			];
			$this->sendJSON($data);
		}
	}
}

