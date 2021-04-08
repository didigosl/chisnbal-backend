<?php
namespace Admin\Controllers;

use Common\Models\IDeliveryFee;
use Common\Models\IDeliveryFeeMeasure;
use Common\Models\SSetting;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 运费
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class IDeliveryFeeController extends ControllerAuth
{

	public function initialize()
	{
		parent::initialize();

		$this->controller_name = '运费';
		$this->view->setVar('controller_name', $this->controller_name);
	}

	/**
	 * @aclDesc 设置运费
	 */
	public function settingAction()
	{

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$user_id = $this->request->getQuery('user_id');
		$status = $this->request->getQuery('status');
		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditions[] = 'shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		$conditions[] = 'area_id>0';

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		//运费计算方式
		if ($this->conf['delivery_fee_type'] == 'default') {
			// $settings = SSetting::find(['name like "delivery%"']);
			$Default = IDeliveryFee::findFirst([
				'shop_id=:shop_id: AND area_id=0',
				'bind' => ['shop_id' => $this->auth->getShopId()]
			]);
			if (!$Default) {
				$Default = new IDeliveryFee;
			}

			$builder = $this->modelsManager->createBuilder()
				->columns('*')
				->from(['u' => 'Common\Models\IDeliveryFee'])
				->where($conditionSql, $params)
				->orderBy('area_id asc,' . IDeliveryFee::getPkCol() . ' ASC');

		} else {

			$Default = IDeliveryFeeMeasure::findFirst([
				'shop_id=:shop_id: AND area_id=0',
				'bind' => ['shop_id' => $this->auth->getShopId()]
			]);

			if (!$Default) {
				$Default = new IDeliveryFeeMeasure;
				$Default->basic_measure = 1;
				$Default->step_measure = 1;
			}

			$builder = $this->modelsManager->createBuilder()
				->columns('*')
				->from(['u' => 'Common\Models\IDeliveryFeeMeasure'])
				->where($conditionSql, $params)
				->orderBy('area_id asc,' . IDeliveryFeeMeasure::getPkCol() . ' ASC');


		}

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));


		$this->breadcrumbs[] = [
			'text' => $this->controller_name,
		];

		//免运费金额
		$delivery_free_limit = $this->db->fetchColumn("SELECT delivery_free_limit FROM i_shop WHERE shop_id=:shop_id", ['shop_id' => $params['shop_id']]);

		$this->view->setVars([
			'controller_name' => $this->controller_name,
			'action_name' => '设置',
			'Default' => $Default,
			'delivery_free_limit' => $delivery_free_limit,
			// 'settings'=>$settings,
			'page' => $paginator->getPaginate(),
			'vars' => [],
		]);

	}

	public function updateSettingAction()
	{

		if ($this->request->isPost()) {

			$id = $this->request->getPost('id', 'int');
			$data['area_id'] = 0;

			$delivery_free_limit = $this->request->getPost('delivery_free_limit');
			if ($delivery_free_limit) {
				$delivery_free_limit = fmtPrice($delivery_free_limit);
			} else {
				$delivery_free_limit = 0;
			}

			$conf = conf();

			$shop_id = (int)$this->auth->getShopId();
			if ($conf['delivery_fee_type'] == 'default') {

				$data['fee'] = $this->request->getPost('fee');
				$data['fee'] = fmtPrice($data['fee']);
				$data['shop_id'] = $shop_id;

				if ($id) {
					$Model = IDeliveryFee::findFirst($id);
					if (!$Model) {
						$data = [
							'status' => '0',
							'code' => '',
							'msg' => '不存在您要操作的数据'
						];
					}

					if ($Model->area_id > 0) {
						throw new \Exception('操作非法:error area_id', 1);
					}

					if ($Model->shop_id != $shop_id) {
						throw new \Exception('操作非法:error shop_id', 1);
					}
				} else {

					$Model = new IDeliveryFee;
				}


			} else {
				$data['shop_id'] = $shop_id;
				$data['basic_fee'] = $this->request->getPost('basic_fee');
				$data['basic_fee'] = fmtPrice($data['basic_fee']);
				$data['step_fee'] = $this->request->getPost('step_fee');
				$data['step_fee'] = fmtPrice($data['step_fee']);
				$data['basic_measure'] = $this->request->getPost('basic_measure');				
				$data['step_measure'] = $this->request->getPost('step_measure');				

				if($conf['delivery_fee_type']=='percent'){
					$data['basic_measure'] = fmtPrice($data['basic_measure']);
					$data['step_measure'] = fmtPrice($data['step_measure']);
				}

				// var_dump($data);exit;
				if ($id) {
					$Model = IDeliveryFeeMeasure::findFirst($id);
					if (!$Model) {
						$data = [
							'status' => '0',
							'code' => '',
							'msg' => '不存在您要操作的数据'
						];
					}

					if ($Model->area_id > 0) {
						throw new \Exception('操作非法:error area_id', 1);
					}
					if ($Model->id && $Model->shop_id != $shop_id) {
						throw new \Exception('操作非法:error shop_id', 1);
					}
				} else {

					$Model = new IDeliveryFeeMeasure;
				}
			}

			$this->db->begin();

			try {
				$Model->assign($data);

				if ($Model->save()) {

					$this->db->updateAsDict('i_shop', ['delivery_free_limit' => $delivery_free_limit], 'shop_id=' . $shop_id);

					SAdminLog::add($Model->getSource(), $this->dispatcher->getActionName(), $Model->id, $Model->area_id ? $Model->Area->getFullName() : '全局运费');
					$this->flashSession->success("数据提交成功");

					$this->db->commit();

					if ($this->request->isAjax()) {
						$this->view->disable();
						$this->sendJSON([
							'status' => '1',
						]);
					} else {
						$this->jump();
					}

				} else {
					$this->db->rollback();
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e) {
				throw new \Exception($e->getMessage(), 1);
			}
		}
	}

	/**
	 * @aclDesc 新增
	 * @return [type] [description]
	 */
	public function createAction()
	{
		$this->modify();
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
		$M = IDeliveryFee::findFirst($id);
		if (!$M) {
			$M = new IDeliveryFee;
		}

		$this->view->setVars([
			'M' => $M,
			'link_select_default' => $link_select_default,
			'link_spu' => $link_spu
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
			$this->view->pick($this->dispatcher->getControllerName() . '/form');
		}

	}

	protected function modify()
	{

		if ($this->request->isPost()) {

			$id = $this->request->getPost('id', 'int');
			$data['area_id'] = $this->request->getPost('area_id', 'int');
			$data['fee'] = $this->request->getPost('fee');
			$data['fee'] = fmtPrice($data['fee']);

			$shop_id = (int)$this->auth->getShopId();

			if ($id) {
				$Model = IDeliveryFee::findFirst($id);
				if (!$Model) {
					$data = [
						'status' => '0',
						'code' => '',
						'msg' => '不存在您要操作的数据'
					];
				}

				if ($Model->shop_id != $shop_id) {
					throw new \Exception('操作非法', 1);
				}
			} else {

				$Model = new IDeliveryFee;
			}

			$this->db->begin();

			try {
				$Model->assign($data);

				if ($Model->save()) {

					SAdminLog::add($Model->getSource(), $this->dispatcher->getActionName(), $Model->id, $Model->area_id ? $Model->Area->getFullName() : '全局运费');
					$this->flashSession->success("数据提交成功");

					$this->db->commit();

					if ($this->request->isAjax()) {
						$this->view->disable();
						$this->sendJSON([
							'status' => '1',
						]);
					} else {
						$this->jump();
					}

					// $this->jump($this->url->get($this->base_url."/setting"));
				} else {
					$this->db->rollback();
					throw new \Exception($Model->getErrorMsg(), 1);
				}
			} catch (Exception $e) {
				throw new \Exception($e->getMessage(), 1);
			}

		} else {
			$this->form();
		}
	}

	/**
	 * @aclDesc 删除
	 * @return [type] [description]
	 */
	public function deleteAction()
	{
		$this->view->disable();
		if ($this->request->isAjax()) {
			$id = $this->request->getQuery('id', 'int');
			$M = IDeliveryFee::findFirst($id);

			if ($M->delete()) {
				SAdminLog::add($M->getSource(), $this->dispatcher->getActionName(), $M->id, $M->Area->getFullName());
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


}