<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Components\Upload;
use Common\Components\Image;
use Common\Libs\Func;

class UploadController extends ControllerAuth {


    public function avatarAction(){

        $this->upload('avatar','image');
    }

    protected function upload($field_name,$file_type='') {
        $data = [];

        if ($this->di->get('request')->hasFiles()) {

            $save_dir = '';

            $Upload = new Upload;

            $files = $Upload->exec($save_dir, [$field_name => $file_type]);

            if (!empty($files[$field_name]['path'])) {

//				if('image'==$file_type){
                $img = new Image();
                //echo $this->d->one($cover_path);
                $img->open($files[$field_name]['path']);
                $file='/uploads/'.$img->file;

                $name = SITE_PATH.$file;
                $url = "https://pic.manshiguang.it/api/pic/uploadFile";
                $ch = curl_init();
                $data = array(
                    "mark" => "chisnbal",
                    "file" => new \CURLFile($name),
                );
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_POST,true);
                curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

                $result = curl_exec($ch);
                curl_close($ch);

                $result_data = json_decode($result);

                if($result_data->code == 1){
                    $images = 'https://pic.manshiguang.it/api/pic/getImg?mark=chisnbal&pic_id='.$result_data->data->pic_id;
                }else{
                    throw new \Exception($result_data->msg, 1);
                }


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


                $file = $small;
//				}
//				else{
//
//					$file = $files[$field_name]['path'];
//				}


//                $name = SITE_PATH.$file;

//				$avatar = '/uploads'.$file;
//				$this->User->avatar = $avatar;
                $avatar = $images;
                $this->User->avatar = $avatar;
                if($this->User->save()){
                    $data = [
                        // 'name'=>$files[$field_name]['name'],
                        // 'ext'=>$files[$field_name]['ext'],
                        'size'=>$files[$field_name]['size'],
                        'url'=>Func::staticPath($avatar),
                        // 'path'=>$file,
                        // 'msg'=>'SUCCESS',
                        // 'status'=>'success',
                    ];
                    $this->send($data);
                }
                else{
                    throw new \Exception("保存失败，".$this->User->getErrorMsg(), 1001);

                }

                if($file){
                    unlink('./uploads'.$file);
                }


            }
            else{
                throw new \Exception("文件上传失败了", 2001);
            }
        }
        else{
            throw new \Exception("没有提供要上传的文件", 2001);

        }

    }

    protected function send($data){

        $this->sendJSON([
            'data'=>$data
        ]);
    }

    public function uploadFileAction($field_name='file',$file_type='image') {
        $data = [];

        if ($this->di->get('request')->hasFiles()) {

            $save_dir = '';

            $Upload = new Upload;

            $files = $Upload->exec($save_dir, [$field_name => $file_type]);

            if (!empty($files[$field_name]['path'])) {
                $img = new Image();
                $img->open($files[$field_name]['path']);
                $file='/uploads/'.$img->file;

                $name = SITE_PATH.$file;
                $url = "https://pic.manshiguang.it/api/pic/uploadFile";
                $ch = curl_init();
                $data = array(
                    "mark" => "chisnbal",
                    "file" => new \CURLFile($name),
                );
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_POST,true);
                curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

                $result = curl_exec($ch);
                curl_close($ch);

                $result_data = json_decode($result);

                if($result_data->code == 1){
                    $images = 'https://pic.manshiguang.it/api/pic/getImg?mark=chisnbal&pic_id='.$result_data->data->pic_id;
                }else{
                    throw new \Exception($result_data->msg, 1);
                }

                if($file){
                    unlink('.'.$file);
                }
                return  $this->send($images);
            }
            else{
                throw new \Exception("文件上传失败了", 2001);
            }
        }
        else{
            throw new \Exception("没有提供要上传的文件", 2001);
        }

    }

}
