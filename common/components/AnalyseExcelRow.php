<?php
namespace Common\Components;

use Common\Models\IGoodsSpu as Spu;
use Phalcon\Mvc\User\Component;
use Phalcon\Exception;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

class AnalyseExcelRow extends Component {

    static public $db;
    static public $label_values = [];
    static public $d;

    static public $colNames = [
        '货号',
        '商品名称',
        '分类一',
        '分类二',
        '分类三',
        '标签',
        '原价',
        '价格',
        '成本价',
        '库存',
        '年卡VIP返利',
        '永久VIP返利',
        '与其他优惠共享',
        '商品介绍',
        '规格（规格名称/货号/库存/价格）',
        '数量单位',
        // '物品类型',
        // '长*宽*高(cm)',
        // '重量(kg)',
        // '价值'
    ];

    public $colKeys = [];

    public function analyse($path,$images_dir){
        self::$d = \Phalcon\Di::getDefault()->get('d');
        self::$db = \Phalcon\Di::getDefault()->get('db');
        $full_images_dir = SITE_PATH.'/'.$images_dir;
        $images = [];
        if(file_exists($full_images_dir)){
            $Dir = dir($full_images_dir);
            while (false !== ($name = $Dir->read())) {
                if($name!='.' && $name!='..'){

                    $img_end = strrchr($name,'-');
                    $img_name = substr($name,0,strrpos($name,'-'));

                    // var_dump($img_end,$img_name);exit;

                    if(strpos($img_end, 'cover')===1){
                        $images[$img_name]['cover'] = str_replace('//','/','/'.$images_dir.'/'.$name);
                    }
                    elseif(strpos($img_end, 'pic')===1){
                        $images[$img_name]['pics'][] = str_replace('//','/','/'.$images_dir.'/'.$name);
                    }

                    /* $arr = explode('-', $name);
                    if(!empty($arr[0] && !empty($arr[1]))){
                        if(strpos($arr[1], 'cover')===0){
                            $images[$arr[0]]['cover'] = str_replace('//','/','/'.$images_dir.'/'.$name);
                        }
                        elseif(strpos($arr[1], 'p')===0){
                            $images[$arr[0]]['pics'][] = str_replace('//','/','/'.$images_dir.'/'.$name);
                        }
                    }
                    else{
                        //throw new \Exception("图片命名错误", 1);
                        
                    } */
                    
                }
                
            }
        }

        // echo self::$d->one($images);exit;

        if(file_exists($path)){
            
            $Reader = new \SpreadsheetReader($path);

            $succss_total = 0 ;
            foreach ($Reader as $k=>$row)
            {
                if($k>0){
                    if(empty(implode('',$row))){
                        continue;
                    }

                    // echo self::$d->one($row);
                    
                    try{
                        $Spu = $this->readRow($row,$images);
                        if(!$Spu){
                            throw new \Exception("创建商品失败，".$Spu->getErrorMsg(), 1);
                            
                        }
                    } catch (\Exception $e){
                        throw new \Exception('第'.($k+1).'行，'.$e->getMessage(), 1);
                    }

                    $succss_total++;
                }
                else{
                    foreach(self::$colNames as $name){
                        $this->colKeys[$name] = array_search($name,$row);
                    }
                    
                }
                
            }
            return $succss_total;
        }
        else{
            throw new \Exception("未找到Excel文档", 1);
            
        }
    }

