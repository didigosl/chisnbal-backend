<?php

namespace Common\Models;

class IMoneyLog extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $money_log_id;

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
    public $amount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $money;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $order_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $remark;

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
    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_money_log");
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_money_log';
    }

    public function beforeSave(){
        $this->user_id = (int)$this->user_id;
        $this->amount = (int)$this->amount;
        $this->money = (int)$this->money;
    }

    public static function getTypeContext($var = null) {
        $data = [
            'draw'  => '提现',
            'rebate'  => '返利',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function afterSave(){
        if($this->getOperationMade()==self::OP_CREATE){
            // $db = $this->getDI()->get('db');
            // $this->User->refresh();
            // $update_sql = '';
            if($this->type =='rebate'){
                $amount = (int)$this->amount;
                // $update_sql = ' , total_rebate=total_rebate+ '.$amount;
                $this->User->total_rebate = $this->User->total_rebate + $amount;
                // $this->User->save();
            }
            elseif($this->type == 'draw'){
                $amount = -1*$this->amount;
            }

            // $sql = "UPDATE i_user SET money=money+".$amount.' '.$update_sql." WHERE user_id=:user_id";
            // echo $sql;exit;
            // $db->execute("UPDATE i_user SET money=money+".$amount.' '.$update_sql." WHERE user_id=:user_id",['user_id'=>$this->user_id]);
            $this->User->money = $this->User->money + $amount;

            if($this->User->money<0){
                throw new \Exception("账户余额不可以小于0", 1);
                
            }
            
            if(!$this->User->save()){
                throw new \Exception($this->User->getErrorMsg(), 1);
                
            }

            $this->money = $this->User->money;
            if(!$this->save()){
                throw new \Exception($this->getErrorMsg(), 1);
            }
        }
        
    }

}
