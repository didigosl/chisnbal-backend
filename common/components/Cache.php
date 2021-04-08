<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component ;
use Phalcon\Cache\Backend\Apcu;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as CacheFrontData;

class Cache extends Component {

	static private $_i;

	public $cache;
	public $front;

	public function __construct(){
		$front = new CacheFrontData(
	        [
	            "lifetime" => 172800,
	        ]
	    );

	    // $this->cache = new Apcu(
	    //     $front,
	    //     [
	    //         "prefix" => "app-",
	    //     ]
	    // );
	    $this->cache = new BackFile($front, array(
	        "cacheDir" => realpath(SITE_PATH."/../runtime/cache/").'/'
		));
		// var_dump( SITE_PATH."/../runtime/cache/",realpath(SITE_PATH.'/../runtime/cache/'));exit;
	}

	static public function init(){
		if(!isset(self::$_cache)){
			self::$_i = new Cache();
		}
		return self::$_i;
	}

	public function getSettings(){
		$settings = $this->cache->get('settings');
		if ($settings === null) {
			$tmp_settings = $this->db->fetchAll("SELECT name,value FROM s_setting",\Phalcon\Db::FETCH_ASSOC);
			$settings = [];
			foreach ($tmp_settings as $k => $v) {
				$settings[$v['name']] = $v['value'];
			}

			$this->cache->save('settings',$settings);
		}
		// var_dump($settings);exit;
		return $settings;
	}

	public function getConf(){
		$conf = $this->cache->get('conf');
		if ($conf === null) {
			$tmp_conf = $this->db->fetchAll("SELECT name,value FROM s_conf",\Phalcon\Db::FETCH_ASSOC);
			$conf = [];
			foreach ($tmp_conf as $k => $v) {
				$conf[$v['name']] = $v['value'];
			}

			$this->cache->save('conf',$conf);
		}
		// var_dump($conf);exit;
		return $conf;
    }	
}
