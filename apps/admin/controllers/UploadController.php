<?php

namespace Admin\Controllers;
use Admin\Components\FileSys;
use Common\Libs\Func;
use Phalcon\Exception as Exception;
use Phalcon\Di;
use Common\Components\Upload;
use Common\Components\Image;
use Admin\Components\Oss;
use Admin\Components\ControllerBase;

/**
 * @acl *
 * @aclCustom false
 */
class UploadController extends ControllerBase
{
    //调用场景，不同场景下文件处理方式，返回数据格式可以不同
    public $scene = 'common';

    public function videoAction(){

        $editorid = $this->request->getQuery('editorid','string');
        if($editorid){
            $this->scene = 'editor';
        }
        $field_name = $this->request->getQuery('field_name','string');
        $this->upload($field_name,'video',$this->dispatcher->getActionName());
    }

    public function audioAction(){

        $editorid = $this->request->getQuery('editorid','string');
        if($editorid){
            $this->scene = 'editor';
        }
        $field_name = $this->request->getQuery('field_name','string');
        $this->upload($field_name,'audio',$this->dispatcher->getActionName());
    }

    public function imageAction(){

        #$content = $this->request->getQuery('content');
        $content = $this->request->getQuery('field_name');
        $editorid = $this->request->getQuery('editorid','string');
        if($editorid){
            $this->scene = 'editor';
        }
        $field_name = $this->request->getQuery('field_name','string');

        if($content){


            $relative_path = '/shop/goods/';
            $images = FileSys::uploadAndThumb($relative_path, [$field_name]);


            $data = [
                'url'=>$images[$field_name]['mid'],
                'msg'=>'SUCCESS',
                'status'=>'SUCCESS',
            ];
            $this->send($data);

        }else{
            $this->uploadPicsToS3($content);
        }
    }

    public function uploadPicsToS3($save_dir='',$content=''){
        $data = [];
        try {
            if ($this->di->get('request')->hasFiles()) {
                $shop_id = $this->auth->getShopId();
                $save_dir = trim(($shop_id ? 'shop'.$shop_id : '').'/'.$save_dir,'/');
                $images = FileSys::uploadAndThumb($save_dir, ['file']);
                if(!empty($images['file'])){
                    $data = [
                        'url'=>$images['file']['mid'],
                        'msg'=>'SUCCESS',
                        'status'=>'success',
                    ];
                }else{
                    throw new \Exception("文件上传失败了", 1);
                }
            }else{
                throw new \Exception("没有提供要上传的文件", 1);
            }
        }catch (\Exception $e){
            $data['status'] = 'fail';
            $data['msg'] = $e->getMessage();
            $data['trace'] = $e->getTraceAsString();
        }
        $this->send($data);
    }


    public function fileAction(){

        $editorid = $this->request->getQuery('editorid','string');
        if($editorid){
            $this->scene = 'editor';
        }
        $field_name = $this->request->getQuery('field_name','string');
        $this->upload($field_name,'attachment',$this->dispatcher->getActionName());
    }

    public function zipAction(){
        // var_dump($_FILES);exit;
        $field_name = $this->request->getQuery('field_name','string');
        $this->upload($field_name,'zip',$this->dispatcher->getActionName());
    }

    protected function upload($field_name,$file_type='',$save_dir='', $content = '') {
        $data = [];
        try{
            if ($this->di->get('request')->hasFiles()) {

                $Upload = new Upload;

                $shop_id = $this->auth->getShopId();

                $save_dir = trim(($shop_id ? 'shop'.$shop_id : '').'/'.$save_dir,'/');
                // var_dump($save_dir);exit;
                $files = $Upload->exec($save_dir, [$field_name => $file_type]);

                if (!empty($files[$field_name]['path'])) {

                    if($this->scene=='editor'){
                        $file = $files[$field_name]['path'];
                    }
                    else
                    {
                        if('image'==$file_type){
                            $img = new Image();
                            //echo $this->d->one($cover_path);
                            $img->open($files[$field_name]['path']);
                            $large = $img->thumb([
                                'width'=>1280,
                                'height'=>1280,
                                'suffix'=>'_l',
                            ]);
                            $mid = $img->thumb([
                                'width'=>600,
                                'height'=>600,
                                'suffix'=>'_m',
                            ]);

                            $small = $img->thumb([
                                'width'=>200,
                                'height'=>200,
                                'suffix'=>'_s',
                                'method'=>'zoomCrop',
                            ]);

                            try{

                                $oss_status = true;
                                /*if(SERV_ENV=='p'){
                                    //缩略图上传至oss，原图保存在web服务器
                                    $Oss = new Oss;

                                    if(!$Oss->uploadFile($large)){
                                        $oss_status = false;
                                    }

                                    if(!$Oss->uploadFile($mid)){
                                        $oss_status = false;
                                    }

                                    if(!$Oss->uploadFile($small)){
                                        $oss_status = false;
                                    }
                                }*/

                            } catch(\Exception $e){
                                throw new \Exception($e->getMessage(), 1);

                            }
                            $file = $mid;
                        }
                        else{
                            try{
                                /*if(SERV_ENV=='p'){
                                    //缩略图上传至oss，原图保存在web服务器
                                    $Oss = new Oss;

                                    if(!$Oss->uploadFile($files[$field_name]['path'])){
                                        $oss_status = false;
                                    }
                                }*/
                            } catch(\Exception $e){
                                throw new \Exception($e->getMessage(), 1);

                            }

                            $file = $files[$field_name]['path'];
                        }
                    }



                    $url = DI::getDefault()->get('config')->oss->domain.$file;
                    if (!empty($content)){
                        $url = Func::staticPath($url);
                    }
                    $data = [
                        'name'=>$files[$field_name]['name'],
                        'ext'=>$files[$field_name]['ext'],
                        'size'=>$files[$field_name]['size'],
                        'url'=>$url,
                        'path'=>$file,
                        'msg'=>'SUCCESS',
                        'status'=>'success',
                    ];
                    $this->send($data);
                }
                else{
                    throw new \Exception("文件上传失败了", 1);
                }
            }
            else{
                throw new \Exception("没有提供要上传的文件", 1);

            }
        } catch (\Exception $e){
            $data['status'] = 'fail';
            $data['msg'] = $e->getMessage();
            $data['trace'] = $e->getTraceAsString();
        }

        $this->send($data);
    }

    protected function send($data){
        $act = '_sendTo'.ucfirst($this->scene);
        $this->$act($data);
        exit;
    }

    protected function _sendToCommon($data){
        $this->sendJSON($data);
    }
    protected function _sendToEditor($data){
        echo json_encode([
            'original'	=> $data['name'],
            'title'	=>	basename($data['url']),
            'url'	=>	$data['url'],
            'size'	=>	$data['size'],
            'type'	=>	'.'.$data['ext'],
            'state'	=>	$data['msg'],
        ]);
    }
}
