<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Exception;

class ISort extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $sort_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $sort_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $sort_cover;

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
        $this->setSource("i_sort");
        $this->keepSnapshots(true);
      
        $this->hasMany('parent_id', 'Common\Models\ISort', 'sort_id', ['alias' => 'sons']);
        $this->belongsTo('parent_id', 'Common\Models\ISort', 'sort_id', ['alias' => 'Parent']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_sort';
    }

    static public function getPkCol(){
        return 'sort_id';
    }

    static public $attrNames = [
        'sort_name'=>'分类名称',
        'sort_cover'=>'分类图标',
        'parent_id'=>'上级分类',
        'level'=>'层级',
        'seq'=>'同级分类排序',
        'recommend_flag'=>'首页推荐',
        'recommend_pic'=>'首页推荐图片',
        'spu_total'=>'商品总数',
    ];

    public function validation() {

        $validator = new Validation();
        $validator->add(
            'sort_name',
            new PresenceOf()
        );
        $validator->add(
            'sort_name',
            new Uniqueness()
        );

        if(!empty($this->sort_name)){
            $validator->add(
                'sort_name',
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
        $this->shop_id = $this->getDi()->get('auth')->getShopId();
    }

    public function beforeSave(){

        $db = $this->getDi()->get('db');
        if(empty($this->seq)){
            $max_seq = $db->fetchColumn("SELECT max(seq) as s FROM i_sort WHERE parent_id=:parent_id",['parent_id'=>(int)$this->parent_id]);
            $this->seq = intval($max_seq)+1;
        }
        if($this->parent_id){
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
                "merger like '%,".$this->sort_id.",%'",
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
        $check_son = $db->fetchColumn('SELECT count(1) FROM i_sort WHERE parent_id=:sort_id',['sort_id'=>$this->sort_id]);
        if($check_son){
            throw new \Exception("此分类下存在下级分类，不可删除", 1);
            
        }
        $check_spu = $db->fetchColumn('SELECT count(1) FROM i_spu_sort WHERE sort_id=:sort_id',['sort_id'=>$this->sort_id]);
        if($check_spu){
            throw new \Exception("此分类下存在商品，不可删除", 1);
            
        }
    }

    public function getTreeStyleName(){
        $ret = str_repeat('—', $this->level).' <b>'.$this->sort_name.'</b>';
        return $ret;
    }

    static public function getTree($parent_id=0){
        $tree = [];

        if($parent_id){
            $list = ISort::find([
                'parent_id=:parent_id:',
                'bind'=>[
                    'parent_id'=>$parent_id,
                ],
                'order'=>'rank asc,level asc'
            ]);
        }
        else{
            $list = ISort::find([
                'bind'=>[
                    'shop_id'=>$shop_id,
                ],
                'order'=>'rank asc,level asc'
            ]);

        }
        // var_dump($list);
        if($list){
            foreach ($list as $Sort) {
                // var_dump($Sort->sort_name,$tree);
                if(!empty($Sort->merger)){
                    $merger = explode(',', trim($Sort->merger,','));
                    // var_dump($Sort->sort_id,$merger);
                    // exit;
                    
                    self::mountToTree($tree,$merger,$Sort);
                }
                else{
                    $tree[$Sort->sort_id] = [
                        'name'=>$Sort->sort_name,
                    ];
                }
                
            }
        }
        return $tree;
    }

    static public function mountToTree(&$tree,$merger=[],$Sort){

        $key = array_shift($merger);

        if(count($merger)==0){
            if(!isset($tree[$key])){
                $tree[$key] = [
                    'name'=>$Sort->Parent->sort_name,
                    'cell'=>[]
                ];
                // var_dump('yes');exit;
            }

            $tree[$key]['cell'][$Sort->sort_id] = [
                'name'=>$Sort->sort_name,
            ];
        }
        else{
            /*var_dump($key);
            var_dump($tree);
            var_dump($tree[$key]);exit;*/
            self::mountToTree($tree[$key]['cell'],$merger,$Sort);
        }

    }

    public function getFullName($sep='/'){
        $ret = $this->sort_name;
        if($this->parent_id){
            $ret = $this->Parent->getFullName($sep).$sep.$ret;
        }
        return $ret;
    }
}
