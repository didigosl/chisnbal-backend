<?php
namespace Admin\Controllers;

use Common\Models\StatDaySale;
use Admin\Components\ControllerAuth;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 销售明细
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class StatDaySaleController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '销售明细';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$category_id = $this->request->getQuery('start_day');
		$start_day = $this->request->getQuery('start_day');
		$end_day = $this->request->getQuery('end_day');

		$start_day = $start_day ? $start_day : date('Y-m-d',strtotime('-10 days'));
		$end_day = $end_day ? $end_day : date('Y-m-d');

		$start = strtotime($start_day);
		$end = strtotime($end_day);

		$days = [];
		for ($i=$start; $i <= $end ; $i=$i+86400) { 
			$days[] = date('Y-m-d',$i);
		}
		// var_dump($days);

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditions[] = " day>=:start_day: AND day<=:end_day:";
		$params = [
			'start_day'=>$start_day,
			'end_day'=>$end_day
		];

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';


        $result = StatDaySale::find([
        	$conditionSql,
        	'bind'=>$params,
        	'order'=>StatDaySale::getPkCol().' DESC'
        ]);
        $stat_list = [];
        foreach ($result as $Item) {
        	if($Item->spu_id>0){
	        	if(!isset($stat_list[$Item->spu_id])){
	        		$stat_list[$Item->spu_id] = [
	        			'spu_name'=>$Item->Spu->spu_name,
	        			'skus'=>[],
	        		];
	        	}

	        	if(!isset($stat_list[$Item->spu_id]['skus'][$Item->sku_id])){
	        		$stat_list[$Item->spu_id]['skus'][$Item->sku_id] = [
	        			'sn'=>$Item->Sku->sku_sn,
		        		'spec_info'=>$Item->Sku->spec_info,
		    			'stat'=>[],
		    			'total'=>0,
		    			'avg'=>0,
		    			'percent'=>0
		        	];
	        	}
	        	
	        	$stat_list[$Item->spu_id]['skus'][$Item->sku_id]['stat'][$Item->day] = $Item->num;
        	}
        	
        	
        }

        // var_dump($stat_list);exit;
        $total = count($result);
        $days_total = count($days);
        foreach($stat_list as $spu_id => $item){
        	foreach($item['skus'] as $sku_id => $sku){
        		foreach($sku['stat'] as $day => $num){
        			if($num>0){
        				$stat_list[$spu_id]['skus'][$sku_id]['total'] += (int)$num;
        				$total += (int)$num;
        			}
        			
        		}

        		$stat_list[$spu_id]['skus'][$sku_id]['avg'] = round($stat_list[$spu_id]['skus'][$sku_id]['total']/$days_total,1);
        	}
        }

        foreach($stat_list as $spu_id => $item){
        	foreach($item['skus'] as $sku_id => $sku){
        		$stat_list[$spu_id]['skus'][$sku_id]['percent'] = round($stat_list[$spu_id]['skus'][$sku_id]['total']/$total*100,2).'%';
        	}
        }
        // echo '<pre>';
        // var_dump($stat_list);
        // echo '</pre>';
        // exit;

		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'action_name'=>'列表',
			// 'page' => $paginator->getPaginate(),
			'stat_list'=>$stat_list,
			'days'=>$days,
			'vars' => [
				'start_day'=>$start_day,
				'end_day'=>$end_day
			],
		]);

	}

	public function mockDataAction(){
		$skus = $this->db->fetchAll("select * from i_goods_sku ");
		$start_day = '2017-12-01';
		$days = 4;
		for ($i=0; $i < $days; $i++) { 
			$day = date('Y-m-d',strtotime($start_day)+ 86400*$i );
			foreach ($skus as $sku) {
				$sql = "insert into stat_day_sale (spu_id,sku_id,num,day) VALUE (".$sku['spu_id'].",".$sku['sku_id'].",CEIL(rand()*100),'$day')";
				 $this->db->execute($sql);
			}
			

		}
	}


}