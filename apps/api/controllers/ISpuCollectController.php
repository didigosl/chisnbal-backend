<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IGoodsSpu;
use Common\Models\ISpuCollect;
use Common\Libs\Func;

class ISpuCollectController extends ControllerAuth
{


    /*
     * 收藏列表
     */
    public function listAction(){
        $ISpuCollect=ISpuCollect::find(['user_id=:user_id:','bind'=>[
            'user_id'=>$this->User->user_id
        ]]);
        $data=[];
        $spu=[];
        foreach ($ISpuCollect as $value){
            $spu['collect_id']=$value->collect_id;
            $spu['spu_id']=$value->Spu->spu_id;
            $spu['sn']=$value->Spu->sn;
            $spu['spu_name']=$value->Spu->spu_name;
            $spu['cover']=$value->Spu->cover;
            $spu['labels']=$value->Spu->labels;
            $spu['price']=$value->Spu->price;
            $spu['unit']=$value->Spu->unit;
            $data[]=$spu;
        }
        $this->sendJSON(['data'=>$data]);
    }

    /**
     * 收藏商品
     * @throws \Exception
     */
    public function addAction(){
        $ISpuCollect=ISpuCollect::findFirst(['spu_id=:spu_id: and user_id=:user_id:','bind'=>[
            'spu_id'=>$this->request->get('spu_id'),
            'user_id'=>$this->User->user_id,
        ]]);
        if(!$ISpuCollect){
            $ISpuCollect=new ISpuCollect();
        }
        $ISpuCollect->spu_id=$this->request->get('spu_id');
        $ISpuCollect->user_id=$this->User->user_id;
        $ISpuCollect->collect_time=date('Y-m-d H:i:s');
        if($ISpuCollect->save()){
            $this->sendJSON(['data'=>$ISpuCollect->collect_id]);
        }else{
            throw new \Exception('收藏失败', 303001);
        }
    }

    /**
     * 取消收藏
     * @throws \Exception
     */
    public function delAction(){
        $ISpuCollect=ISpuCollect::findFirst(['collect_id=:collect_id:','bind'=>[
            'collect_id'=>$this->request->get('collect_id')
        ]]);

        if($ISpuCollect->delete()){
            $this->sendJSON([]);
        }else{
            throw new \Exception('取消收藏失败', 303001);
        }
    }

}
