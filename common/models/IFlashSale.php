<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class IFlashSale extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $sale_id;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $sale_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $start_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $end_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $finish_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $shop_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $status;

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
        
        $this->setSource("i_flash_sale");
        $this->hasMany('sale_id', 'Common\Models\IFlashSaleSpu', 'sale_id', ['alias' => 'spus']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_flash_sale';
    }

    static public function getPkCol(){
        return 'sale_id';
    }
    
    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('sale_name',$cols)){
            $validator->add(
                'sale_name',
                new PresenceOf()
            );
        }
        
        if($cols_length==0 OR in_array('start_time',$cols)){
            $validator->add(
                'start_time',
                new PresenceOf()
            );
        }
        
        if($cols_length==0 OR in_array('end_time',$cols)){
             $validator->add(
                'end_time',
                new PresenceOf()
            );
        }                

        return $validator;
    }


    static public $attrNames = [
        'sale_name'=>'限时抢购名称',
        'start_time'=>'抢购开始时间',
        'end_time'=>'抢购终止时间',
        'finish_time'=>'抢购结束时间'
    ];

    public function beforeCreate(){

        parent::beforeCreate();
        $this->status = 1;
        $this->shop_id = $this->getDi()->get('auth')->getShopId();
    }

    public function beforeSave(){
        $this->status = (int)$this->status;
    }

    public static function getStatusContext($var = null) {
        $data = [
            1  => '未开始',
            2   => '进行中',
            3   =>  '已结束'
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }


    public function start(){
        $this->status = 2;
        $ret = $this->save();
        if($ret){
            if($this->spus){
                foreach($this->spus as $SaleSpu){
                    $SaleSpu->Spu->addFlashSale($SaleSpu->id,$SaleSpu->sale_price,$SaleSpu->sale_stock);
                }
            }
        }
        return $ret;
    }

    public function finish(){
        $this->status = 3;
        $this->finish_time = date('Y-m-d H:i:s');
        $ret = $this->save();
        if($ret){
            if($this->spus){
                foreach($this->spus as $SaleSpu){
                    $SaleSpu->Spu->removeFlashSale();
                }
            }
        }
        return $ret;
    }

    public function updateSpus($spus){

        if(!is_array($spus)){
            throw new \Exception("抢购商品数据错误", 2002);            
        }
            

        $spus_ids = [];
        foreach($spus as $spu){
            $spus_ids[] = $spu['spu_id'];
            $data = [
                'spu_id'=>$spu['spu_id'],
                'sale_price'=>$spu['sale_price'],
                'sale_stock'=>$spu['sale_stock'],
                'per_limit'=>(int)$spu['per_limit'],
            ];

            $SaleSpu = IFlashSaleSpu::findFirst([
                'sale_id=:sale_id: AND spu_id=:spu_id:',
                'bind'=>[
                    'spu_id'=>$data['spu_id'],
                    'sale_id'=>$this->sale_id
                ]
            ]);

            if(!$SaleSpu){
                $SaleSpu = new IFlashSaleSpu;
                $data['sale_id'] = $this->sale_id;
            }

            $SaleSpu->assign($data);

            if(!$SaleSpu->save()){
                throw new \Exception($SaleSpu->getErrorMsg(), 2002);
                
            }
        }

        if($this->spus){
            foreach ($this->spus as $Item) {
                if(!in_array($Item->spu_id,$spus_ids)){
                    $Item->delete();
                }
            }
        }
    }

}
