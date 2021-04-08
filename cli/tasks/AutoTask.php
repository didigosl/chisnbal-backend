<?php
use \Common\Models\IOrder;
use \Common\Models\IFlashSale;
use \Common\Models\ICoupon;
use \Common\Models\IAd;
use	\Common\Models\StatDayOrder;
use	\Common\Models\StatDaySale;
use Common\Components\Cache;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

class AutoTask extends \Phalcon\Cli\Task
{
    protected $log;

    public function initialize(){
        $this->log = new FileAdapter(SITE_PATH.'/logs/cli.txt');
        
    }

    public function flashSaleAction(){
        $this->log->info('flashSale...');
        $now = time();
        $this->db->begin();
        try{

            $list = IFlashSale::find([
                'status<3'
            ]);
            if($list){
                foreach ($list as $Sale) {
                    $this->log->info('sale:'.$Sale->sale_id);
                    if($Sale){
                        $start_time = strtotime($Sale->start_time);
                        $end_time = strtotime($Sale->end_time);
                        if($end_time<=$now){
                            $this->log->info('exe end:'.$Sale->sale_id);
                            $Sale->finish();
                        }
                        elseif($Sale->status==1 AND $start_time<=$now AND $end_time>$now){
                            $this->log->info('exe start:'.$Sale->sale_id);
                            $Sale->start();
                        }
                    }
                }
            }
            
            $this->db->commit();
            $this->log->info('flashSale ran');
        } catch ( \Exception $e){
            $this->db->rollback();
            $this->log->error('flashSale error:'.$e->getMessage());
        }
        
    }

    //更新过期的订单
    public function orderExpireAction(){
        
        //取消过期未支付的订单
        $this->log->info('orderExpire ...');
        $settings = Cache::init()->getSettings();

        if($settings['order_pay_expired']){
            $now = date('Y-m-d H:i:s',strtotime('-'.$settings['order_pay_expired'].' hours'));
            $this->log->info('orderExpire: now='.$now);

            $list = IOrder::find([
                'flag=1 AND close_flag=0 AND create_time<:now:',
                'bind'=>['now'=>$now]
            ]);
            if($list){
                $this->db->begin();
                foreach ($list as $Order) {
                    
                    if($Order->close('sys')){
                        $this->log->info('close order:'.$Order->order_id);
                    }
                    else{
                        $this->db->rollback();
                        $this->log->info('fail to close order:'.$Order->order_id);
                    }
                    
                }
                $this->db->commit();
            }

        }
        
        //已发货订单到期自动确认完成
        $this->log->info('orderFinish ...');
        $settings = Cache::init()->getSettings();
        $order_finish_expired = $settings['order_finish_expired'] ? $settings['order_finish_expired'] : 7;
        $time = date('Y-m-d H:i:s',strtotime('-'.$order_finish_expired.' days'));
        $this->log->info('orderFinish: time='.$time);

        $list = IOrder::find([
            'flag=3 AND close_flag=0 AND delivery_time<:time:',
            'bind'=>['time'=>$time]
        ]);
        if($list){
            $this->db->begin();
            foreach ($list as $Order) {
                
                if($Order->finish('sys')){
                    $this->log->info('close finish:'.$Order->order_id);
                    
                }
                else{
                    $this->db->rollback();
                    $this->log->info('fail to finish order:'.$Order->order_id);
                }
                
            }
            $this->db->commit();
        }
    }

    //设置代金券生效失效
    public function couponAction(){
        $this->log->info('coupon ...');
        $list = ICoupon::find([
            'status<3',
        ]);
        if($list){
            foreach ($list as $Coupon) {
                $this->db->begin();

                $now = time();
                $start_time = strtotime($Coupon->start_time);
                $end_time = strtotime($Coupon->end_time);

                if($now>$end_time){
                    $Coupon->status = 3; 
                }
                elseif($now>$start_time and $now<$end_time){
                    $Coupon->status = 2; 
                }

                if($Coupon->save()){
                    $this->db->commit();
                    if($Coupon->status==2){
                        $this->log->info('enable coupon:'.$Coupon->coupon_id);
                    }
                    elseif($Coupon->status==3){
                        $this->log->info('close coupon:'.$Coupon->coupon_id);
                    }
                    
                }
                else{
                    $this->db->rollback();
                }
               
                
            }
        }
    }

