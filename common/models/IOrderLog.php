<?php
namespace Common\Models;

class IOrderLog extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $order_log_id;

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
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $admin_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $action;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $content;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $show_flag;

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
        $this->setSource("i_order_log");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_order_log';
    }

    public function beforeCreate(){
        // $this->create
    }

    static function log($data){
        
        $Log = new self;
        $Log->assign($data);
        $Log->save();
    }

}
