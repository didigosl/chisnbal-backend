<?php
namespace Admin\Components;

use Common\Models\SConf;
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
                    $img = new Image();
                    $img->open($files[$v]['path']);
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
                        $images[$v]['large'] = 'https://pic.manshiguang.it/api/pic/getImg?mark=chisnbal&pic_id='.$result_data->data->pic_id;
                        $images[$v]['mid'] = 'https://pic.manshiguang.it/api/pic/getImg?mark=chisnbal&pic_id='.$result_data->data->pic_id;
                        $images[$v]['small'] = 'https://pic.manshiguang.it/api/pic/getImg?mark=chisnbal&pic_id='.$result_data->data->pic_id;
                    }else{
                        throw new \Exception($result_data->msg, 1);
                    }
                }

                if($file){
                    unlink('.'.$file);
                }
            }
        } catch (\Exception $e){
            throw new \Exception($e->getMessage(), 1);

        }
        return $images;
    }

    static public function uploadAndThumbu($upload_dir,$inputs=[]){
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
                        'quality'=>55,
                    ]);

                    $icon = $img->thumb([
                        'width'=>120,
                        'height'=>120,
                        'suffix'=>'_i',
                        'method'=>'zoomCrop',
                    ]);

                    try{
                        $oss_status = true;
                        if(SERV_ENV=='p'){
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
                        }

                    } catch(\Exception $e){
                        throw new \Exception($e->getMessage(), 1);

                    }

                    if($oss_status){
                        $images[$v]['large'] = DI::getDefault()->get('config')->oss->domain.$large;
                        $images[$v]['mid'] = DI::getDefault()->get('config')->oss->domain.$mid;
                        $images[$v]['small'] = DI::getDefault()->get('config')->oss->domain.$small;
                    }
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
                    $img = new Image();
                    $img->open($files[$v]['path']);
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

                }

                if($file){
                    unlink('.'.$file);
                }
            }
        } catch (\Exception $e){
            throw new \Exception($e->getMessage(), 1);

        }
        return $images;
    }

    /**
     * AWS S3上传文件
     * @param string $file 文件名称
     * @return array
     */
    static public function fileUpload($file){
        require_once './aws-autoloader.php';

        $s3_key=SConf::findFirst(['name=:name:','bind'=>['name'=>'s3_key']]);
        $s3_secret=SConf::findFirst(['name=:name:','bind'=>['name'=>'s3_secret']]);

        $s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => 'eu-central-1', #改为美国西部
            'credentials' => [
                'key'    => $s3_key->value, #访问秘钥
                'secret' => $s3_secret->value #私有访问秘钥
            ]
        ]);
        $bucketName = 'chisnbal'; #存储桶的名字
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



    static public  function pic($url){
        require_once SITE_PATH.'/aws-autoloader.php';
        // $aext = explode('.', $url);
        // $ext = end($aext);
        $ext = 'jpg';
        $pic_name = rand(1000,9999).time() . '.' . $ext;
        $name = SITE_PATH.'/uploads/shop/'. $pic_name;
        if(strpos($url,'http') === false){
            $url = 'https:'.$url;
        }
        $source = file_get_contents($url);
        if(file_put_contents($name,$source)){
//            $images = FileSys::fileUpload('/uploads/shop/'.$pic_name);
            $file = SITE_PATH.'/uploads/shop/'.$pic_name;
            $s3_key=SConf::findFirst(['name=:name:','bind'=>['name'=>'s3_key']]);
            $s3_secret=SConf::findFirst(['name=:name:','bind'=>['name'=>'s3_secret']]);
            $s3 = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => 'eu-central-1', #改为美国西部
                'credentials' => [
                    'key'    => $s3_key->value, #访问秘钥
                    'secret' => $s3_secret->value #私有访问秘钥
                ]
            ]);

            $bucketName = 'chisnbal'; #存储桶的名字
            $file_Path = $file; #要上传的文件的路径
            $key = basename($file_Path);
            try {
                $result = $s3->putObject([
                    'Bucket' => $bucketName,
                    'Key'    => $key,
                    'Body'   => fopen($file_Path, 'r'),
                    'ACL'    => 'public-read',
                ]);
                unlink($file_Path);
                $images = $result->get('ObjectURL');
            } catch (Aws\S3\Exception\S3Exception $e) {
                echo "There was an error uploading the file.\n";
                echo $e->getMessage();
            }

        }else{
            $images = '';
        }

        return $images;
    }



}