    public function readRow($row,$images){
        file_put_contents(SITE_PATH.'/row.txt',var_export($row,true));
        if(is_array($row)){
            $spu_data = [];
            $categories = [];
            $skus = [];
            $spec = [];
            $rebate = [];
            foreach($row as $k=>$col){
                $col = trim($col);
                // var_dump($col);continue;
                // if($k==0){
                if($k==$this->colKeys['货号']){
                    // echo self::$d->one($col);exit;
                    if(!empty($col)){
                        $spu_data['sn'] = self::getSpuSn($col);
                    }
                    else{
                        throw new \Exception("必须提供商品货号", 1);
                    }
                }
                // if($k==1){
                if($k==$this->colKeys['商品名称']){
                    if(!empty($col)){
                        $spu_data['spu_name'] = self::getSpuName($col);
                    }
                    else{
                        throw new \Exception("必须提供商品名称", 1);
                    }
                    // var_dump($spu_data);
                }
                // if($k==2){
                if($k==$this->colKeys['分类一']){
                    if(!empty($col)){
                        // var_dump($col);
                        $main_category = self::getMainCategory($col);
                        $categories['category_id1'] = $main_category['category_id'];
                    }
                    else{
                        throw new \Exception("必须提供商品分类一", 1);
                        
                    }
                    
                }
                // if($k==3){
                if($k==$this->colKeys['分类二']){
                    if(!empty($col)){
                        $categories['category_id2'] = self::getCategory($col);
                    }
                    
                }
                // if($k==4){
                if($k==$this->colKeys['分类三']){
                    if(!empty($col)){
                        $categories['category_id3'] = self::getCategory($col);
                    }
                    
                }

                // if($k==5){
                if($k==$this->colKeys['标签']){
                    if(!empty($col)){
                        $spu_data['labels'] = self::getLabels($col);
                    }
                }

                // if($k==6){
                if($k==$this->colKeys['原价']){
                    if(!empty($col)){
                        $spu_data['origin_price'] = self::getPrice($col);
                    }
                    
                }

                // if($k==7){
                if($k==$this->colKeys['价格']){
                    if(!empty($col)){
                        $spu_data['price'] = self::getPrice($col);
                    }
                    else{
                        throw new \Exception("必须提供商品价格", 1);
                        
                    }
                }

                if($k==$this->colKeys['VIP价格1']){
                    if(!empty($col)){
                        $spu_data['price2'] = self::getPrice($col);
                    }
                }

                if($k==$this->colKeys['VIP价格2']){
                    if(!empty($col)){
                        $spu_data['price3'] = self::getPrice($col);
                    }
                }

                if($k==$this->colKeys['VIP价格3']){
                    if(!empty($col)){
                        $spu_data['price4'] = self::getPrice($col);
                    }
                }

                if($k==$this->colKeys['VIP价格4']){
                    if(!empty($col)){
                        $spu_data['price5'] = self::getPrice($col);
                    }
                }

                // if($k==8){
                if($k==$this->colKeys['成本价']){
                    if(!empty($col)){
                        $spu_data['cost_price'] = self::getPrice($col);
                    }
                    
                }
                // if($k==9){
                if($k==$this->colKeys['库存']){
                    if(!empty($col)){
                        $spu_data['stock'] = self::getStock($col);
                    }
                    
                }                
                // if($k==10){
                if($k==$this->colKeys['年卡VIP返利']){
                    if(!empty($col)){
                        $rebate['2'] = self::getPrice($col);
                    }
                    
                }
                // if($k==11){
                if($k==$this->colKeys['永久VIP返利']){
                    if(!empty($col)){
                        $rebate['3'] = self::getPrice($col);
                    }
                    
                }

                $spu_data['rebate'] = json_encode($rebate,JSON_UNESCAPED_UNICODE);

                // if($k==12){
                if($k==$this->colKeys['与其他优惠共享']){
                    if(!empty($col)){
                        $spu_data['rebate_with_discount'] = self::getWithDiscount($col);
                    }
                    
                }

                // if($k==13){
                if($k==$this->colKeys['商品介绍']){
                    if(!empty($col)){
                        $spu_data['content'] = self::getContent($col);
                    }
                }

                // if($k==14){
                if($k==$this->colKeys['规格（规格名称/货号/库存/价格）']){
                    if(!empty($col)){
                        $skus = self::getSkus($col,$main_category['spec_names'],$main_category['specs']);
                        $spu_data['spec_data'] = json_encode($skus['spec_data'],JSON_UNESCAPED_UNICODE);
                        // echo self::$d->one($skus);//exit;
                    }
                    else{
                        // $skus = [];
                        // throw new \Exception("必须提供规格信息", 1);
                        
                    }
                }

                // if($k==15){
                if($k==$this->colKeys['数量单位']){
                    if(!empty($col)){
                        $spu_data['unit'] = self::getContent($col);
                    }
                }

                /* if($k==$this->colKeys['物品类型']){
                    if(!empty($col)){
                        // $spu_data['express_goods_type'] = self::getContent($col);
                    }
                }

                if($k==$this->colKeys['长*宽*高(cm)']){
                    if(!empty($col)){
                        // $spu_data['express_goods_type'] = self::getContent($col);
                    }
                }

                if($k==$this->colKeys['重量(kg)']){
                    if(!empty($col)){
                        // $spu_data['express_goods_type'] = self::getContent($col);
                    }
                }

                if($k==$this->colKeys['价值']){
                    if(!empty($col)){
                        $spu_data['express_goods_type'] = self::getContent($col);
                    }
                } */
            }

            // $sn = strtolower($spu_data['sn']);
            $sn = $spu_data['sn'];
            if(!empty($images)){
                if(!empty($images[$sn]['cover'])){
                    $spu_data['cover'] = $images[$sn]['cover'];
                }

                if(!empty($images[$sn]['pics'])){
                    $spu_data['pics'] = implode(',',$images[$sn]['pics']);
                }
            }
            // echo self::$d->one($images);
            // echo self::$d->one($spu_data);exit;
            if(!empty($spu_data)){
                $Spu = Spu::findFirst([
                    'sn=:sn:',
                    'bind'=>['sn'=>$spu_data['sn']]
                ]);
                if(!$Spu){
                    $Spu = new Spu;
                }
                
                $spu_data['remove_flag'] = 0;
                $Spu->assign($spu_data);
                if($Spu->save()){
                    $Spu->updateCategory($categories);
                    // var_dump($skus);exit;
                    $Spu->updateSkus($skus['skus']);
                    $Spu->updateSpecs($skus['spec_data']);

                    return $Spu;      
                }
                else{
                    throw new \Exception($Model->getErrorMsg(), 1);
                }
            }
        }
    }

