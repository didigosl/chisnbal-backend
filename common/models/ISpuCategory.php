<?php

namespace Common\Models;

class ISpuCategory extends Model
{

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $spu_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $category_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $merger;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $seq;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        
        $this->setSource("i_spu_category");
        $this->belongsTo('spu_id', 'Common\Models\IGoodsSpu', 'spu_id', ['alias' => 'Spu']);
        $this->belongsTo('category_id', 'Common\Models\ICategory', 'category_id', ['alias' => 'Category']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_spu_category';
    }

    public function beforeSave(){
        $this->category_id = (int)$this->category_id;
        $this->seq = (int)$this->seq;
        $this->merger = ','.trim($this->Category->merger.$this->category_id,',').',';
    }

    public function afterCreate(){
        $this->updateSpuTotal();

    }

    public function afterDelete(){
        $this->updateSpuTotal();
      
    }

    protected function updateSpuTotal(){

        $db = $this->getDi()->get('db');
        $category_ids = trim($this->Category->merger.$this->category_id,',');
        $category_ids_arr = explode(',',trim($category_ids,','));
        foreach ($category_ids_arr as $category_id) {
            $category = $db->fetchOne("SELECT * FROM i_category WHERE category_id=:id",\Phalcon\Db::FETCH_ASSOC,['id'=>$category_id]);

			$merger = '';
	        if($category['parent_id']){
	            $merger = ','.trim($category['merger'].$category['category_id'],',').',';
	        }
	        else{
	            $merger = ','.$category['category_id'].',';
	        }

	        $sql = "SELECT count(distinct spu.spu_id) 
	            FROM i_spu_category as sc
	            JOIN i_goods_spu as spu on sc.spu_id=spu.spu_id
                WHERE spu.remove_flag=0 AND merger like '".$merger."%'";
                
            $total = $db->fetchColumn($sql);
            
            $db->updateAsDict('i_category',['spu_total'=>$total],'category_id='.$category_id);


		}
  
    }
}
