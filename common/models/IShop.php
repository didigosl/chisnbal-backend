<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;
use Common\Libs\Func;

class IShop extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $shop_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $shop_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $intro;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $contact_man;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $tel;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $email;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $address;

    public $lan;

    public $lon;

    public $postcode;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $logo;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $bg;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $delivery_free_limit;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $goods_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $sale_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $request_time;

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

    public $import_mode = false;    //是否在执行数据导入

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_shop");
        $this->belongsTo('sort_id','Common\Models\ISort','sort_id',['alias' => 'Sort']);
        $this->hasMany('shop_id', 'Common\Models\IShopCheck', 'shop_id', ['alias' => 'Check']);
        $this->hasMany('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_shop';
    }

    static public function getPkCol(){
        return 'shop_id';
    }


    public function validation() {

        $conf = $this->getDi()->get('conf');
        $validator = new Validation();

        if(!$this->import_mode){
            if(!$this->shop_id){

                $validator->add(
                    'contact_man',
                    new PresenceOf()
                );
    
                $validator->add(
                    'tel',
                    new PresenceOf()
                );
    
               
            }
    
            if($conf['enable_multi_shop']){
                $validator->add(
                    'sort_id',
                    new PresenceOf()
                );
        
            }
            
            $validator->add(
                'shop_name',
                new PresenceOf()
            );
            $validator->add(
                'shop_name',
                new Uniqueness([
                    'filed'=>'shop_name',
                ])
            );
    
            if(!empty($this->shop_name)){
                $validator->add(
                    'shop_name',
                    new StringLength([
                        'max' => 20,
                        'min' => 3,
                    ])
                );
            }
    
            $validator->add(
                'intro',
                new PresenceOf()
            );
            
        }
        
       
        return $this->validate($validator);
        
    }

    static public $attrNames = [
        'shop_id'=>'ID',
        'shop_name'=>'店铺名称',
        'sort_id'=>'主营分类',
        'intro'=>'店铺介绍',
        'contact_man'=>'联系人',
        'tel'=>'联系电话',
        'address'=>'店铺地址',
        'logo'=>'店铺logo',
        'bg'=>'背景图片',
        'spu_total'=>'商品总量',
        'sale_total'=>'销售总量',
        'user_id'=>'创建人',
        'status'=>'状态',
        'create_time'=>'申请时间',
        'check_time'=>'审核时间',
        'lon'=>'经度',
        'lan'=>'维度',
        'postcode'=>'配送范围邮编',
    ];

    public function beforeSave(){
        if(isset($this->sort_id)){
            $this->sort_id = (int)$this->sort_id;
        }
        
    }

    public static function getStatusContext($var = null) {
        $data = [
            -2=>'被拒',
            -1  => '冻结',
            1   => '待审',
            2   => '运营中',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function getFmtLogo(){
        $ret = '';
        if($this->logo){
            $ret = Func::staticPath($this->logo);
        }
        return $ret;
    }

    public function getFmtBg(){
        $ret = '';
        if($this->bg){
            $ret = Func::staticPath($this->bg);
        }
        return $ret;
    }

    /**
     * 审核通过
     * @return [type] [description]
     */
    public function checkPass(){

        $user = $this->getDi()->get('user');

        $ShopCheck = new IShopCheck;
        $ShopCheck->assign([
            'shop_id'=>$this->shop_id,
            'admin_id'=>$user->id,
            'check_result'=>2,
        ]);

        if($ShopCheck->save()){
            $this->status = 2;
            $this->check_time = date('Y-m-d H:i:s');
            if(!$this->save()){
                                
                throw new \Exception($this->getErrorMsg(), 1001);
            }
            else{

                $Admin = new SAdmin;
                $Admin->assgin([

                    'username'=>$this->User->phone,
                    'password'=>'456123',
                    'acl_role_id'=>3,
                    'flag'=>1,
                    'shop_id'=>$this->shop_id,
                ]);
                if(!$Admin->save()){
                    throw  new \Exception('店铺帐号创建失败'.$Admin->getErrorMsg());
                }

                SAdminLog::add($this->getSource(),'checkPass',$this->shop_id,'店铺('.$this->shop_name.')'.'申请审核通过');
                return true;
            }
        }
        else{
            throw new \Exception($ShopCheck->getErrorMsg(), 1);
            
        }
        
        return $false;
    }

    /**
     * 审核拒绝
     * @return [type] [description]
     */
    public function checkRefuse(){

        $user = $this->getDi()->get('user');

        $ShopCheck = new IShopCheck;
        $ShopCheck->assign([
            'shop_id'=>$this->shop_id,
            'admin_id'=>$user->id,
            'check_result'=>-2,
            'reason'=>'',
        ]);

        $this->status = -2;

        if($ShopCheck->save()){
            $ret = $this->save();
            if($ret){
                SAdminLog::add($this->getSource(),'checkRefuse',$this->shop_id,'店铺('.$this->shop_name.')'.'申请被拒绝');
            }
            else{
                throw new \Exception($this->getErrorMsg(), 1);
                
            }
        }
        else{
            throw new \Exception($this->getErrorMsg(), 1);
            
        }
        
        return $ret;
    }

}
