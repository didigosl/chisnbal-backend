<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;

class ISpec extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $spec_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $spec_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $specs;

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
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $total;

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
        
        $this->setSource("i_spec");
        $this->belongsTo('category_id', 'Common\Models\ICategory', 'category_id', ['alias' => 'Category']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_spec';
    }

    static public function getPkCol(){
        return 'spec_id';
    }

    static public $attrNames = [
        'category_id'=>'规格分类',
        'spec_name'=>'规格名称',
        'specs'=>'规格内容'
    ];

    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator); 
        
    }

    static public function validator($cols=[]){
        $validator = new Validation();
        $validator->add(
            'spec_name',
            new PresenceOf()
        );

        $validator->add(
            'spec_name',
            new StringLength([
                'max' => 10,
                'min' => 1,
            ])
        );

        $validator->add(
            ['category_id','spec_name'],
            new Uniqueness()
        );

        $validator->add(
            'specs',
            new PresenceOf()
        );
        return $validator;
    }

    public function beforeSave(){
        $this->specs = trim($this->specs);
        $this->category_id = (int)$this->category_id;
        $this->total = (int)$this->total;
    }

    static public function fmtSpecs($specs){
        $specs = str_replace('，', ',', $specs);
        $specs = preg_replace('/[,]+/', ',', $specs);
        $specs = trim($specs,',');
        return $specs;
    }

    /**
     * 以数组形式返回规格值
     * @param  boolean $hex_flag 返回值是否使用16进制标记
     * @return [type]            [description]
     */
    public function getArrSpecs($hex_flag=false){
        $ret = [];
        if($this->specs){
            $tmp = explode(',', trim($this->specs,','));
        }

        if($hex_flag){
            foreach ($tmp as $k => $v) {
                $ret[$k] = [
                    'mode'=>dechex($k+1),
                    'value'=>$v,
                ];
            }
        }
        else{
            $ret = $tmp;
        }

        return $ret;
    }

    public function afterCreate(){

        $this->getDi()->get('db')->execute(
            'UPDATE i_category SET spec_total=spec_total+1 WHERE category_id=:category_id',
            ['category_id'=>$this->category_id]
        );
    }

    public function beforeDelete(){
        $db = $this->getDi()->get('db');
        $check = $db->fetchColumn('SELECT count(1) FROM i_spu_spec as ss join i_goods_spu as spu on ss.spu_id=spu.spu_id WHERE spec_id=:spec_id AND spu.remove_flag=0',[
            'spec_id'=>$this->spec_id
        ]);
        if($check){
            throw new \Exception("此规格下存在关联的商品信息，无法删除", 1);
            
        }
    }

    public function afterDelete(){
        
        $this->getDi()->get('db')->execute(            
            'UPDATE i_category SET spec_total=spec_total-1 WHERE category_id=:category_id and spec_total>0',
            ['category_id'=>$this->category_id]
        );
    }

    /**
     * @return array|null
     * 用于获取首页的全局商品规格的参数，默认
     * category_id = -1 未启用状态
     * category_id = -2 启用状态
     * 特殊的商品分类id
     */
    public  static function getGlobalSpec()
    {
        $global_spec = db()->fetchOne("select * from i_spec where category_id in(-1,-2)");
        $global_spec_info = json_decode($global_spec['specs'], true);
        if(empty($global_spec_info) || empty($global_spec_info['color']) || empty($global_spec_info['size']))
        {
            return NULL;
        }
        else{
            return [
                'spec_id' => $global_spec['spec_id'],
                'status' => $global_spec['category_id'], //-1为关闭 -2为启用
                'color' => $global_spec_info['color'],
                'size' => $global_spec_info['size']
            ];
        }
    }

}
