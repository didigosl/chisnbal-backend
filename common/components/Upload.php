<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use Phalcon\Exception;

class Upload extends Component {

    public $exts = [
        'image' =>  ['jpeg','jpg','png','gif'],
        'zip'   =>  ['zip'],
        'audio' =>  ['mp3'],
        'video' =>  ['mp4','m4v'],
        'apk'   =>  ['apk'],
        'attachment' => ['pdf','zip','xlsx','docx'],
        'excel'=>['xlsx']
    ];

	public function exec($save_dir,$inputs=[],$params=[]){
        $return = [];

		if ($this->request->hasFiles()) {

            $cols = array_keys($inputs);
            foreach ($this->request->getUploadedFiles() as $file) {
                $error = $file->getError();
                if(0 == $error){
                    $ext = strtolower($file->getExtension());

                    if(!empty($inputs)){
                        //只接收指定的表单文件
                        if(!in_array($file->getKey(), $cols)){
                            continue;
                        }

                        //上传的文件后缀名和要求不符
                        if(!in_array($ext, $this->exts[$inputs[$file->getKey()]])){
                            throw new \Exception('上传的文件扩展名不合要求，系统可接受的文件扩展名为：'.implode('、', $this->exts[$inputs[$file->getKey()]]),2001);
                            continue;
                        }
                    }

                    $config = $this->di->get('config');
                    //echo $this->di->get('D')->one($config);
                    $full_upload_dir = SITE_PATH.$config->params->uploadDir;
                    $full_upload_dir = str_replace('//','/',$full_upload_dir);
                    // var_dump($full_upload_dir) ;exit;
                    if(!is_dir($full_upload_dir.$save_dir)){
                        File::createDir($full_upload_dir,$save_dir);
                    }
                    if($file->isUploadedFile()){

                        //如果$params参数中没有指定要保存为的文件名称，则按照文件自身的md5指生成子目录和文件名
                        if(!empty($params['save_name'])){
                            if($params['save_name']===true){
                                $save_name = $file->getName();
                            }
                            else{
                                $save_name = $params['save_name'];
                            }
                            $sub_dir = $params['sub_dir'];
                        }
                        else{
                            $md5 = md5_file($file->getTempName());
                            $sub_dir = File::genSubDir([
                                    'type'=>'md5',
                                    'md5_string'=>$md5,
                                ]);

                            $save_name = $md5.$file->getSize().'.'.$file->getExtension();
                        }
                        // var_dump($sub_dir);exit;

                        // var_dump($save_dir,$sub_dir);
                        $full_sub_dir = File::createDir($full_upload_dir.$save_dir,$sub_dir);
                        // var_dump($full_sub_dir);
                        //var_dump($theUploadDir);exit;
                        $save_file = $full_sub_dir.'/'.$save_name;      

                        // var_dump($full_sub_dir,$save_name,$save_file);exit;
                        if(!$file->moveTo($save_file)){
                            throw new \Exception('上传文件无法移动到目标文件夹',2002);
                        }
                        // $return['save'][$file->getKey()] = substr($save_file, strlen($full_upload_dir));
                        // $return['full'][$file->getKey()] = $save_file;
                        $return[$file->getKey()]['path'] = substr($save_file, strlen($full_upload_dir));
                        $return[$file->getKey()]['full_path'] = $save_file;
                        $return[$file->getKey()]['size'] = filesize($save_file);
                        $return[$file->getKey()]['ext'] = $file->getExtension();
                        $return[$file->getKey()]['name'] = $file->getName();
                    }
                    else{
                        throw new \Exception('不是合法的上传文件',2001);
                    }
                }
                else{
                    switch ($error) {
                        case 1:
                        case 2:
                            $errorMsg = '上传的文件超过了系统要求的上限';
                            break;
                        case 3:
                            $errorMsg = '文件没有完成上传';
                            break;
                        case 4:
                             $errorMsg = '没有上传文件';
                             $errorMsg = '';
                             break;
                        default:
                            $errorMsg = '其他未知错误';
                            break;
                    }
                    if($errorMsg){
                        throw new \Exception($errorMsg,1002);
                        
                    }
                }
                
            }
        }
        return $return;
	}

    public function thumb(){
        
    }

	
}
