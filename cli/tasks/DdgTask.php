<?php
use Impt\Models\Supplier;
use Impt\Models\SupplierParentCategory;
use Impt\Models\SupplierCategory;
use Impt\Models\SupplierGoods;
use Impt\Models\Customer;
use Impt\Models\Order;

class DdgTask extends \Phalcon\Cli\Task
{

    public function initialize(){

    }

    public function shopAction(){
        // echo 'yes';exit;
        $total = $this->db->fetchColumn("SELECT count(1) FROM so_supplier WHERE site='ddg'");
        $p = 1;
        $offset = 20;
        do{
            $start = $offset*($p-1);
            $list = $this->db->fetchAll("SELECT * FROM so_supplier WHERE site='ddg' LIMIT $start,$offset");
            if($list){
                foreach($list as $v){
                    $Supplier = new Supplier;
                    $Supplier->ddg_id = $v['Id'];
                    $Supplier->data = $v;
                    if($Supplier->save()){

                    }
                    else{
                        echo $Supplier->getErrorMsg().PHP_EOL;
                    }
                }

                
            }

            $p++;

        } while($start<=$total);
        echo $total;
    }

    public function SupplierParentCategoryAction(){

        $shops = $this->db->fetchAll("SELECT * FROM ddg_supplier");
        foreach($shops as $shop){
            $total = $this->db->fetchColumn("SELECT count(1) FROM so_supplierparentcategory WHERE BusinessId=:BusinessId",[
                'BusinessId'=>$shop['ddg_id']
            ]);
            $p = 1;
            $offset = 20;
            do{
                $start = $offset*($p-1);
                $list = $this->db->fetchAll("SELECT * FROM so_supplierparentcategory WHERE BusinessId=:BusinessId LIMIT $start,$offset",\Phalcon\Db::FETCH_ASSOC,[
                    'BusinessId'=>$shop['ddg_id']
                ]);
                if($list){
                    foreach($list as $v){
                        $Category = new SupplierParentCategory;
                        $Category->ddg_id = $v['CategoryId'];
                        $Category->data = $v;
                        if($Category->save()){

                        }
                        else{
                            echo $Category->getErrorMsg().PHP_EOL;
                        }
                    }
                }

                $p++;

            } while($start<=$total);
            echo 'shop:'.$shop['ddg_id'].' total:'.$total.PHP_EOL;
        }
        
    }

    public function SupplierCategoryAction(){

        $shops = $this->db->fetchAll("SELECT * FROM ddg_supplier");
        foreach($shops as $shop){
            $total = $this->db->fetchColumn("SELECT count(1) FROM so_suppliercategory  WHERE BusinessId=:BusinessId ",[
                'BusinessId'=>$shop['ddg_id']
            ]);
            $p = 1;
            $offset = 20;
            do{
                $start = $offset*($p-1);
                $list = $this->db->fetchAll("SELECT * FROM so_suppliercategory WHERE BusinessId=:BusinessId LIMIT $start,$offset",\Phalcon\Db::FETCH_ASSOC,[
                    'BusinessId'=>$shop['ddg_id']
                ]);
                if($list){
                    foreach($list as $v){
                        $Category = new SupplierCategory;
                        $Category->ddg_id = $v['Id'];
                        $Category->data = $v;
                        if($Category->save()){

                        }
                        else{
                            echo $Category->getErrorMsg().PHP_EOL;
                        }
                    }

                    
                }

                $p++;

            } while($start<=$total);
            echo 'shop:'.$shop['ddg_id'].' total:'.$total.PHP_EOL;
        }
        
    }

    public function SupplierGoodsAction(){

        $shops = $this->db->fetchAll("SELECT * FROM ddg_supplier");
        foreach($shops as $shop){
            $total = $this->db->fetchColumn("SELECT count(1) FROM so_suppliergoods WHERE BusinessId=:BusinessId",[
                'BusinessId'=>$shop['ddg_id']
            ]);
            $p = 1;
            $offset = 20;
            do{
                $start = $offset*($p-1);
                $list = $this->db->fetchAll("SELECT * FROM so_suppliergoods WHERE BusinessId=:BusinessId LIMIT $start,$offset",\Phalcon\Db::FETCH_ASSOC,[
                    'BusinessId'=>$shop['ddg_id']
                ]);
                if($list){
                    foreach($list as $v){
                        $Goods = new SupplierGoods;
                        $Goods->ddg_id = $v['GoodsCode'];
                        $Goods->data = $v;
                        if($Goods->save()){
                            echo $v['GoodsCode'].'saved'.PHP_EOL;
                        }
                        else{
                            echo $Goods->getErrorMsg().PHP_EOL;
                        }
                    }

                    
                }

                $p++;

            } while($start<=$total);
            echo 'shop:'.$shop['ddg_id'].' total:'.$total.PHP_EOL;
        }
        
    }

    public function CustomerAction(){
    
        $shops = $this->db->fetchAll("SELECT * FROM ddg_supplier");
        foreach($shops as $shop){
            $total = $this->db->fetchColumn("SELECT count(1) FROM so_customer WHERE BusinessId=:BusinessId",[
                'BusinessId'=>$shop['ddg_id']
            ]);
            $p = 1;
            $offset = 20;
            do{
                $start = $offset*($p-1);
                $list = $this->db->fetchAll("SELECT * FROM so_customer  WHERE BusinessId=:BusinessId LIMIT $start,$offset",\Phalcon\Db::FETCH_ASSOC,[
                    'BusinessId'=>$shop['ddg_id']
                ]);
                if($list){
                    foreach($list as $v){
                        $Customer = new Customer;
                        $Customer->ddg_id = $v['Id'];
                        $Customer->data = $v;
                        if($Customer->save()){
                            echo $v['LoginName'] .' saved'.PHP_EOL;
                        }
                        else{
                            echo $v['Id'].':'.$Customer->getErrorMsg().PHP_EOL;
                        }
                    }
     
                }

                $p++;

            } while($start<=$total);
            echo 'shop:'.$shop['ddg_id'].' total:'.$total.PHP_EOL;
        }
        
        
    }

    public function OrderAction(){
    
        $shops = $this->db->fetchAll("SELECT * FROM ddg_supplier");
        foreach($shops as $shop){
            $total = $this->db->fetchColumn("SELECT count(1) FROM so_order WHERE BusinessId=:BusinessId",[
                'BusinessId'=>$shop['ddg_id']
            ]);
            $p = 1;
            $offset = 20;
            try{
                do{
                    $start = $offset*($p-1);
                    $list = $this->db->fetchAll("SELECT * FROM so_order  WHERE BusinessId=:BusinessId LIMIT $start,$offset",\Phalcon\Db::FETCH_ASSOC,[
                        'BusinessId'=>$shop['ddg_id']
                    ]);
                    if($list){
                        foreach($list as $v){
                            $Order = new Order;
                            $Order->ddg_id = $v['OrderId'];
                            $Order->data = $v;
                            if($Order->save()){
                                echo $v['OrderId'] .' saved'.PHP_EOL;
                            }
                            else{
                                echo 'OrderId:'. $v['OrderId'].':'.$Order->getErrorMsg().PHP_EOL;
                                exit;
                            }
                        }
         
                    }
    
                    $p++;
    
                } while($start<=$total);
            } catch (\Exception $e){
                echo $e->getMessage().PHP_EOL;
                exit;
            }
            
            echo 'shop:'.$shop['ddg_id'].' total:'.$total.PHP_EOL;
        }
        
        
    }

}