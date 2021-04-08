<?php
namespace Admin\Controllers;

use Common\Models\IOrder;
use Common\Models\IGoodsSpu;
use Common\Models\IShop;
use Common\Models\SAdminLog;
use Common\Models\IUser;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 订单
 * @aclCustom super,single_shop,multi_shop
 */
class IOrderController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '订单';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 对账单
	 * @acl superadmin
	 * @aclCustom super
	 */
	public function listAction(){

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$shop_id = $this->request->getQuery("shop_id", "int");
		$flag = $this->request->getQuery('flag');
		$refound_flag = $this->request->getQuery('refound_flag');
		$range = $this->request->getQuery('range');
		$success_flag = $this->request->getQuery('success_flag');
		$close_flag = $this->request->getQuery('close_flag');

		$sn = $this->request->getQuery('sn','trim');
		$start_day = $this->request->getQuery('start_day');
		$end_day = $this->request->getQuery('end_day');

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditions[] = 'shop_id>0';
		$conditions[] = 'close_flag=0';
		$conditions[] = 'flag>1';

		if($shop_id){
			$conditions[] = 'shop_id=:shop_id:';
			$params['shop_id'] = $shop_id;
		}
		

		if($flag){
			if($flag==4){
				$conditions[] = 'flag>=:flag:';
			}
			else{
				$conditions[] = 'flag=:flag:';
			}
			
			$params['flag'] = $flag;
		}

		if($refound_flag=='refound'){
			$conditions[] = 'refound_flag>0 and refound_flag<3';
		}

		if($range=='in3months'){
			$conditions[] = 'create_time>=:in3months:';
			$params['in3months'] = date(strtotime('3 months ago'));
		}

		if($success_flag=='success'){
			$conditions[] = 'flag>3';
		}


		if($sn){
			$conditions[] = 'sn=:sn:';
			$params['sn'] = $sn;
		}

		if($start_day){
			$conditions[] = 'create_time>=:start:';
			$params['start'] = date('Y-m-d H:i:s',strtotime($start_day));
		}

		if($end_day){
			$conditions[] = 'create_time<:end:';
			$params['end'] = date('Y-m-d H:i:s',strtotime($end_day.' +1 day'));
		}

		if($id){
			$conditions[] = 'order_id=:id:';
			$params['order_id'] = $id;
		}

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IOrder')
                ->where($conditionSql,$params)
                ->orderBy(IOrder::getPkCol().' DESC');
		

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'controller_name'=>'对账单',
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'flag'=>$flag,
				'refound_flag'=>$refound_flag,
				'range'=>$range,
				'success_flag'=>$success_flag,
				'close_flag'=>$close_flag,
				'sn'=>$sn,
				'start_day'=>$start_day,
				'end_day'=>$end_day,
			],
		]);
	}

	/**
	 * @aclDesc 列表
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$flag = $this->request->getQuery('flag');
		$refound_flag = $this->request->getQuery('refound_flag');
		$range = $this->request->getQuery('range');
		$success_flag = $this->request->getQuery('success_flag');
		$close_flag = $this->request->getQuery('close_flag');

		$sn = $this->request->getQuery('sn');
		$receive_man = $this->request->getQuery('receive_man');
		$start_day = $this->request->getQuery('start_day');
        $end_day = $this->request->getQuery('end_day');
        
        $user_id = $this->request->getQuery('user_id');

		$id = $this->request->getQuery('id');

		$conf = conf();

		$conditions = [];
		$params = [];

		$conditions[] = 'shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		if($flag){
			$conditions[] = 'flag=:flag:';
			$params['flag'] = $flag;
        }
        
		if($conf['hide_unpaid_order']){
			$conditions[] = 'flag!=1';
		}

		if($refound_flag=='refound'){
			$conditions[] = 'refound_flag>0 and refound_flag<3';
		}

        if($range=='new'){
            $conditions[] = 'flag>0 AND flag<3';
        }

		if($range=='in3months'){
			$conditions[] = 'create_time>=:in3months:';
			$params['in3months'] = date(strtotime('3 months ago'));
		}

		if($success_flag=='success'){
			$conditions[] = 'flag>3';
		}

		if($close_flag=='close'){
			$conditions[] = 'close_flag>0';
		}

		if($sn){
			$conditions[] = 'sn=:sn:';
			$params['sn'] = $sn;
		}

		if($receive_man){
			if(is_numeric($receive_man)){
				$conditions[] = 'receive_phone like :receive_phone:';
				$params['receive_phone'] = '%'.$receive_man.'%';
			}
			else{
				$conditions[] = 'receive_man like :receive_man:';
				$params['receive_man'] = '%'.$receive_man.'%';
			}
			
		}

		if($start_day){
			$conditions[] = 'create_time>=:start:';
			$params['start'] = date('Y-m-d H:i:s',strtotime($start_day));
		}

		if($end_day){
			$conditions[] = 'create_time<:end:';
			$params['end'] = date('Y-m-d H:i:s',strtotime($end_day.' +1 day'));
        }
        
        if($user_id){
			$conditions[] = 'user_id=:user_id:';
            $params['user_id'] = $user_id;
            
            $User = IUser::findFirst($user_id);
            if(!$User){
                throw new \Exception('用户不存在');
            }

            $action_name = '['.$User->country_code.$User->phone.'('.$User->name.')]的订单列表';
		}

		if($id){
			$conditions[] = 'order_id=:id:';
			$params['order_id'] = $id;
		}

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IOrder')
                ->where($conditionSql,$params)
                ->orderBy('update_time DESC,create_time DESC');
		

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		//近三月订单
		$total_of_3months = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND create_time>=:in3months",[
			'shop_id'=>$this->auth->getShopId(),
			'in3months'=>date(strtotime('3 months ago'))
		]);
		//等待发货
		$total_of_waiting_send = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND flag=2",[
			'shop_id'=>$this->auth->getShopId(),
		]);
		//已发货
		$total_of_sent = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND flag=3",[
			'shop_id'=>$this->auth->getShopId(),
		]);
		//退款中
		$total_of_refound = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND refound_flag>0 and refound_flag<3",[
			'shop_id'=>$this->auth->getShopId(),
		]);
		//成功订单
		$total_of_success = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND flag>3",[
			'shop_id'=>$this->auth->getShopId(),
		]);
		//关闭订单
		$total_of_close = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND close_flag>0",[
			'shop_id'=>$this->auth->getShopId(),
		]);
		//全部订单
		$total_of_all = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id",[
			'shop_id'=>$this->auth->getShopId(),
        ]);        
        //新订单
        if($conf['hide_unpaid_order']){ //隐藏未付款订单
            $total_of_new = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND flag=2",[
                'shop_id'=>$this->auth->getShopId(),
            ]);
        }
        else{
            $total_of_new = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND flag>0 AND flag<3",[
                'shop_id'=>$this->auth->getShopId(),
            ]);
        }
		
		
		// var_dump($total_of_3months);exit;

		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'action_name'=> $action_name ? $action_name : '列表',
			'page' => $paginator->getPaginate(),
			'vars' => [
				'flag'=>$flag,
				'refound_flag'=>$refound_flag,
				'range'=>$range,
				'success_flag'=>$success_flag,
				'close_flag'=>$close_flag,
				'sn'=>$sn,
				'receive_man'=>$receive_man,
				'start_day'=>$start_day,
				'end_day'=>$end_day,
				
			],
			'total_of_3months'=>$total_of_3months,
			'total_of_waiting_send'=>$total_of_waiting_send,
			'total_of_sent'=>$total_of_sent,
			'total_of_refound'=>$total_of_refound,
			'total_of_success'=>$total_of_success,
			'total_of_close'=>$total_of_close,
			'total_of_all'=>$total_of_all,
            'total_of_new'=>$total_of_new
		]);

	}

	/**
	 * @aclDesc 查看
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function settingAction(){
		$id = $this->request->getQuery('id','int');

		$M = IOrder::findFirst($id);
		if(!$M){
			$M = new IOrder;
		}

		$this->view->setVars([
			'M'=>$M
		]);
		
	}

	/**
	 * @aclDesc 收发货信息
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function updateDeliveryAction(){

		if($this->request->isPost()){
			$data = [];

			$order_id = $this->request->getPost('order_id','int');
            $data['receive_man'] = $this->request->getPost('receive_man');
            $data['receive_area'] = $this->request->getPost('receive_area');
			$data['receive_address'] = $this->request->getPost('receive_address');
			$data['receive_phone'] = $this->request->getPost('receive_phone');
			$data['express_corp_id'] = (int)$this->request->getPost('express_corp_id','int');
			$data['express_no'] = $this->request->getPost('express_no');

			$Order = IOrder::findFirst($order_id);
			if(!$Order){
				throw new \Exception("订单不存在", 1);
			}

			if( ($Order->flag < 2 || $Order->close_flag) && ($data['express_corp_id'] || $data['express_no'])){
				throw new \Exception("订单尚未付款或已经关闭，不可修改收发货信息", 1);
				
			}

			$Order->assign($data);
			$this->db->begin();
			if($Order->save()){
				$this->db->commit();

				SAdminLog::add($Order->getSource(),$this->dispatcher->getActionName(),$Order->order_id,'订单号'.$Order->sn);
				$this->flashSession->success("数据提交成功");
				$this->jump($this->url->get($this->base_url."/setting",['id'=>$Order->order_id]));
			}
			else{
				$this->db->rollback();
				throw new \Exception($e->getMessage(), 1);
			}
		}
	}

	/**
	 * @aclDesc 删除
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function deleteAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IOrder::findFirst($id);
			
			$this->db->begin();
			if($M->remove()){

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->order_id,'订单号'.$M->sn);
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}
	}

	/**
	 * @aclDesc 关闭
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function closeAction(){
		$this->view->disable();
		if($this->request->isAjax()){
			$id = $this->request->getQuery('id','int');
			$M = IOrder::findFirst($id);
			if(!$M){
				throw new \Exception("订单不存在", 1);
				
			}
			$this->db->begin();
			if($M->close('admin')){

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->order_id,'订单号'.$M->sn);
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}
	}

	/**
	 * @aclDesc 确认退款
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function refoundAction(){		
		
		if($this->request->isAjax()){
			$this->view->disable();

			$id = $this->request->getQuery('id','int');
			$M = IOrder::findFirst($id);
			if(!$M){
				throw new \Exception("订单不存在", 1);
				
			}
			
			$this->db->begin();
			if($M->refound()){

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->order_id,'订单号'.$M->sn);
				$this->flashSession->success("操作成功，订单已完成退款");
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}

	}

	/**
	 * @aclDesc 确认收款
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function paidAction(){		
		
		if($this->request->isAjax()){
			$this->view->disable();

			$id = $this->request->getQuery('id','int');
			$M = IOrder::findFirst($id);
			if(!$M){
				throw new \Exception("订单不存在", 1);
				
			}
			$this->db->begin();
			if($M->paid()){

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->order_id,'订单号'.$M->sn);
				$this->flashSession->success("操作成功，订单修改为已付款状态");
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}
			$this->sendJSON($data);

		}

	}


	/**
	 * @aclDesc 发货
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function deliveryAction(){	

		$this->view->disable();	
		$id = $this->request->get('id','int');
		$M = IOrder::findFirst($id);
		if(!$M){
			throw new \Exception("订单不存在", 1);
			
		}

		if($this->request->isPost()){	

			$data['express_corp_id'] = $this->request->getPost('express_corp_id','int');
			$data['express_no'] = $this->request->getPost('express_no');	
			
			$this->db->begin();
			if($M->delivery($data)){

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->order_id,'订单号'.$M->sn);
				$this->flashSession->success("操作成功，订单发货完成");
				$this->db->commit();
				$data = [
					'status'=>'1',
					'code'=>'',
					'msg'=>$msg
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>'0',
					'code'=>'',
				];
			}			

		}
		else{
			$this->view->setVars([
				'M'=>$M,
			]);
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),'delivery');
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
		}

		$this->sendJSON($data);

	}

	/**
	 * @aclDesc 调价
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function adjustAction(){			

		if($this->request->isPost()){	

			$this->view->disable();	
			$id = $this->request->get('id','int');
			$M = IOrder::findFirst($id);
			if(!$M){
				throw new \Exception("订单不存在", 1);
				
			}

			$new_total_amount = $this->request->getPost('new_total_amount');

			$this->db->begin();

			if($M->adjust($new_total_amount)){

				SAdminLog::add($M->getSource(),$this->dispatcher->getActionName(),$M->order_id,'订单号'.$M->sn);
				$this->flashSession->success("操作成功，订单总价已经修改");
				$this->db->commit();
				$data = [
					'status'=>1,
					'code'=>'',
					'msg'=>$msg
				];
			}
			else{
				$this->db->rollback();
				$data = [
					'status'=>0,
					'code'=>'',
				];
			}			
			$this->sendJSON($data);
		}

    }
    
    /**
	 * @aclDesc 打印配货单
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function printAction(){
		$id = $this->request->getQuery('id','int');

		$Order = IOrder::findFirst($id);
		if(!$Order){
			$Order = new IOrder;
        }
        
        $skus = [];
        foreach($Order->skus as $Sku){
            if(!isset($skus[$Sku->distribution_type_id])){
                if($Sku->distribution_type_id){
                    $skus[$Sku->distribution_type_id] = [
                        'distribution_type'=>[
                            'distribution_type_id'=>$Sku->distribution_type_id,
                            'name'=>$Sku->DistributionType->name,
                        ],
                        'list'=>[
                            'weigh'=>[],
                            'no_weigh'=>[]
                        ],
                    ];
                }
                else{
                    $skus[0] = [
                        'distribution_type'=>null,
                        'list'=>[
                            'weigh'=>[],
                            'no_weigh'=>[]
                        ],
                    ];
                }
            }
            
            if($Sku->sku->weigh_flag){
                $skus[$Sku->distribution_type_id]['list']['weigh'][] = $Sku;
            }
            else{
                $skus[$Sku->distribution_type_id]['list']['no_weigh'][] = $Sku;
            }
            
        }

        $Shop = IShop::findFirst(auth()->getShopId());

		$this->view->setVars([
            'Order'=>$Order,
            'skus'=>$skus,
            'Shop'=>$Shop
		]);
		
    }
    
    /**
	 * @aclDesc 批量打印配货单
	 * @acl shopadmin
	 * @aclCustom single_shop,multi_shop
	 */
	public function batPrintAction(){

        $flag = $this->request->getQuery('flag');
		$refound_flag = $this->request->getQuery('refound_flag');
		$range = $this->request->getQuery('range');
		$success_flag = $this->request->getQuery('success_flag');
		$close_flag = $this->request->getQuery('close_flag');

		$start_day = $this->request->getQuery('start_day');
		$end_day = $this->request->getQuery('end_day');


		$conf = conf();

		$conditions = [];
		$params = [];

		$conditions[] = 'shop_id=:shop_id:';
		$params['shop_id'] = $this->auth->getShopId();

		if($flag){
			$conditions[] = 'flag=:flag:';
			$params['flag'] = $flag;
        }
        
		if($conf['hide_unpaid_order']){
			$conditions[] = 'flag!=1';
		}

		if($refound_flag=='refound'){
			$conditions[] = 'refound_flag>0 and refound_flag<3';
		}

        if($range=='new'){
            $conditions[] = 'flag>0 AND flag<3';
        }

		if($range=='in3months'){
			$conditions[] = 'create_time>=:in3months:';
			$params['in3months'] = date(strtotime('3 months ago'));
		}

		if($success_flag=='success'){
			$conditions[] = 'flag>3';
		}

		if($close_flag=='close'){
			$conditions[] = 'close_flag>0';
		}

		if($start_day){
			$conditions[] = 'create_time>=:start:';
			$params['start'] = date('Y-m-d H:i:s',strtotime($start_day));
		}

		if($end_day){
			$conditions[] = 'create_time<:end:';
			$params['end'] = date('Y-m-d H:i:s',strtotime($end_day.' +1 day'));
		}


		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';

		$orders = IOrder::find([
            $conditionSql,
            'bind'=>$params
        ]);

        $list = [];
        $totalPage = 0;
        foreach($orders as $Order){
            $skus = [];
            foreach($Order->skus as $Sku){
                if(!isset($skus[$Sku->distribution_type_id])){
                    if($Sku->distribution_type_id){
                        $skus[$Sku->distribution_type_id] = [
                            'distribution_type'=>[
                                'distribution_type_id'=>$Sku->distribution_type_id,
                                'name'=>$Sku->DistributionType->name,
                            ],
                            'list'=>[],
                        ];
                    }
                    else{
                        $skus[0] = [
                            'distribution_type'=>null,
                            'list'=>[],
                        ];
                    }
                }
                
                $skus[$Sku->distribution_type_id]['list'][] = $Sku;
            }

            $totalPage += count($skus);

            $list[] = [
                'Order'=>$Order,
                'skus'=>$skus
            ];
        }

        $Shop = IShop::findFirst(auth()->getShopId());

		$this->view->setVars([
            'list'=>$list,
            'Shop'=>$Shop,
            'totalPage'=>$totalPage
		]);
		
    }
    
    public function newTotalAction(){
        
        $conf = conf();
        //新订单
        if($conf['hide_unpaid_order']){
            $total_of_new = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND flag>1 AND flag<3",[
                'shop_id'=>$this->auth->getShopId(),
            ]);
        }
        else{
            $total_of_new = db()->fetchColumn("SELECT count(1) FROM i_order WHERE shop_id=:shop_id AND flag>0 AND flag<3",[
                'shop_id'=>$this->auth->getShopId(),
            ]);
        }
        
        $this->sendJSON([
            'data'=>[
                'total_of_new'=>$total_of_new
            ]
        ]);
    }

}