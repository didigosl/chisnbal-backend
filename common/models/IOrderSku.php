<?php

namespace Common\Models;

class IOrderSku extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $order_sku_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $order_id;

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
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $spec_info;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $sku_sn;

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
    public $price;

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
    public $discount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $rebate;

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
     * @Column(type="string", nullable=false)
     */
    public $update_time;

    public $import_mode = false;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_order_sku");
        $this->belongsTo('order_id', 'Common\Models\IOrder', 'order_id', ['alias' => 'Order']);
        $this->belongsTo('sku_id', 'Common\Models\IGoodsSku', 'sku_id', ['alias' => 'Sku']);
        $this->belongsTo('spu_id', 'Common\Models\IGoodsSpu', 'spu_id', ['alias' => 'Spu']);
        $this->belongsTo('sale_spu_id','Common\Models\IFlashSaleSpu','id',['alias' => 'SaleSpu']);
        $this->belongsTo('distribution_type_id','Common\Models\IDistributionType','distribution_type_id',['alias' => 'DistributionType']);
    }

    static public $attrNames = [
        'num'=>'数量',
        'price'=>'价格',
        'spec_info'=>'商品规格',
        'sku_sn'=>'单品货号'
    ];

    public function beforeSave(){
        $this->order_id = (int)$this->order_id;
        $this->spu_id = (int)$this->spu_id;
        $this->sku_id = (int)$this->sku_id;
        $this->num = (int)$this->num;
        $this->price = (int)$this->price;
        $this->discount = (int)$this->discount;
        $this->rebate = (int)$this->rebate;
        $this->amount = (int)$this->amount;


        if($this->sale_spu_id){

            if($this->SaleSpu->sale_stock<=0){
                throw new \Exception('抢购商品缺货', 2002);
                
            }

            if($this->SaleSpu->sale_stock - $this->num < 0){
                throw new \Exception('剩余抢购库存不足', 303003);
            }
        }
        else{
            if($this->Sku->stock<=0){
                throw new \Exception('商品缺货', 2002);
                
            }

            if($this->Sku->stock - $this->num < 0){
                throw new \Exception('剩余库存不足'.$this->Sku->sku_id .'/'.$this->num, 303003);
            }
        }
        
    }

    public function afterCreate(){
        $this->Sku->stock = $this->Sku->stock - $this->num;
        if($this->Sku->stock<0){
            throw new \Exception("剩余库存不足".$this->Sku->stock .'/'.$this->num, 2002);
        }
        if($this->Spu->has_default_sku){
            $this->Spu->stock = $this->Sku->stock;
            $this->Spu->save();
        }

        if($this->Sku->save()){
            //根据库存更新商品上下架状态
            $stock_total = db()->fetchColumn("SELECT sum(stock) as s FROM i_goods_sku WHERE spu_id=:spu_id AND status=1",['spu_id'=>$this->spu_id]);
            if($stock_total<=0){
                db()->updateAsDict('i_goods_spu',['status'=>-1],'spu_id='.$this->spu_id);
            }
        }
    }

    public function afterSave(){
        if($this->getOperationMade()==self::OP_UPDATE){
            if($this->hasUpdated('stock') && $this->spec_info=='default'){
                $this->Spu->stock = $this->stock;
                $this->Spu->save();
            }
        }
    }

    public function getFmtSpecInfo(){
        $ret = null;
        if($this->spec_info!=='default'){
            $ret = explode(',',$this->spec_info);
            foreach($ret as $k=>$v){
                $data = explode(':',$v);
                $ret[$k] = [
                    'key'=>$data[0],
                    'val'=>$data[1]
                ];
            }
        }

        return $ret;
    }
}
