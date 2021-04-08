<?php
namespace Admin\Components;

use Phalcon\Mvc\User\Component;
use Phalcon\Exception;
use Common\Components\Image;
use Common\Components\Upload;
use Phalcon\Di;

class FileSys extends Component {

	static public function uploadAndThumb($upload_dir,$inputs=[]){
		$images = [];	//返回上传的图片
		$images_inputs = [];
		foreach ($inputs as $v) {
			$images_inputs[$v] = 'image';
		}
		
		try{
			$Upload = new Upload;
			$files = $Upload->exec($upload_dir, $images_inputs);
			
			foreach ($inputs as $v) {
				if($files[$v]['path']){
					//$cover_path = $files['save'][$v];

					$img = new Image();
					//echo $this->d->one($cover_path);
					$img->open($files[$v]['path']);

                    $file='/uploads/'.$img->file;
//					$large = $img->thumb([
//						'width'=>1280,
//						'height'=>1280,
//						'suffix'=>'_l',
//						]);
//					$mid = $img->thumb([
//						'width'=>600,
//						'height'=>600,
//						'suffix'=>'_m',
//						]);
//
//					$small = $img->thumb([
//						'width'=>200,
//						'height'=>200,
//						'suffix'=>'_s',
//						'method'=>'zoomCrop',
//						'quality'=>55,
//						]);
//
//					$icon = $img->thumb([
//						'width'=>120,
//						'height'=>120,
//						'suffix'=>'_i',
//						'method'=>'zoomCrop',
//						]);
					
					try{
//						$oss_status = true;
//						if(SERV_ENV=='p'){
							//缩略图上传至oss，原图保存在web服务器
							/*$Oss = new Oss;
							
							if(!$Oss->uploadFile($large)){
								$oss_status = false;
							}

							if(!$Oss->uploadFile($mid)){
								$oss_status = false;
							}
							
							if(!$Oss->uploadFile($small)){
								$oss_status = false;
							}*/
//						}
                        $path=self::fileUpload($file);
                        $images[$v]['large']=$path;
                        $images[$v]['mid']=$path;
                        $images[$v]['small']=$path;
					} catch(\Exception $e){
						throw new \Exception($e->getMessage(), 1);
						
					}
					
//					if($oss_status){
//						$images[$v]['large'] = DI::getDefault()->get('config')->oss->domain.$large;
//						$images[$v]['mid'] = DI::getDefault()->get('config')->oss->domain.$mid;
//						$images[$v]['small'] = DI::getDefault()->get('config')->oss->domain.$small;
//					}
					//var_dump($oss_status,$images);exit;
					
				}
			}
		} catch (\Exception $e){
			throw new \Exception($e->getMessage(), 1);
			
		}		

		return $images;		
	}

	static public function upload($upload_dir,$inputs=[]){
		$images = [];	//返回上传的图片
		$images_inputs = [];
		foreach ($inputs as $v) {
			$images_inputs[$v] = 'image';
		}
		
		try{
			$Upload = new Upload;
			$files = $Upload->exec($upload_dir, $images_inputs);
			
			foreach ($inputs as $v) {
				if($files[$v]['path']){
					//$cover_path = $files['save'][$v];
					$path = $files[$v]['path'];

//					try{
//						$oss_status = true;
//						if(SERV_ENV=='p'){
//							/*$Oss = new Oss;
//
//							if(!$Oss->uploadFile($path)){
//								$oss_status = false;
//							}*/
//						}
//
//					} catch(\Exception $e){
//						throw new \Exception($e->getMessage(), 1);
//
//					}
//
//					if($oss_status){
						//$path = DI::getDefault()->get('config')->oss->domain.$path;
						$file = DI::getDefault()->get('config')->oss->domain.$path;
//					}


                    $path=self::fileUpload($file);
				}
			}
		} catch (\Exception $e){
			throw new \Exception($e->getMessage(), 1);
			
		}		
		return $path;		
	}

    /**
     * AWS S3上传文件
     * @param string $file 文件名称
     * @return array
     */
    static public function fileUpload($file){
        require_once './aws-autoloader.php';
        $s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => 'eu-central-1', #改为美国西部
            'credentials' => [
                'key'    => 'AKIAWSJGTEW2BNF5BVBP', #访问秘钥
                'secret' => 'so/jkiRHVEKLzjT8kPbdecdH8UTXrik1HmrzxRWY' #私有访问秘钥
            ]
        ]);
        $bucketName = 'ccshopbarcelona'; #存储桶的名字
        // $file_Path/ = '/data/wwwroot/aws-sdk-php-laravel/QQ图片20180223091800.png'; #要上传的文件的路径
        $file_Path = '.'.$file; #要上传的文件的路径
        $key = basename($file_Path);
        try {
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key'    => $key,
                'Body'   => fopen($file_Path, 'r'),
                'ACL'    => 'public-read',
            ]);
            unlink($file_Path);
            return $result->get('ObjectURL');
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo "There was an error uploading the file.\n";
            echo $e->getMessage();
        }
    }

}