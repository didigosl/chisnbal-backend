<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class ICart extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $cart_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $spu_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sku_id;

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
    public $shop_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $rebate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $discount;


    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $num;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $amount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total_rebate;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_cart");
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
        $this->belongsTo('spu_id', 'Common\Models\IGoodsSpu', 'spu_id', ['alias' => 'Spu']);
        $this->belongsTo('sku_id', 'Common\Models\IGoodsSku', 'sku_id', ['alias' => 'Sku']);
        $this->belongsTo('sale_spu_id','Common\Models\IFlashSaleSpu','id',['alias' => 'SaleSpu']);
        $this->belongsTo('shop_id','Common\Models\IShop','shop_id',['alias' => 'Shop']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_cart';
    }

    static public function getPkCol(){
        return 'cart_id';
    }

    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('user_id',$cols)){
            $validator->add(
                'user_id',
                new PresenceOf()
            );
        }
        
        /*if($cols_length==0 OR in_array('spu_id',$cols)){
            $validator->add(
                'spu_id',
                new PresenceOf()
            );
        }*/
        
        if($cols_length==0 OR in_array('sku_id',$cols)){
             $validator->add(
                'sku_id',
                new PresenceOf()
            );
        }

        if($cols_length==0 OR in_array('num',$cols)){
             $validator->add(
                'num',
                new PresenceOf()
            );
        }

        return $validator;
    }

    public function beforeCreate(){
        parent::beforeCreate();
        $this->spu_id = $this->Sku->spu_id;
        $this->shop_id = $this->Sku->Spu->shop_id;
    }

    public function beforeSave(){
        $this->num = intval($this->num);
        // $this->price = (int)$this->Sku->price;

        $rebates = $this->Spu->getFmtRebates(true,false);
        $this->rebate = intval($rebates[$this->User->level_id] ? $rebates[$this->User->level_id]['rebate'] : 0);

        $discounts = $this->Spu->getFmtDiscounts(true);
        $this->discount = intval($discounts[$this->User->level_id] ? $discounts[$this->User->level_id] : 0);

        $this->amount = intval(($this->price - $this->discount) * $this->num);
        $this->amount = intval($this->amount ? $this->amount : 0);
        // var_dump($this->rebate,$this->num);exit;
        $this->total_rebate = intval($this->rebate * $this->num);

        if(!empty($this->Sku->Spu->sale_spu_id)){
            if( $this->Sku->Spu->SaleSpu->per_limit>0 AND $this->num>$this->Sku->Spu->SaleSpu->per_limit ){
                throw new \Exception("商品数量超出限制，抢购商品每人限购".$this->Sku->Spu->SaleSpu->per_limit.'件', 2002);
                
            }
        }

        if( $this->Sku->Spu->min_to_buy>0 AND $this->num<$this->Sku->Spu->min_to_buy ){
            throw new \Exception("商品最少必须购买".$this->Sku->Spu->min_to_buy."件", 2002);
            
        }
    }

    //加入购物车
    //$merge_with_old 是否和购物车中原有的同款商品合并
    static public function add($user_id,$sku_id,$num=1,$merge_with_old=true){

        $User = IUser::findFirst($user_id);

        $Sku = IGoodsSku::findFirst($sku_id);
        if(!$Sku){
            throw new \Exception("商品型号不存在", 2002);
            
        }

        if($Sku->Spu->status<0){
            throw new \Exception("商品已经下架了", 2002);
            
        }

        if($Sku->Spu->remove_flag>0){
            throw new \Exception("商品已经删除了", 2002);
            
        }

        if($Sku->stock-$num<0){
            
        }

        // var_dump($Sku->Spu->sale_spu_id,$Sku->Spu->SaleSpu->sale_price);exit;
        if($Sku->Spu->sale_spu_id){
            $price = $Sku->Spu->SaleSpu->sale_price;

            if($Sku->Spu->SaleSpu->FlashSale->status==1){
                throw new \Exception("此商品的抢购尚未开始", 2002);
                
            }
            if($Sku->Spu->SaleSpu->FlashSale->status==3){
                throw new \Exception("此商品的抢购已经结束了", 2002);
                
            }
        }
        else{
            $price = $Sku->price;

            //执行会员价，限无sku的商品
            if(conf('enable_vip_price') && $User->level_id>1){
                
                $price_var = 'price'.$User->level_id;
                if($Sku->Spu->$price_var>0){
                    $price = $Sku->Spu->$price_var;
                }
                
            }
        }

        $Cart = self::findFirst([
            'user_id=:user_id: AND sku_id=:sku_id: AND sale_spu_id=:sale_spu_id:',
            'bind'=>[
                'user_id'=>$user_id,
                'sku_id'=>$sku_id,
                'sale_spu_id'=>(int)$Sku->Spu->sale_spu_id,
            ]
        ]);

        if($Cart){
            
            if($merge_with_old){
                $Cart->num = $Cart->num + $num;
            }
            else{

                $Cart->num = $num;
            }

        } 

        if(!$Cart){            

            $Cart = new self;
            $Cart->assign([
                'user_id'=>$user_id,
                'sku_id'=>$sku_id,
                'num'=>$num,
                'sale_spu_id'=>(int)$Sku->Spu->sale_spu_id,
                'price'=>$price,
            ]);

            $Cart->spu_id = $Cart->Sku->spu_id;
        }

		if($Sku->stock - $Cart->num < 0){
			throw new \Exception('剩余库存不足', 303003);
		}

        if(!$Cart->save()){
            throw new \Exception($Cart->getErrorMsg(), 2002);
            
        }

        return $Cart;
    }


    //更新购物车信息
    static public function renew($user_id,$list=[]){

        $User = IUser::findFirst($user_id);
        
        $list_ids = [];
        if(is_array($list)){
            foreach ($list as $v) {
                $list_ids[] = $v['cart_id'];
            }
        }

        $tmp = self::find([
            'user_id=:user_id:',
            'bind'=>[
                'user_id'=>$user_id,
            ]
        ]);

        $carts = [];

        if($tmp){
            foreach ($tmp as $Cart) {
                $carts[$Cart->cart_id] = $Cart;
            }
        }

        unset($tmp);

        foreach ($list as $v) {
            if($carts[$v['cart_id']]){
                $carts[$v['cart_id']]->num = $v['num'];

                if($carts[$v['cart_id']]->Sku->stock - $carts[$v['cart_id']]->num < 0){
                    throw new \Exception('剩余库存不足', 303003);
                }

                if(!$carts[$v['cart_id']]->save()){
                    throw new \Exception($carts[$v['cart_id']]->getErrorMsg(), 2001);
                    
                }
            }
        }

        foreach($carts as $Cart){
            if(!in_array($Cart->cart_id,$list_ids)){
                if(!$Cart->delete()){
                    throw new \Exception("移除购物车商品失败", 1002);
                    
                }
            }
        }

        return true;
    }

    //删除一条购物车商品
    public function remove($id){
        $Cart = self::findFirst($id);
        if($Cart){
            if(!$Cart->delete()){
                throw new \Exception("移除购物车商品失败", 1);
            }
        }

        return true;
    }

    //清空购物车
    static public function clean($user_id){

        $carts = self::find([
            'user_id=:user_id:',
            'bind'=>[
                'user_id'=>$user_id,
            ]
        ]);

        if($carts){
            foreach ($carts as $Cart) {
                if(!$Cart->delete()){
                    throw new \Exception("移除购物车商品失败", 1);
                    
                }
            }
        }

        return true;
    }

}