    static function getSpuSn($data){
        return trim($data);
    }

    static function getSpuName($data){
        return trim($data);
    }

    static function getMainCategory($data){
        // $db = \Phalcon\Di::getDefault()->get('db');
        $id = self::$db->fetchColumn('SELECT category_id FROM i_category WHERE category_name = :name',['name'=>trim($data)]);
        if(!$id){
            throw new \Exception("[分类一]没有找到对应的分类:".$data, 1);
            
        }

        $temp_spec = self::$db->fetchAll('SELECT * FROM i_spec WHERE category_id=:id',\Phalcon\Db::FETCH_ASSOC,['id'=>$id]);
        $spec_names = [];
        $specs = [];
        if(!empty($temp_spec)){
            foreach ($temp_spec as $k => $v) {
                $spec_names[$v['spec_id']] = $v['spec_name'];
                $specs[$v['spec_name']] = explode(',',$v['specs']);
            }
        }
        $ret['category_id'] = $id;
        $ret['spec_names'] = $spec_names;
        $ret['specs'] = $specs;
        return $ret;
    }

    static function getCategory($data){
        // $db = \Phalcon\Di::getDefault()->get('db');
        $id = self::$db->fetchColumn('SELECT category_id FROM i_category WHERE category_name = :name',['name'=>trim($data)]);
        if(!$id){
            throw new \Exception("[分类二/分类三]没有找到对应的分类:".$data, 1);
            
        }

        return $id;
    }

