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

					
					$file = $small;
				}
				else{					

					$file = $files[$field_name]['path'];
				}

				$avatar = '/uploads'.$file;
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

}
