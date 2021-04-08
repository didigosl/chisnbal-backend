<?php
namespace Admin\Controllers;

use Common\Models\ICsSession;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 客服对话
 * @acl shopadmin,superadmin
 * @aclCustom super,single_shop
 */
class ICsSessionController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '客服对话';
		$this->view->setVar('controller_name',$this->controller_name);
    }
    
    public function indexAction(){
        $page = $this->request->getQuery("p", "int");
        $page = $page ? $page : 1;
        
        $Admin = auth()->getUser();

		$conditions = [
            ' (kf_admin_id=0 OR kf_admin_id=:admin_id:) '
        ];
        $params = [
            'admin_id'=>$Admin->id
        ];

        $shop_id = $this->auth->getShopId();
        if($shop_id){
            $conditions[] = 's.shop_id=:shop_id:';
		    $params['shop_id'] = $shop_id;
        }        
        // var_dump($params);exit;
		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns(['s.*'])
                ->from(['s'=>'Common\Models\ICsSession'])
                ->join('Common\Models\IUser','u.user_id=s.user_id','u')
                // ->
                ->where($conditionSql,$params)
                ->orderBy('s.update_time DESC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
        ));

        $this->view->setVars([
			'controller_name'=>$this->controller_name,
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),
			'vars' => [],
		]);
    }



}