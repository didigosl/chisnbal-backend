<?php
namespace Admin\Controllers;
use \Common\Components\Assets;
use Admin\Components\ControllerBase;
use Common\Models\ICart;

/**
 * @aclDesc 购物车
 * @aclCustom false
 * @acl *
 */
class ICartController extends ControllerBase {

	//清理掉下架、删除商品的购物车数据
	public function clearCartAction(){
		$list = $this->db->fetchAll('SELECT spu_id FROM i_goods_spu WHERE remove_flag=1');
		if($list){
			foreach($list as $v){
				$this->db->execute('DELETE FROM i_cart WHERE spu_id=:spu_id',['spu_id'=>$v['spu_id']]);
			}
		}

		exit;
	}

}