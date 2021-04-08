<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Common\Libs\Func;

class IGoodsSpu extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $spu_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $spu_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $cover;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $pics;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $video;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $labels;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $origin_price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $price;

    public $price2;

    public $price3;

    public $price4;

    public $price5;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $cost_price;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $rebate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $rebate_with_discount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $stock;

    public $unit;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $min_in_cart;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $min_to_buy;

    public $weigh_flag;

    /**
     *
     * @var float
     * @Column(type="float", length=11, nullable=true)
     */
    public $weight;

    public $length;

    public $width;

    public $hight;

    public $express_goods_type;    

    public $express_value;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $sn;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $content;

    /**
     *
     * @var string
     * @Column(type="string", length=250, nullable=true)
     */
    public $spec_data;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sale_spu_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $not_sale_price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sale_stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $shop_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sort_id;

    public $distribution_type_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $onsale_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $update_time;

    public $seq;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $has_default_sku;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $remove_flag;

    public $import_mode = false;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->keepSnapShots(true);
        $this->setSource("i_goods_spu");
        $this->hasMany('spu_id', 'Common\Models\ICart', 'spu_id', ['alias' => 'Cart']);
        $this->hasMany('spu_id', 'Common\Models\IFlashSaleSpu', 'spu_id', ['alias' => 'FlashSaleSpu']);
        $this->hasMany('spu_id', 'Common\Models\IGoodsSku', 'spu_id', ['alias' => 'skus']);
        $this->hasMany('spu_id', 'Common\Models\ISpuCategory', 'spu_id', ['alias' => 'categories']);
        $this->hasOne('spu_id','Common\Models\ISpuCategory', 'spu_id', [
            'alias' => 'Cate1',
            'params'=>['conditions'=>'seq=1']
        ]);
        $this->belongsTo('sale_spu_id','Common\Models\IFlashSaleSpu','id',['alias' => 'SaleSpu']);
        $this->belongsTo('sort_id','Common\Models\ISort','sort_id',['alias' => 'Sort']);
        $this->belongsTo('shop_id', 'Common\Models\IShop', 'shop_id', ['alias' => 'Shop']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_goods_spu';
    }

    static public function getPkCol(){
        return 'spu_id';
    }

    static public $attrNames = [
        'spu_name'=>'商品名称',
        'sn'=>'货号',
        'cover'=>'封面图',
        'pics'=>'商品相册',
        'video'=>'商品视频',
        'labels'=>'商品标签',
        'origin_price'=>'商品原价',
        'price'=>'商品价格',
        'cost_price'=>'成本价',
        'rebate'=>'返利',
        'rebate_with_discount'=>'与其他优惠共享',
        'stock'=>'商品数量',
        'unit'=>'数量单位',
        'min_in_cart'=>'每次加入购物车数量',
        'min_to_buy'=>'最少购买数量',
        'weigh_flag'=>'需要称重出售',
        'weight'=>'重量',
        'length'=>'长度',
        'width'=>'宽度',
        'height'=>'高度',
        'express_goods_type'=>'物品类型（物流）',
        'express_value'=>'物流价值',
        'content'=>'商品介绍',
        'status'=>'状态',
        'sort_id'=>'平台分类',
        'distribution_type_id'=>'配货类型',
        'seq'=>'排序参数'
    ];

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);

        if($cols_length==0 OR in_array('spu_name',$cols)){
            $validator->add(
                'spu_name',
                new PresenceOf()
            );
        }

        if($cols_length==0 OR in_array('sn',$cols)){
            $validator->add(
                'sn',
                new PresenceOf()
            );
        }

        if($cols_length==0 OR in_array('sn',$cols)){
            $validator->add(
                'sn',
                new Uniqueness([
                    'message'=>'##货号已经存在'
                ])
            );
        }

        if($cols_length==0 OR in_array('price',$cols)){
            $validator->add(
                'price',
                new PresenceOf()
            );
        }
           

        return $validator;
    }

    public static function getStatusContext($var = null) {
        $data = [
            -1  => '下架',
            1   => '上架',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function beforeCreate(){
        parent::beforeCreate();
        if(!$this->shop_id){
            $this->shop_id = 1;
        }
        if(!$this->import_mode){
            $this->shop_id = $this->getDi()->get('auth')->getShopId();
            $this->status = -1;
        }
        
        // if(strlen($this->seq)==0){
        //     $this->seq = 9999999;
        // }
        
    }

    public function beforeSave(){

        if($this->spu_id AND !$this->hasChanged('remove_flag') AND $this->remove_flag>0){
            throw new \Exception("此商品已被删除，无法修改", 1);
            
        }
        $this->sort_id = (int)$this->sort_id;
        $this->origin_price = (int)$this->origin_price;
        $this->price = (int)$this->price;
        $this->price2 = (int)$this->price2;
        $this->price3 = (int)$this->price3;
        $this->price4 = (int)$this->price4;
        $this->price5 = (int)$this->price5;
        $this->cost_price = (int)$this->cost_price;
        $this->rebate_with_discount = (int)$this->rebate_with_discount;
        $this->stock = (int)$this->stock;
        $this->min_in_cart = $this->min_in_cart ? (int)$this->min_in_cart : 1;
        $this->min_to_buy = $this->min_to_buy ? (int)$this->min_to_buy : 1;
        $this->sale_spu_id = (int)$this->sale_spu_id;
        $this->not_sale_price = (int)$this->not_sale_price;
        $this->sale_stock = (int)$this->sale_stock;
        $this->shop_id = $this->shop_id ? (int)$this->shop_id : 1;
        $this->remove_flag = (int)$this->remove_flag;
        $this->distribution_type_id = (int)$this->distribution_type_id;
        $this->seq = (int)$this->seq;
    }

    public function updateCategory($categories){
        // var_dump($categories);
        $db = $this->getDi()->get('db');
        if($this->spu_id AND $this->categories){
            foreach($this->categories as $SpuCategory){
               
                if(!$SpuCategory->delete()){
                    throw new \Exception("清除原商品分类错误", 1002);
                    
                }
            }
        }
        foreach($categories as $k=>$id){
            $SpuCategory = new ISpuCategory;
            $SpuCategory->assign([
                'spu_id'=>$this->spu_id,
                'category_id'=>$id,
                'seq'=>$k+1
            ]);
            if(!$SpuCategory->save()){
                throw new \Exception("更新商品分类错误", 1002);
            }
        }
        /*$db->delete('i_spu_category','spu_id='.$this->spu_id);
        foreach($categories as $k=>$id){
            $db->insertAsDict('i_spu_category',[
                'spu_id'=>$this->spu_id,
                'category_id'=>$id,
                'seq'=>$k+1
            ]);
        }*/
        
    }

    public function afterSave(){

        $conf = conf();
        if($conf['enable_sku_price_sync']){
            if($this->getOperationMade() == self::OP_UPDATE && $this->hasUpdated('price')){
                foreach($this->skus as $Sku){
                    $Sku->price = $this->price;
                    $Sku->save();
                }
            }
        }
        
    }

    public function updateSpecs($spec){
        $enable_no_sku = $this->getDi()->get('conf')['enable_no_sku'];
        if(!$enable_no_sku && !is_array($spec)){
            throw new \Exception("规格参数格式错误", 1);
            
        }

        $db = $this->getDi()->get('db');
        if(is_array($spec)){
            foreach($spec as $spec_id => $data){
                $check = $db->fetchColumn('SELECT count(1) FROM i_spu_spec WHERE spu_id=:spu_id AND spec_id=:spec_id',[
                    'spu_id'=>$this->spu_id,
                    'spec_id'=>$spec_id
                ]);

                if(!$check){
                    $SpuSpec = new ISpuSpec;
                    $SpuSpec->assign([
                        'spu_id'=>$this->spu_id,
                        'spec_id'=>$spec_id,
                        'total'=>1
                    ]);
                    $SpuSpec->save();
                }
            }
        }
        

    }   

    public function updateSkus($skus){
        
        $db = $this->getDi()->get('db');
        if(!is_array($skus)){
            // throw new \Exception("商品单品规格数据错误", 2002);
            
        }

        //允许不添加规格时，系统添加一个默认规格数据
        $enable_no_sku = $this->getDi()->get('conf')['enable_no_sku'];

        // var_dump($skus);exit;
        //检测是否需要设置默认sku
        $no_need_default_sku = 0;
        if(is_array($skus)){
            foreach($skus as $v){
                if($v['default_flag']==0 && $v['status']>0){
                    $no_need_default_sku = 1;
                }
            }
        }
        
        if(($enable_no_sku && empty($skus)) || !$no_need_default_sku) {
            $sku_data = [
                'sku_sn'=>$this->sn,
                'spec_info'=>'default',
                'stock'=>$this->stock,
                'price'=>$this->price,
                'status'=>1,
                'default_flag'=>1,
                'weigh_flag'=>$this->weigh_flag,
            ];

            //检测默认sku是否存在
            $DefaultSku = IGoodsSku::findFirst([
                'spu_id=:spu_id: AND default_flag=1',
                'bind'=>['spu_id'=>$this->spu_id]
            ]);
            
            if(!$DefaultSku){
                $DefaultSku = new IGoodsSku;
                $sku_data['spu_id'] = $this->spu_id;
            }

            $DefaultSku->assign($sku_data);
            if(!$DefaultSku->save()){

                throw new \Exception($DefaultSku->getErrorMsg(), 2002);
            }

            //讲当前商品标记为使用默认sku
            $db->execute('UPDATE i_goods_spu SET has_default_sku=1 WHERE spu_id=:spu_id',['spu_id'=>$this->spu_id]);
            //保证停用非默认sku
            $db->execute('UPDATE i_goods_sku SET status=0 WHERE spu_id=:spu_id AND default_flag=0',['spu_id'=>$this->spu_id]);
        }
        else{
            if(!empty($skus)){                
                
                //保存用户提交的skus
                foreach($skus as $sku){
                    $data = [
                        'spec_info'=>$sku['spec_info'],
                        'sku_sn'=>$sku['sn'],
                        'stock'=>(int)$sku['stock'],
                        'price'=>fmtPrice($sku['price']),
                        'status'=>$sku['status'],
                        'weigh_flag'=>$sku['weigh_flag'],
                    ];
                    // var_dump($data);exit;
                    if($sku['sku_id']){
                        $Sku = IGoodsSku::findFirst($sku['sku_id']);                
                    }
                    elseif($sku['status']>0){
                        $Sku = new IGoodsSku;
                        $data['spu_id'] = $this->spu_id;
                    }
                    if($Sku){
                        $Sku->assign($data);
                        if(!$Sku->save()){
                            throw new \Exception($Sku->getErrorMsg(), 2002);
                            
                        }
                    }
                    
                }
                //讲当前商品标记为不使用默认sku
                $db->execute('UPDATE i_goods_spu SET has_default_sku=0 WHERE spu_id=:spu_id',['spu_id'=>$this->spu_id]);
                //保证停用默认sku
                $db->execute('UPDATE i_goods_sku SET status=0 WHERE spu_id=:spu_id AND default_flag=1',['spu_id'=>$this->spu_id]);
            }
            
        }
        
        
    }

    public function getCategories(){
        $ret = [];
        foreach ($this->categories as $SpuCategory) {
            $ret[$SpuCategory->category_id] = $SpuCategory->Category->getFullName();
        }
        // var_dump($ret);exit;
        return $ret;
    }

    public function getFmtPics(){
        $ret = [];
        // $host = $host ? $host : 'http://'.$this->getDi()->get('request')->getHttpHost();
        if($this->pics){
            $ret = explode(',', $this->pics);
            foreach($ret as $k=>$v){
                $ret[$k] = Func::staticPath($v);
            }
        }
        return $ret;
    }

    public function getFmtCover(){
        $ret = '';
        if($this->cover){
            $ret = Func::staticPath($this->cover);
        }
        return $ret;
    }

    public function getFmtLabels(){
        $ret = [];

        $spu_labels = trim($this->labels,',');
        if($spu_labels){
            $tmp = ILabel::find();
            foreach ($tmp as $Label) {
                $labels[$Label->label_id] = $Label->label_name;
            }

            $spu_labels = explode(',', $spu_labels);
            foreach ($spu_labels as $v) {
                $ret[] = [
                    'label_id'=>$v,
                    'label_name'=>$labels[$v],
                ];
            }
        }

        if($this->sale_spu_id>0){
            $ret[] = [
                'label_id'=>-1,
                'label_name'=>'抢购',
            ];
        }

        return $ret;
    }

    public function getFmtDiscounts($level_id_key=false,$fmt_money=true){

        $ret = [];
        $has_discount = false;
        $SpuCategory = ISpuCategory::findFirst([
            'spu_id=:spu_id:',
            'bind'=>['spu_id'=>$this->spu_id],
            'order'=>'seq ASC'
        ]);
        

        //按商品分类确认会员优惠信息
        if($SpuCategory){
            $Category = $SpuCategory->Category;
            if($Category->discounts){
                $has_discount = true;
                foreach($Category->discounts as $Discount){
                    if($Discount->discount){
                        if($Discount->discount_type==1){
                            if($level_id_key){
                                    $ret[$Discount->level_id] = $Discount->discount;
                            }
                            else{
                                $ret[] = $Discount->discount;
                            }
                        }
                        else{
                            if($level_id_key){
                                $ret[$Discount->level_id] = $this->price * $Discount->discount * 0.01;
                            }
                            else{
                                $ret[] = $this->price * $Discount->discount * 0.01;
                            }
                        }
                    }
                    
                }
            }
        }

        //获取会员全局优惠信息
        $list = db()->fetchAll("SELECT * FROM i_user_level");
        $levels = [];
        foreach($levels as $v){
            $levels[$v['level_id']] = $v;
        }

        //没有设置分类优惠，则适用全局优惠
        foreach($ret as $k=>$v){
            if($v<=0 && $levels[$k]['discount']){
                if($levels[$k]['discount_type']==1){
                    $ret[$k] = $levels[$k]['discount'];
                }
                else{
                    $ret[$k] = $this->price * $levels[$k]['discount'] * 0.01;
                }
            }
        }

        if($fmt_money){
            foreach($ret as $k=>$v){
                $ret[$k] = fmtMoney($v);
            }
        }

        return $ret;
    }

    //获取商品表单中直接设置的返利数据
    public function getFormRebates(){
        $ret = [];
        if($this->rebate){
            $rebates = json_decode($this->rebate);

            if($rebates){
                $tmp = IUserLevel::find();
                $levels = [];
                foreach ($tmp as $Level) {
                    $levels[$Level->level_id] = $Level->level_name;
                }

                foreach ($rebates as $level_id => $value) {

                    $ret[$level_id] = [
                        'level_id'=>$level_id,
                        'level_name'=>$levels[$level_id],
                        'rebate'=> fmtMoney($value) 
                    ]; 
                    
                }
            }
        }

        return $ret;
    }

    //获取当前商品的总返利金额
    public function getAllRebate($level_id){

        $ret = 0;

        if($this->rebate){
            
            $rebates = json_decode($this->rebate);
            if($rebates){
                if($rebates->$level_id){
                    $ret = $rebates->$level_id;

                }
            }

        }
        else{ //获取主分类的返利信息
            if($this->Cate1->Category->rebates){
                foreach($this->Cate1->Category->rebates as $RebateCategory){
                    if($RebateCategory->level_id==$level_id){
                        if($RebateCategory->rebate_type==1){

                            $ret = $RebateCategory->rebate ;
                            
                        }
                        elseif($RebateCategory->rebate_type==2){
                            $ret = $this->price * (int)$RebateCategory->rebate * 0.01;
                        }
                    }
                    else{
                        continue;
                    }

                }
            }
        }

        return $ret;
    }

    public function getFmtRebates($level_id_key=false,$fmt_money=true){
        $ret = [];

        $tmp = IUserLevel::find();
        $levels = [];
        foreach ($tmp as $Level) {
            $levels[$Level->level_id] = $Level->level_name;
        }

        $settings = $this->getDi()->get('settings');

        if($this->rebate){
            
            $rebates = json_decode($this->rebate);

            if($rebates){
                

                foreach ($rebates as $level_id => $value) {

                    $value = $value ? $value : 0;

                    if($level_id_key){
                        $ret[$level_id] = [
                            'level_id'=>$level_id,
                            'level_name'=>$levels[$level_id],
                            'rebate'=>($fmt_money ? fmtMoney($value) : $value) * $settings['rebate_1'] * 0.01
                            // 'rebate'=> $value * $settings['rebate_1'] * 0.01
                        ]; 
                    }
                    else{
                        $ret[] = [
                            'level_id'=>$level_id,
                            'level_name'=>$levels[$level_id],
                            'rebate'=> ($fmt_money ? fmtMoney($value) : $value) * $settings['rebate_1'] * 0.01
                            // 'rebate'=> $value * $settings['rebate_1'] * 0.01
                        ];  
                    }
                    
                }
            }
            
        }
        else{ //获取主分类的返利信息

            if($this->Cate1->Category->rebates){
                foreach($this->Cate1->Category->rebates as $RebateCategory){
                    if($RebateCategory->rebate_type==1){
                        $money = $fmt_money ? fmtMoney($RebateCategory->rebate) :$RebateCategory->rebate ;
                        
                    }
                    elseif($RebateCategory->rebate_type==2){
                        $money = $fmt_money ? fmtMoney($this->price * (int)$RebateCategory->rebate * 0.01) : $this->price * (int)$RebateCategory->rebate * 0.01;
                    }
                    else{
                        $money = 0;
                    }
                    if($level_id_key){
                        $ret[$RebateCategory->level_id] = [
                            'level_id'=>$RebateCategory->level_id,
                            'level_name'=>$levels[$RebateCategory->level_id],
                            'rebate'=>$money* $settings['rebate_1'] * 0.01
                        ]; 
                    }
                    else{
                        $ret[] = [
                            'level_id'=>$RebateCategory->level_id,
                            'level_name'=>$levels[$RebateCategory->level_id],
                            'rebate'=>$money* $settings['rebate_1'] * 0.01
                        ]; 
                    }
                }
            }
        }

        return $ret;
    }

    public function getFmtRebatesAndDiscounts(){
        $discounts = $this->getFmtDiscounts(true);
        $rebates = $this->getFmtRebates(true);

        if($discounts){
            foreach ($discounts as $level_id => $v) {
                $rebates[$level_id]['discount'] = $v;
            }
        }

        $ret = [];

        foreach($rebates as $v){
            $ret[] = $v;
        }

        return $ret;
    }


    /**
     * 软删除
     * @return [type] [description]
     */
    public function remove(){
        $this->remove_flag = 1;
        $this->sn = $this->sn.'-del'.date('YmdHis');
        $ret = $this->save();
        if($ret){
            $db = $this->getDi()->get('db');
            /*$category_ids = trim($this->Cate1->Category->merger.$this->Cate1->category_id,',');
            $sql = 'UPDATE i_category SET spu_total=spu_total-1 WHERE category_id in ('.$category_ids.') and spu_total>0';
            $db->execute($sql);*/

            if($this->categories){
                foreach($this->categories as $Cate){
                    /*$category_ids = trim($Cate->Category->merger.$Cate->category_id,',');
                    $sql = 'UPDATE i_category SET spu_total=spu_total-1 WHERE category_id in ('.$category_ids.') and spu_total>0';
                    $db->execute($sql);*/
                    $Cate->Category->updateSpuTotal();
                }

            }

            //清空删除商品的购物车信息
            $db->execute('DELETE FROM i_cart WHERE spu_id=:spu_id',['spu_id'=>$this->spu_id]);
        }
        return $ret;
    }

    public function onSale(){
        $this->status = 1;
        $this->onsale_time = date('Y-m-d H:i:s');
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'onSale',$this->spu_id,$this->spu_name);
        }
        return $ret;
    }

    public function offSale(){
        $this->status = -1;
        $ret = $this->save();
        if($ret){
            $db = $this->getDi()->get('db');
            //清空删除商品的购物车信息
            $db->execute('DELETE FROM i_cart WHERE spu_id=:spu_id',['spu_id'=>$this->spu_id]);
            SAdminLog::add($this->getSource(),'offSale',$this->spu_id,$this->spu_name);
        }
        return $ret;
    }

    //开启抢购
    public function addFlashSale($sale_spu_id,$sale_price,$sale_stock){
        $this->sale_spu_id = $sale_spu_id;
        $this->not_sale_price = $this->price;
        $this->price = $sale_price;
        $this->sale_stock = $sale_stock;
        return $this->save();
    }

    //结束抢购
    public function removeFlashSale(){
        $this->sale_spu_id = 0;
        if($this->not_sale_price>0){
            $this->price = $this->not_sale_price;
            $this->not_sale_price = 0;
        }
        
        $this->sale_stock = 0;
        return $this->save();
    }


    public function getFmtSpecData(){
        $ret = [];
        $specs = json_decode($this->spec_data,JSON_UNESCAPED_UNICODE);
        // var_dump($specs);
        if($specs and is_array($specs)){
            // var_dump($specs);exit;
            foreach($specs as $k=>$v){
                $spec = [
                    'spec_id'=>$k,
                    'spec_name'=>'',
                    'specs'=>[]
                ];

                foreach($v as $i=>$item){
                    $item = explode(':', $item);
                    if(empty($spec['spec_name'])){
                        $spec['spec_name'] = $item[0];
                    }
                    $spec['specs'][] = [
                        'mode'=>dechex($i+1),
                        'value'=>$item[1]
                    ];
 
                }
                $ret[] = $spec;
            }

            
        }
        // var_dump($ret);exit;
        return $ret;
    }

}
