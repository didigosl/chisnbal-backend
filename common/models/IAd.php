<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Common\Libs\Func;

class IAd extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $ad_id;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $ad_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $position_type;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $ad_pos_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $category_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sort_id;

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
     * @Column(type="string", length=255, nullable=true)
     */
    public $img;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $link_type;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=true)
     */
    public $link_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $link_url;


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
     * @Column(type="string", nullable=true)
     */
    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_ad");
        $this->belongsTo('category_id', 'Common\Models\ICategory', 'category_id', ['alias' => 'Category']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_ad';
    }

    static public function getPkCol(){
        return 'ad_id';
    }

    static public $attrNames = [
        'ad_name'=>'名称',
        'position_type'=>'位置',
        'category_id'=>'商品分类',
        'sort_id'=>'平台分类',
        'start_time'=>'开始时间',
        'end_time'=>'结束时间',
        'img'=>'图片',
        'status'=>'状态',        
        'create_time'=>'创建时间',
        'link_url'=>'链接地址'
    ];

    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('ad_name',$cols)){
            $validator->add(
                'ad_name',
                new PresenceOf()
            );
        }
        
        if($cols_length==0 OR in_array('position_type',$cols)){
             $validator->add(
                'position_type',
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

    public function beforeCreate(){
        parent::beforeCreate();
        $this->shop_id = $this->getDi()->get('auth')->getShopId();
        // $this->status = 1;
    }

    public function beforeSave(){
        $this->category_id = (int)$this->category_id;
        $this->sort_id = (int)$this->sort_id;
        $this->link_id = $this->link_id;
        
        $now = time();
        $start_time = strtotime($this->start_time);
        $end_time = strtotime($this->end_time);

        if($now>$end_time){
            $this->status = 2; 
        }
        elseif($now>$start_time and $now<$end_time){
            $this->status = 3; 
        }
        else{
            $this->status = 1;
        }
    }

    public static function getStatusContext($var = null) {
        $data = [
            1  => '未开始',
            2  => '已结束',
            3  => '进行中'
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public static function getPositionTypeContext($var = null) {
        $data = [
            'index'  => '首页',
            'category'   => '分类页',
            'pc_index'=>'PC端首页',
            'pc_category'=>'PC端分类页'
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }

    public function getAdPosition(){
        $ret = '';
        $ret = $this->getPositionTypeContext($this->position_type);
        if($this->position_type=='category'){
            $ret .= ':'.($this->category_id ? ($this->Category ? $this->Category->getFullName() : "") : '全部分类');
        }

        return $ret;
    }

    public function getFmtImg(){
        $ret = '';
        if($this->img){
            $ret = Func::staticPath($this->img);
        }
        return $ret;
    }

    public function getLinkSeries($order='onsale_time Desc'){
        $ret = [];
        if($this->link_type=='goodsSeries' && $this->link_id){
            $ret = IGoodsSpu::find([
                'spu_id in ('.$this->link_id.')',
                'order'=>$order
            ]);
        }

        return $ret;
    }

}