    static function getLabels($data){

        if(!self::$label_values){
            $temp_labels = self::$db->fetchAll('SELECT * from i_label');
            foreach ($temp_labels as $k => $v) {
                self::$label_values[$v['label_name']] = $v['label_id'];
            }
        }
        $label_ids = [];
        $labels = explode('|',$data);
        foreach($labels as $v){
            if(!self::$label_values[$v]){
                throw new \Exception("[标签]提供了不存在的标签", 1);
                
            }
            else{
                $label_ids[] = self::$label_values[$v];
            }
            
        }
        $ret = implode(',',$label_ids);
        return $ret;
    }

    static function getPrice($data){
        $data = trim($data,'_');
        if(!preg_match('/^[0-9]+\.{0,1}[0-9]{0,2}$/',$data)){
            throw new \Exception("价格必须为数字，最多两位小数，当前值：".$data, 1);
            
        }
        $ret = fmtPrice($data);
        return $ret;
    }

    static function getStock($data){
        return intval($data);
    }

    static function getWithDiscount($data){
        if($data!='是' and !empty($data)){
            throw new \Exception('[与其他优惠共享]只可不填或者填写“是”，当前值为：'.$data, 1);
            
        }
        $ret = 1;
        return $ret;
    }

    static function getContent($data){
        
        return trim($data);
    }

    static function getSkus($data,$spec_names,$specs){
        // echo self::$d->one($spec_names);
        if(empty($data)){
            throw new \Exception("缺少规格数据", 1);
            
        }
        $ret = [
            'spec_data'=>[],
            'skus'=>[],
        ];
        $lines = explode("\n",$data);
        foreach ($lines as $l) {
            $l = trim($l);
            $attrs = explode('/',$l);
            if(count($attrs)!=4){
                throw new \Exception('[规格]参数错误，必须依次填写“规格名称/货号/库存/价格”', 1);
                
            }

            $sku = [];

            foreach($attrs as $k=>$v){
                $v = trim($v);
                if($k==0){
                    $v = trim(str_replace('，', ',', $v));
                    $spec_attrs = explode(',',$v);
                    // echo self::$d->one($spec_attrs);
                    if(count($spec_attrs)<1){
                        throw new \Exception("规格数据格式错误1", 1);
                        
                    }
                    foreach($spec_attrs as $spec_k=>$spec_item){
                        $spec_item = trim(str_replace('：', ':', $spec_item));
                        $spec_attrs[$spec_k] = explode(':',$spec_item);
                        if(count($spec_attrs[$spec_k])!=2){
                            throw new \Exception("规格数据格式错误2", 1);
                            
                        }
                    }

                    foreach($spec_attrs as $spec_a){
                        if(!in_array($spec_a[0],$spec_names)){
                            throw new \Exception("使用了不合法的规格名称", 1);
                            
                        }

                        if(!in_array($spec_a[1],$specs[$spec_a[0]])){
                            throw new \Exception("使用了不合法的规格选值:“".$spec_a[0].":".$spec_a[1]."”", 1);
                            
                        }

                        $spec_id = array_search($spec_a[0], $spec_names);
                        $ret['spec_data'][$spec_id][] = implode(':',$spec_a);


                    }

                    $sku['spec_info'] = $v;
                }

                if($k==1){
                    $sku['sn'] = $v;
                }

                if($k==2){
                    if(!preg_match('/^[0-9]+$/',$v)){
                        throw new \Exception("[规格]库存必须是整数，当前值：".$v, 1);
                        
                    }
                    $sku['stock'] = $v;

                }

                if($k==3){
                    $v = trim($v,'_');
                    if(!preg_match('/^[0-9]+\.{0,1}[0-9]{0,2}$/',$v)){
                        throw new \Exception("[规格]价格必须为数字，最多两位小数，当前值：".$v, 1);
                        
                    }
                    $sku['price'] = $v;
                }

                $sku['status'] = 1;
            }

            $ret['skus'][] = $sku;
        }

        
        return $ret;
    }

}
