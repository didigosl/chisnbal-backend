<?php

namespace Common\Models;

class SAdminLog extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $action;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $action_name;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $table;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $table_pk;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $remark;

    public $values;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $admin_id;

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
        $this->setSource("s_admin_log");
        $this->belongsTo('admin_id', 'Common\Models\SAdmin', 'id', ['alias' => 'Admin']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 's_admin_log';
    }

    static public $attrNames = [
        'admin_id'=>'管理员',
        'action'=>'操作类型',
        'remark'=>'操作内容',
        'create_time'=>'操作时间',
        'ip'=>'IP'
    ];

    public function beforeCreate(){
        parent::beforeCreate();
        $auth = $this->getDi()->get('auth')->getIdentity();
        $this->admin_id = $auth['id'];
        $this->ip = $this->getDi()->get('request')->getClientAddress();
        $this->shop_id = $this->getDi()->get('auth')->getShopId();
    }

    public function beforeSave(){
        $this->table_pk = (int)$this->table_pk; 
        $this->admin_id = (int)$this->admin_id; 
    }

    static public function add($table,$action,$pk,$remark,$values=''){

        $names = self::getTableActionName($table,$action);
        $Log = new self;
        $Log->assign([
            'table'=>$table,
            'action'=>$action,
            'action_name'=>'['.$names['table_name'].']['.$names['action_name'].']',
            'table_pk'=>(int)$pk,
            'remark'=>$names['action_name'].'【'.$names['table_name'].($remark ? '：'.$remark : '').'】',
            'values'=>$values,
        ]);

        if(!$Log->save()){
            throw new \Exception($Log->getErrorMsg(), 1001);
            
        }

    }

    static public $common_actions = [    
        'create'=>'创建',
        'update'=>'修改',
        'delete'=>'删除'
    ];
    static public $names = [
            's_admin'=>[
                'name'=>'管理员',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>'',
                    'changePassword'=>'修改密码'
                ]
            ],
            's_acl_role'=>[
                'name'=>'管理员角色',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'setting'=>'设置权限'
                ]
            ],
            's_setting'=>[
                'name'=>'系统参数',
                'actions'=>[
                    'setting'=>'更新'
                ]
            ],
            'i_ad'=>[
                'name'=>'广告',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>''
                ]
            ],
            'i_article'=>[
                'name'=>'文章',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>''
                ]
            ],
            'i_category'=>[
                'name'=>'商品分类',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>''
                ]
            ],
            'i_category'=>[
                'name'=>'商品分类',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>''
                ]
            ],
            'i_coupon'=>[
                'name'=>'代金券',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>''
                ]
            ],
            'i_delivery_fee'=>[
                'name'=>'运费',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>''
                ]
            ],
            'i_draw'=>[
                'name'=>'提现申请',
                'actions'=>[
                    'checkPass'=>'通过',
                    'checkRefuse'=>'驳回',
                ]
            ],
            'i_flash_sale'=>[
                'name'=>'限时抢购',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>'',
                    'finish'=>'结束'
                ]
            ],
            'i_goods_spu'=>[
                'name'=>'商品',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>'',
                    'onSale'=>'上架',
                    'offSale'=>'下架',
                ]
            ],
            'i_label'=>[
                'name'=>'商品标签',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>'',
                ]
            ],
            'i_order'=>[
                'name'=>'订单',
                'actions'=>[
                    'updateDelivery'=>'更新收发货信息',
                    'close'=>'关闭',
                    'refound'=>'确认退款',
                    'paid'=>'确认收款',
                    'delivery'=>'设为发货',
                    'adjust'=>'调整价格'
                ]
            ],
            'i_order_remark'=>[
                'name'=>'订单备注',
                'actions'=>[
                    'create'=>'',
                    'delete'=>''
                ]
            ],
            'i_spec'=>[
                'name'=>'商品规格',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>''
                ]
            ],
            'i_user'=>[
                'name'=>'会员',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>'',
                    'resetpsw'=>'重置密码',
                    'freeze'=>'冻结',
                    'unfreeze'=>'解冻'
                ]
            ],
            'i_user_level'=>[
                'name'=>'会员等级',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'delete'=>'',
                ]
            ],
            'i_excel'=>[
                'name'=>'Excel导入',
                'actions'=>[
                    'create'=>'上传导入',
                ]
            ],
            'i_shop'=>[
                'name'=>'店铺',
                'actions'=>[
                    'create'=>'',
                    'update'=>'',
                    'checkPass'=>'审核通过',
                    'refuse'=>'驳回申请'
                ]
            ]
        ];

    static public function getTableName($table){
        return self::$names[$table]['name'];
    } 

    static public function getTableActionName($table,$action){

        $ret = [
            'table_name'=>self::$names[$table]['name'],
            'action_name'=>self::$common_actions[$action] ? self::$common_actions[$action] : self::$names[$table]['actions'][$action],
        ];
        return $ret;
    }

}
