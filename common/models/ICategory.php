<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Exception;
use Common\Libs\Func;

class ICategory extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $category_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $category_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $category_cover;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $parent_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $level;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $seq;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $merger;    

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $recommend_flag;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $recommend_pic;    

    /**
     *
     * @var string
     * @Column(type="string", length=6, nullable=true)
     */
    public $rank;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $spu_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $spec_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $shop_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $shop_id;

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
        $this->setSource("i_category");
        $this->keepSnapshots(true);
        $this->hasMany('category_id', 'Common\Models\IDiscountCategory', 'category_id', ['alias' => 'discounts']);
        $this->hasMany('category_id', 'Common\Models\IRebateCategory', 'category_id', ['alias' => 'rebates']);
        $this->hasMany('category_id', 'Common\Models\IShopCategory', 'category_id', ['alias' => 'ShopCategory']);
        $this->hasMany('category_id', 'Common\Models\ISpuCategory', 'category_id', ['alias' => 'SpuCategory']);
        $this->hasMany('parent_id', 'Common\Models\ISpuCategory', 'category_id', ['alias' => 'sons']);
        $this->belongsTo('parent_id', 'Common\Models\ICategory', 'category_id', ['alias' => 'Parent']);
        $this->hasMany('category_id', 'Common\Models\ISpec', 'category_id', ['alias' => 'cate_specs']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_category';
    }

    static public function getPkCol(){
        return 'category_id';
    }

    static public $attrNames = [
        'category_name'=>'分类名称',
        'category_cover'=>'分类图标',
        'parent_id'=>'上级分类',
        'level'=>'层级',
        'seq'=>'同级分类排序',
        'recommend_flag'=>'首页推荐',
        'recommend_pic'=>'首页推荐图片',
        'spu_total'=>'商品总数',
        'spec_total'=>'规格总数'
    ];

    public function validation() {

        $validator = new Validation();

        if(!$this->import_mode){
            $validator->add(
                'category_name',
                new PresenceOf()
            );
            $validator->add(
                'category_name',
                new Uniqueness()
            );
    
            if(!empty($this->category_name)){
                $validator->add(
                    'category_name',
                    new StringLength([
                        'max' => 30,
                        'min' => 1,
                    ])
                );
            }
    
            $validator->add(
                'level',
               new Between(
                    [
                        "minimum" => 0,
                        "maximum" => 3,
                        "message" => "最多只允许创建三层分类",
                    ]
                )
            );
        }
        
       
        return $this->validate($validator);
        
    }

    public function beforeValidation(){
        $this->parent_id = intval($this->parent_id);
        if($this->parent_id){
            $this->level = $this->Parent->level + 1;
        }
        else{
            $this->level = 1;
        }
    }

    public function beforeCreate(){
        parent::beforeCreate();
        if(!$this->import_mode){
            $this->shop_id = $this->getDi()->get('auth')->getShopId();
        }
        
    }

    public function beforeSave(){

        $db = $this->getDi()->get('db');
        if(empty($this->seq)){
            $max_seq = $db->fetchColumn("SELECT max(seq) as s FROM i_category WHERE parent_id=:parent_id",['parent_id'=>(int)$this->parent_id]);
            $this->seq = intval($max_seq)+1;
        }
        if($this->parent_id){

            if($this->category_id && ($this->category_id==$this->parent_id || strpos($this->Parent->merger,','.$this->category_id.',')!==false)){
                throw new \Exception('您不可以将一个分类设置为自己的下级分类',1);
            }

            $this->rank = rtrim($this->Parent->rank,'0').sprintf("%02s", $this->seq);
            // var_dump($this->rank);exit;
            $this->merger = ','.($this->Parent->merger ? trim($this->Parent->merger,',').',' : '').$this->parent_id.',';
        }
        else{
            $this->merger = '';
            $this->rank = sprintf("%02s", $this->seq);
        }

        $this->rank = sprintf("%0-6s",$this->rank);
        $this->level = (int)$this->level;
        $this->seq = (int)$this->seq;
        $this->recommend_flag = (int)$this->recommend_flag;
    }

    public function afterSave(){

        if($this->hasUpdated('rank')){
            $subCates = self::find([
                "merger like '%,".$this->category_id.",%'",
            ]);

            if($subCates){
                foreach ($subCates as $Cate) {
                    $Cate->save();
                }
            }
        }
    }

    public function beforeDelete(){

        $db = $this->getDi()->get('db');
        $check_son = $db->fetchColumn('SELECT count(1) FROM i_category WHERE parent_id=:category_id',['category_id'=>$this->category_id]);
        if($check_son){
            throw new \Exception("此分类下存在下级分类，不可删除", 1);
            
        }
        $check_spu = $db->fetchColumn('SELECT count(1) FROM i_spu_category as sc join i_goods_spu as s on sc.spu_id=s.spu_id WHERE category_id=:category_id and s.remove_flag=0',['category_id'=>$this->category_id]);
        if($check_spu){
            throw new \Exception("此分类下存在商品，不可删除", 1);
            
        }
    }

    public function getTreeStyleName(){
        $ret = str_repeat('—', $this->level).' <b>'.$this->category_name.'</b>';
        return $ret;
    }

    static public function getTree($parent_id=0,$shop_id=0){
        $tree = [];

        if($parent_id){
            $list = ICategory::find([
                'parent_id=:parent_id: AND shop_id=:shop_id:',
                'bind'=>[
                    'parent_id'=>$parent_id,
                    'shop_id'=>$shop_id,
                ],
                'order'=>'rank asc,level asc'
            ]);
        }
        else{
            $list = ICategory::find([
                'shop_id=:shop_id:',
                'bind'=>[
                    'shop_id'=>$shop_id,
                ],
                'order'=>'rank asc,level asc'
            ]);

        }
        if($list){
            foreach ($list as $Category) {
                if(!empty($Category->merger)){
                    $merger = explode(',', trim($Category->merger,','));

                    self::mountToTree($tree,$merger,$Category);
                }
                else{
                    $tree[$Category->category_id] = [
                        'name'=>$Category->category_name,
                    ];
                }
                
            }
        }
        return $tree;
    }

    static public function mountToTree(&$tree,$merger=[],$Category){

        $key = array_shift($merger);

        if(count($merger)==0){
            if(!isset($tree[$key])){
                $tree[$key] = [
                    'name'=>$Category->Parent->category_name,
                    'cell'=>[]
                ];
            }

            $tree[$key]['cell'][$Category->category_id] = [
                'name'=>$Category->category_name,
            ];
        }
        else{

            self::mountToTree($tree[$key]['cell'],$merger,$Category);
        }

    }

    public function getFmtCategoryCover(){
        $ret = '';
        if($this->category_cover){
            $ret = Func::staticPath($this->category_cover);
        }
        return $ret;
    }

    public function getFmtRecommendPic(){
        $ret = '';
        if($this->recommend_pic){
            $ret = Func::staticPath($this->recommend_pic);
        }
        return $ret;
    }

    public function getFullName($sep='/'){
        $ret = $this->category_name;
        if($this->parent_id){
            $ret = $this->Parent->getFullName($sep).$sep.$ret;
        }
        return $ret;
    }

    public function getRebateByLevel($level_id){
        $db = $this->getDi()->get('db');
        $ret = '';
        $data = $db->fetchOne("SELECT * FROM i_rebate_category WHERE category_id=:category_id AND level_id=:level_id",\Phalcon\Db::FETCH_ASSOC,['category_id'=>$this->category_id,'level_id'=>$level_id]);
        if($data and $data['rebate']){
            if($data['rebate_type']==2){
                $ret = $data['rebate'].'%';
            }
            else{
                $ret = $data['rebate'];
            }
        }

        return $ret;
    }

    public function saveRebates($rebates,$rebate_types){
        $db = $this->getDi()->get('db');
        $db->delete('i_rebate_category','category_id='.$this->category_id);
        foreach($rebates as $k=>$v){
            $db->insertAsDict('i_rebate_category',[
                'level_id'=>$k,
                'category_id'=>$this->category_id,
                'rebate'=>$rebate_types[$k]==1 ? fmtPrice($v) : $v,
                'rebate_type'=>$rebate_types[$k]
            ]);
        }
        
    }

    public function saveDiscounts($discounts,$discount_types){
        $db = $this->getDi()->get('db');
        $db->delete('i_discount_category','category_id='.$this->category_id);
        foreach($discounts as $k=>$v){
            $db->insertAsDict('i_discount_category',[
                'level_id'=>$k,
                'category_id'=>$this->category_id,
                'discount'=>$discount_types[$k]==1 ? fmtPrice($v) : $v,
                'discount_type'=>$discount_types[$k]
            ]);
        }
        
    }

    public function getSpecs(){

        $ret = [];
        if($this->cate_specs){
            foreach ($this->cate_specs as $Spec) {
                $ret[] = [
                    'spec_id'=>$Spec->spec_id,
                    'spec_name'=>$Spec->spec_name,
                    'specs'=>$Spec->getArrSpecs(true)
                ];
            }
        }

        return $ret;
    }

    public function updateSpuTotal(){

        $db = $this->getDi()->get('db');
        $merger = '';
        if($this->parent_id){
            $merger = ','.trim($this->merger.$this->category_id,',').',';
        }
        else{
            $merger = ','.$this->category_id.',';
        }
        $spu_total = $db->fetchColumn("
            SELECT count(distinct sc.spu_id) 
            FROM i_spu_category as sc
            JOIN i_goods_spu as spu on sc.spu_id=spu.spu_id
            WHERE spu.remove_flag=0 AND merger like '".$merger."%'"
        );

        $this->spu_total = $spu_total;
        $this->save();

        if($this->parent_id){
            $this->Parent->updateSpuTotal();
        }

    }

}
