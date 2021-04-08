<?php

namespace Common\Models;

class IDraw extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $draw_id;

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
     * @Column(type="integer", length=4, nullable=true)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $admin_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $act_time;

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
        $this->setSource("i_draw");
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_draw';
    }

    static public function getPkCol(){
        return 'draw_id';
    }

    static public $attrNames = [
        'user_id'=>'提款用户',
        'amount'=>'提款金额',
        'status'=>'状态',
        'act_time'=>'处理时间',
        'create_time'=>'申请时间',
    ];

    public static function getStatusContext($var = null) {
        $data = [
            -1  => '已拒绝',
            1   => '审核中',
            2   =>  '已完成'
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
        $this->status = 1;
    }

    public function beforeSave(){
        $this->user_id = (int)$this->user_id;
        $this->amount = (int)$this->amount;
        $this->status = (int)$this->status;
        $this->admin_id = (int)$this->admin_id;
    }

    /**
     * 审核通过
     * @return [type] [description]
     */
    public function checkPass(){

        $MoneyLog = new IMoneyLog;
        $MoneyLog->assign([
            'amount'=>$this->amount,
            'user_id'=>$this->user_id,
            'type'=>'draw'
        ]);

        if($MoneyLog->save()){
            $this->status = 2;
            if(!$this->save()){

                throw new \Exception($this->getErrorMsg(), 1001);
            }
        }
        $this->status = 2;        
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'checkPass',$this->draw_id,$this->User->phone.'('.$this->User->name.')'.'提现'.fmtMoney($this->amount));
        }
        return $ret;
    }

    /**
     * 审核拒绝
     * @return [type] [description]
     */
    public function checkRefuse(){
        $this->status = -1;
        $ret = $this->save();
        if($ret){
            SAdminLog::add($this->getSource(),'checkRefuse',$this->draw_id,$this->User->phone.'('.$this->User->name.')'.'提现'.fmtMoney($this->amount));
        }
        return $ret;
    }
}
