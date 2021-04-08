<?php

namespace Common\Models;

use \Common\Libs\Pinyin;

class IArea extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $area_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $parent_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $level;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=false)
     */
    public $merger;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $seq;

    public $first_letter;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_area");
        $this->belongsTo('parent_id', 'Common\Models\IArea', 'area_id', ['alias' => 'Parent']);
        $this->hasMany('area_id', 'Common\Models\IArea', 'parent_id', ['alias' => 'sons']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_area';
    }

    static public function getPkCol(){
        return 'area_id';
    }

    static public $attrNames = [
        'name'=>'名称',
        'parent_id'=>'上级地区',

    ];

    public function beforeCreate(){
        parent::beforeCreate();        
        $this->status = 1;
    }

    public function beforeSave(){

        if(!$this->parent_id){
            $this->level = 1;
        }
        else{
            $this->level = $this->Parent->level+1;
        }

        $this->parent_id = (int)$this->parent_id;
        $this->status = (int)$this->status;
        $this->level = (int)$this->level;
        $this->seq = (int)$this->seq;
    }

    public function beforeDelete(){
        $db = $this->getDi()->get('db');
        $sons_total = $db->fetchColumn('SELECT count(1) FROM i_area WHERE parent_id=:parent_id',['parent_id'=>$this->area_id]);

        if($sons_total>0){
            throw new \Exception('因存在下级地区，不可删除');
        }

        $fee_total = $db->fetchColumn('SELECT count(1) FROM i_delivery_fee_measure WHERE area_id=:area_id',['area_id'=>$this->area_id]);
        if($fee_total>0){
            throw new \Exception('因存在相关的运费设置项目，不可删除');
        }
    }

    public function getParentNames(&$ret=[]){
        array_unshift($ret,$this->name);
        if($this->parent_id){
            $this->Parent->getParentNames($ret);
        }
        $ret = array_reverse($ret);
        return $ret;
    }

    public function getFullName($sep='/'){
        $ret = $this->name;
        if($this->parent_id){
            $ret = $this->Parent->getFullName($sep).$sep.$ret;
        }
        return $ret;
    }

    public function getParents(&$ret=[]){
        // static $ret = [];
        $ret[] = $this->area_id;
        if($this->parent_id){
            $this->Parent->getParents($ret);
        }
        return $ret;
    }

    public function getChildren(){
        $ret = [];
        if($this->sons){
            // exit('sons');
            foreach($this->sons as $Son){
                // var_dump($Son->toArray());exit;
                $ret[$Son->area_id] = [
                    'name'=>$Son->name,
                ];

                if($Son->sons){
                    $children = $Son->getChildren();
                    if(!empty($children)){
                        $ret[$Son->area_id]['cell'] = $children;
                    }
                    
                }
            }
        }
        // var_dump($ret);
        return $ret;
    }

    public function getFirstLetter()
    {
        $pinyin = Pinyin::getPinyin($this->name);
        echo $pinyin.PHP_EOL;
        $ret = strtoupper(substr(Pinyin::getPinyin($this->name),0,1));
    
        return $ret;
    }

    static public function getTree($parent_id=0){
        $tree = [];

        if($parent_id){
            $list = IArea::find([
                'parent_id=:parent_id: AND status=1',
                // 'parent_id=:parent_id: ',
                'bind'=>[
                    'parent_id'=>$parent_id
                ],
                'order'=>'level asc'
            ]);
        }
        else{
            $list = IArea::find([
                'status=1 AND level=1',
                'order'=>'level asc'
            ]);

        }
 
        if($list){
            foreach ($list as $Area) {
                $tree[$Area->area_id] = [
                    'name'=>$Area->name,
                ];
                if($Area->sons){
                    $tree[$Area->area_id]['cell'] = $Area->getChildren();
                }

                
            }
        }
        // var_dump($tree);exit;
        return $tree;
    }

    static public function mountToTree(&$tree,$merger=[],$Area){

        $key = array_shift($merger);

        if(count($merger)==0){
            if(!isset($tree[$key])){
                $tree[$key] = [
                    'name'=>$Area->Parent->name,
                    'cell'=>[]
                ];
            }

            $tree[$key]['cell'][$Area->area_id] = [
                'name'=>$Area->name,
            ];
        }
        else{
            self::mountToTree($tree[$key]['cell'],$merger,$Area);
        }

    }

}
