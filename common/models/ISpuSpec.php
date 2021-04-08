<?php
namespace Common\Models;
class ISpuSpec extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $spu_spec_id;

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
    public $spec_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_spu_spec");
        $this->belongsTo('spu_id','Common\Models\IGoodsSpu','spu_id',['alias' => 'Spu']);
        $this->belongsTo('spec_id','Common\Models\ISpec','spec_id',['alias' => 'Spec']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_spu_spec';
    }

    static public function getPkCol(){
        return 'spu_spec_id';
    }

    public function beforeSave(){
        $this->spu_id = (int)$this->spu_id;
        $this->spec_id = (int)$this->spec_id;
    }

    public function beforeCreate(){
        if($this->Spec){
            $this->Spec->total = $this->Spec->total + 1;
            $this->Spec->save();
        }
    }

    public function afterCreate(){
        $less = $this->Spec->total - 1;
        $this->Spec->total = $less>=0 ? $less : 0;
        $this->Spec->save();
    }

}
