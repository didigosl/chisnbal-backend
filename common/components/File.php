<?php
namespace Common\Components;

use Phalcon\Exception;
use Phalcon\Mvc\User\Component;
use Phalcon\Di;

class File extends Component {

	/**
	 * @param array $params
	 * @return mixed
	 */
	static public function genSubDir($params = array()) {

		if ($params['type'] == 'md5') {
			if (empty($params['md5_string'])) {
				throw new \Exception("the params['md5_string'] must not be empty");

			}
			$pieces = str_split($params['md5_string'], $params['length'] ? $params['length'] : 2);
			$pieces = array_slice($pieces, 0, $params['level'] ? $params['level'] : 2);
			$return = implode('/', $pieces);
		}

		if ($params['type'] == 'time') {
			if (empty($params['timestamp'])) {
				throw new \Exception("the params['timestamp'] must not be empty");
			}

			$return = date($params['format'], $params['timestamp']);
		}

		return $return;
	}

	/**
	 * @param $baseDir
	 * @param $subDir
	 * @return mixed
	 */
	static function createDir($baseDir, $subDir) {
		if (!is_dir($baseDir)) {
			throw new \Exception('baseDir "' . $baseDir . '" not a exists dir');
		}

		$uploadDir = $baseDir;

		$subDir = str_replace('\\', '/', $subDir);

		$dirs = explode('/', $subDir);
		$dir = $baseDir;
		foreach($dirs as $d){
			$dir = $dir.'/'.$d;
			if (!is_dir($dir)) {
				if (!mkdir($dir, 0777, true)) {
					throw new \Exception('fail to create sub dir');
				}
			}
		}
		return $dir;
		/*$uploadDir = $subDir ? $uploadDir . $subDir . '/' : $uploadDir;
		if (!is_dir($uploadDir)) {
			if (!mkdir($uploadDir, 0777, true)) {
				throw new \Exception('fail to create sub dir');
			}
		}

		return $uploadDir;*/
	}

	/**
	 * 根据给出的oss url 返回本地服务器存放的图片绝对路径
	 *
	 * @param $img_url
	 * @param array $size_params 返回哪种size的本地图片，如果为空，则返回oss url对应的本地图片
	 */
	static function getLocalImage($img_url, $size_params = []) {
		$return = [];

		$config = DI::getDefault()->get('config');

		if(empty($img_url)){
			throw new \Exception("请指定图片路径", 1);
			
		}

		if(strrpos($img_url, $config->oss['domain'])===false){
			throw new \Exception("图片url不是本系统的oss域名", 1);
		}
		else{
			$local_path = SITE_PATH.'/'.str_replace($config->oss['domain'], $config->params['uploadDir'], $img_url);
		}
		

		$return['origin'] = $local_path;
		if(empty(!$size_params) and is_array($size_params)){		
			foreach ($size_params as $v) {
				$return[$v] = preg_replace('/_[a-z]{1}/', $v, $local_path);
			}
		}

		return  $return;
	}

	static function getExt($path){
		$return = '';
		if(!empty($path) and is_string($path)){
			$arr = explode('.', $path);
			$return = $arr[count($arr)-1];
		}

		return $return;
	}

}
