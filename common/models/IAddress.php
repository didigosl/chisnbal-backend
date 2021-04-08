<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class IAddress extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $address_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $man;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $phone;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $area;

    public $area_json;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    public $city_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $address;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $postcode;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=true)
     */
    public $hash;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    public $default_flag;

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
        
        $this->setSource("i_address");
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
        $this->belongsTo('area_id', 'Common\Models\IArea', 'area_id', ['alias' => 'Area']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_address';
    }

    static public function getPkCol(){
        return 'address_id';
    }

    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
        if($cols_length==0 OR in_array('user_id',$cols)){
            $validator->add(
                'user_id',
                new PresenceOf()
            );
        }
        
        if($cols_length==0 OR in_array('area_id',$cols)){
             $validator->add(
                'area_id',
                new PresenceOf()
            );
        }

        if($cols_length==0 OR in_array('address',$cols)){
             $validator->add(
                'address',
                new PresenceOf()
            );
        }

        if($cols_length==0 OR in_array('phone',$cols)){
             $validator->add(
                'phone',
                new PresenceOf()
            );
        }

        if($cols_length==0 OR in_array('man',$cols)){
             $validator->add(
                'man',
                new PresenceOf()
            );
        }

        return $validator;
    }

    public function beforeSave(){

        $this->area_id = (int)$this->area_id;
        $this->default_flag = (int)$this->default_flag;
        $this->user_id = (int)$this->user_id;
        
        if($this->default_flag){
            $this->getDi()->get('db')->updateAsDict(
                'i_address',
                ['default_flag'=>0],
                'user_id='.$this->user_id
            );
        }
    }

    public function afterDelete(){

        if($this->default_flag){
            $Address = self::findFirst([
                'user_id=:user_id:',
                'bind'=>['user_id'=>$this->user_id],
                'order'=>'update_time DESC'
            ]);

            if($Address){
                $Address->default_flag = 1;
                $Address->save();
            }
        }
    }

    
    public function afterFind(){
        if(empty($this->area_json) && !empty($this->area_id)){
            if($this->Area){
                $area_arr = $this->Area->getParentNames();
                $area_arr = array_reverse($area_arr);
                $this->area_json = json_encode($area_arr,JSON_UNESCAPED_UNICODE);
                $this->area_json = $this->area_json=='null' ? null : $this->area_json;
                $this->area = implode(' ',$area_arr);
                $this->save();
            }
            
        }
    }

    public function setDefault($user_id){

        try{
            $this->getDi()->get('db')->updateAsDict(
                'i_address',
                ['default_flag'=>0],
                'user_id='.$this->user_id
            );

            $this->default_flag = 1;
            
            if(!$this->save()){
                throw new \Exception($this->getErrorMsg(), 1002);
                
            }
            return true;
        } catch (\Exception $e){
            throw new \Exception($e->getMessage(), 1002);
            
        }
    }

}
