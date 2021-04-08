<?php
namespace Common\Models;

use Common\Libs\Func;
class IShareInfo extends Model
{

    public $id;

    public $title;

    public $sub_title;
     
    public $app_name;
     
    public $app_sub_name;
     
    public $ios_url;

    public $android_url;
     
    public $logo;

    public $pics;
     
    public $intro;

    static public $attrNames = [
        'title'=>'标题',
        'sub_title'=>'标题下内容',
        'app_name'=>'APP名称',
        'app_sub_name'=>'APP副标题',
        'ios_url'=>'IOS链接',
        'android_url'=>'Android链接',
        'logo'=>'LOGO',
        'pics'=>'APP截图',
        'intro'=>'应用介绍',
    ];

    static public function getPkCol(){
        return 'id';
    }

    public function getSource()
    {
        return 'i_share_info';
    }
    public function initialize() {
        $this->useDynamicUpdate(true);
    }

    public function getFmtPics(){
        $ret = [];
        if($this->pics){
            $ret = explode(',', $this->pics);
            foreach($ret as $k=>$v){
                $ret[$k] = Func::staticPath($v);
            }
        }
        return $ret;
    }
}
