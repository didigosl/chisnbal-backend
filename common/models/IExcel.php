<?php

namespace Common\Models;

use Common\Components\AnalyseExcelRow;
use Common\Components\File;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class IExcel extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $excel_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $images;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $zip;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $path;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $size;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $msg;

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
        $this->setSource("i_excel");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_excel';
    }

    static public function getPkCol(){
        return 'excel_id';
    }

    static public $attrNames = [
        'name'=>'文档名称',
        'zip'=>'图片压缩包',
        'path'=>'Excel文档',
        'status'=>'状态',
        'total'=>'成功导入条目',
        'create_time'=>'上传时间'
    ];

    public function validation() {
        $validator = new Validation();
        /*$validator->add(
            'zip',
            new PresenceOf()
        );*/
        $validator->add(
            'path',
            new PresenceOf()
        );

        return $this->validate($validator);
        
    }

    public function beforeCreate(){

        parent::beforeCreate();
        $config = $this->getDi()->get('config');
        if(!file_exists(SITE_PATH.$this->zip)){
            $message = new Message('图片压缩包不存在');
            $this->appendMessage($message);
            return false;
            // throw new \Exception("Error Processing Request", 2002);
            
        }
        $auth = $this->getDi()->get('auth')->getIdentity();
        $this->admin_id = $auth['id'];
    }

    public function afterCreate(){
        $config = $this->getDi()->get('config');
        $zip = SITE_PATH . $this->zip;
        $path = SITE_PATH . $this->path;
        if(file_exists($path)){
            $this->getDi()->get('db')->begin();
            try{
             
                if(!empty($this->zip) && file_exists($zip) ){
                    $ZipArchive = new \ZipArchive;
                    $res = $ZipArchive->open($zip);
                    if ($res === TRUE) {
                        $shop_id = $this->getDi()->get('auth')->getShopId();
                        $shop_dir = $shop_id ? 'shop'.$shop_id.'/' : '';
                        $base_dir = SITE_PATH;
                        $images_dir = $config->params['uploadDir'].$shop_dir.'import/'.$this->excel_id;

                        $full_path = str_replace('//','/',$base_dir.$images_dir);
                        if(!file_exists($full_path)){
                            File::createDir($base_dir,$images_dir);
                        }
                        if(!$ZipArchive->extractTo($full_path)){
                            throw new \Exception("释放压缩包失败", 1);
                            
                        }
                        $ZipArchive->close();
                        // var_dump($base_dir.$images_dir);exit;
                    } else {
                        throw new \Exception("解压图片压缩包失败", 1);
                        
                    }
                }

                $Analyse = new AnalyseExcelRow;
                $total = $Analyse->analyse($path,$images_dir);

                $this->status = 'success';
                $this->total = $total;
                $this->save();
                // $this->getDi()->get('flashSession')->success('批量导入完成：成功导入了'.$total.'条新的商品数据');
                $this->getDi()->get('db')->commit();
                
            } catch(\Exception $e){
                $this->getDi()->get('db')->rollback();
                $this->status = 'fail';
                $this->msg = $e->getMessage();
                $this->save();
                throw new \Exception($e->getMessage(), 1);
                
                // $this->getDi()->get('flashSession')->error('批量导入失败：'.$e->getMessage());
            }
            
        }
        else{
            throw new \Exception("未找到Excel文档", 1);
            
        }
    }

    
}
