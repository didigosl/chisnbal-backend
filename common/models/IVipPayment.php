<?php
namespace Common\Models;

use Common\Libs\Func;

class IVipPayment extends Model
{

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $vip_payment_id;

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
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $refer_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $level_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $transaction_id;

    public $peyment_method;
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_vip_payment");
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
        $this->belongsTo('level_id', 'Common\Models\IUserLevel', 'level_id', ['alias' => 'UserLevel']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_vip_payment';        

    }

    public function afterCreate(){

        if($this->refer_user_id){

            if(!$this->User->setParent($this->refer_user_id)){
                throw new \Exception("Error:".$this->User->getErrorMsg(), 1);
                
            }
        }
    }

    public function beforeSave(){
        $this->amount = (int)$this->amount;
        $this->user_id = (int)$this->user_id; 
        $this->refer_user_id = (int)$this->refer_user_id; 
        $this->level_id = (int)$this->level_id; 
    }

    public function paid(){

        
        if($this->User->level_id==$this->level_id){
            return true;
        }

        $this->User->level_id = $this->level_id;
        if($this->payment_method=='money'){
            $this->User->money = $this->User->money - $this->amount;
            
        }

        if(!$this->User->save()){
            throw new \Exception('更新账户信息失败');
        }
        else{
            $this->status =2 ;
            
            return $this->save();
        }
    }

}
