<?php
namespace Admin\Controllers;

use Common\Models\IUser;
use Common\Models\SAdminLog;
use Common\Models\IRecharge;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 充值
 * @acl shopadmin,superadmin
 * @aclCustom super,single_shop
 */
class IRechargeController extends ControllerAuth
{

	public function initialize()
	{
		parent::initialize();

		$this->controller_name = '充值';
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
	 * @aclDesc 充值
	 * @return [type] [description]
	 */
	public function createAction()
	{
		$this->view->disable();
		if ($this->request->isAjax()) {
            $id = $this->request->getQuery('id', 'int');
            $amount = $this->request->getQuery('amount');

            if(!is_numeric($amount)){
                throw new \Exception("充值金额必须是数字，最多两位小数", 1);
            }
			$User = IUser::findFirst($id);
			if (!$User) {
				throw new \Exception("用户不存在", 1);
            }
            
            $Recharge = new IRecharge;
            $Recharge->user_id = $User->user_id;
            $Recharge->amount = floor($amount * 100);
            $Recharge->result = 'success';

			if ($Recharge->save()) {
				$data = [
					'status' => '1',
					'msg' => $msg
				];
			} else {
				$data = [
                    'status' => '0',
                    'code'  => 1
				];
			}
			$this->sendJSON($data);
		}
	}
}