    //设置广告失效生效
    public function adAction(){
        $this->log->info('ad ...');
        $list = IAd::find([
            'status=1 or status=3',
        ]);
        if($list){
            foreach ($list as $Ad) {
                $this->db->begin();

                $now = time();
                $start_time = strtotime($Ad->start_time);
                $end_time = strtotime($Ad->end_time);

                if($now>$end_time){
                    $Ad->status = 2; 
                }
                elseif($now>$start_time and $now<$end_time and $Ad->status==1){
                    $Ad->status = 3; 
                }

                if($Ad->save()){
                    $this->db->commit();
                    if($Ad->status==3){
                        $this->log->info('enable ad:'.$Ad->ad_id);
                    }
                    elseif($Ad->status==2){
                        $this->log->info('close ad:'.$Ad->ad_id);
                    }
                    
                }
                else{
                    $this->db->rollback();
                }
               
                
            }
        }
    }

    public function statAction($params=[]){
        $this->log->info('stat ...');
    	if(!$params[0]){
    		$day = date('Y-m-d',strtotime('last day'));
    	}
    	else{
    		$day = $params[0];
    	}

        echo 'computing '.$day."\n";

    	$last_day_begin = date('Y-m-d H:i:s',strtotime($day));
		$last_day_end = date('Y-m-d H:i:s',strtotime($day.' +1 day'));
	
		//stat_day_order 
		$res = $this->db->fetchOne('SELECT count(order_id) AS num,sum(total_amount) AS amount FROM i_order WHERE flag>=2 AND create_time>=:day_begin AND create_time<:day_end',\Phalcon\Db::FETCH_ASSOC,[
			'day_begin'=>$last_day_begin,
			'day_end'=>$last_day_end
		]);

		$DayOrder = StatDayOrder::findFirst([
			'day=:day:',
			'bind'=>['day'=>$day]
		]);

		if(!$DayOrder){
			$DayOrder = new StatDayOrder;
		}
		
		$DayOrder->assign([
			'day'=>$day,
			'num'=>$res['num'] ? $res['num'] : 0,
			'amount'=>$res['amount'] ? $res['amount'] : 0,
		]);

		if($DayOrder->save()){
			echo $day." computing $day order finished.\n";
		}
		else{
			$logger = new FileAdapter(SITE_PATH.'/logs/auto_stat.txt');
                    $logger->error($day.' error:'.$DayOrder->getErrorMsg());
		}

		//stat_day_sale
		$res = $this->db->fetchAll('SELECT any_value(sku_id) as sku_id,any_value(spu_id) as spu_id,sum(num) as num FROM i_order_sku as sku 
		join i_order as o on o.flag>=2 AND o.create_time>=:day_begin AND o.create_time<:day_end AND o.order_id=sku.order_id 
			GROUP BY sku.sku_id',\Phalcon\Db::FETCH_ASSOC,[
			'day_begin'=>$last_day_begin,
			'day_end'=>$last_day_end
		]);
        // var_dump($res);
        
        foreach($res as $k=>$v){
            echo $v['sku_id'].PHP_EOL;
            $DaySale = StatDaySale::findFirst([
                'day=:day: and sku_id=:sku_id:',
                'bind'=>[
                    'day'=>$day,
                    'sku_id'=>$v['sku_id']
                ]
            ]);
    
            if(!$DaySale){
                $DaySale = new StatDaySale;
            }
            
            $DaySale->assign([
                'day'=>$day,
                'num'=>$v['num'] ? $v['num'] : 0,
                'spu_id'=>$v['spu_id'],
                'sku_id'=>$v['sku_id'],
            ]);
    
            if($DaySale->save()){
                echo $day." computing $day sale finished.\n";
            }
            else{
                $logger = new FileAdapter(SITE_PATH.'/logs/auto_stat.txt');
                $logger->error($day.' error:'.$DaySale->getErrorMsg());
            }
        }

		
    }

    public function statDaysAction($params=[]){
        $this->log->info('stat days ...');
        if($params[0]){
            $day = $params[0];
        }
        else{
            echo 'please input the start date'.PHP_EOL;
            exit;
        }
       
        $day_time = strtotime($day);
        $today = date('Y-m-d');
        $today_time = strtotime($today);
        $params = [];

        while($day_time<$today_time){
            $params[0] = $day;
            $this->statAction($params);

            $day = date('Y-m-d',strtotime($day.' +1 day'));
            $day_time = strtotime($day);
        }

    	
    }

}