<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use Phalcon\Exception;

class Image extends Component {

	public $file;
	public $img;

	/*public function __construct($file){
		$this->file = $file;
		if(!$this->img =  \Gregwar\Image\Image::open(SITE_PATH.$this->config->params->uploadDir.$this->file)){
			throw new \Exception('fail to open the image file');
		}
	}
    */

    public function open($file){
        $this->file = $file;
        if(!$this->img =  \Gregwar\Image\Image::open(SITE_PATH.$this->config->params->uploadDir.$this->file)){
            throw new \Exception('fail to open the image file');
        }
    }

    public function create($width,$height){
        $this->img = \Gregwar\Image\Image::create($width,$height);
        return $this;
    }

	public function thumb($params=[],&$ret=[]){

        if(empty($params['suffix'])){
            $params['suffix'] = '';
        }

        if(empty($params['prefix'])){
            $params['prefix'] = '';
        }

        if(empty($params['name'])){
            $params['name'] = '';
        }


        $method = '';
        switch ($params['method']) {
            case 'resize':
            case 'zoomCrop':
            case 'scaleResize':
            case 'cropResize':
                $method = $params['method'];
                break;
            default:
                $method = 'cropResize';
                break;
        }

		$full_upload_dir = SITE_PATH.$this->config->params->uploadDir;
		$fullFile = $full_upload_dir.$this->file;

        if($params['width'] and $this->img->width() > $params['width']){
            $width = $params['width'];
            $height = $params['height'];
            //$height = ceil($this->img->height()*($params['width']/$this->img->width()));
            
        }
        else{
            $width = $this->img->width();
            $height = $this->img->height();
        }

        //如果有指定缩略图保存的目录，则存入指定目录
        //否则存入原图所在目录
        if($params['save_dir']){
            if(!is_dir($full_upload_dir.$params['save_dir'])){
                File::createDir($full_upload_dir,$params['save_dir']);
            }
            $save_dir = $params['save_dir'].'/';
        }
        else{
            $save_dir = dirname($this->file).'/';
        }

        if(!empty($params['name'])){
            $file_name = $params['name'];
        }
        else{
            //获取文件主干名称
            $basename = basename($this->file);
            $mainname = substr($basename,0,-1*strlen(strrchr($basename,'.')));
            $file_name = $params['prefix'].$mainname.$params['suffix'];
        }

        $save_file = $save_dir.$file_name.'.jpg';
        $full_save_file = $full_upload_dir.$save_file;
       
        if(!$this->img->$method($width,$height)->save($full_upload_dir.'/'.$save_file,'jpg',$params['quality']?$params['quality']:80)){
        	throw new \Exception("save thumb image failed");        	
        }

        $ret['width'] = $this->img->width();
        $ret['height'] = $this->img->height();

        return $save_file;
	}


    public function captcha($sid,$code){

        $this->img->fill(0xefefef);
        $file = 'code/'.$sid.'.gif';
        $this->img->saveGif(SITE_PATH.$this->config->params->uploadDir.$file);
        return $this->config->params->uploadDir.$file;
    }
}
