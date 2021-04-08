<?php
namespace W\Controllers;

use Phalcon\Http\Response;
use \Common\Components\Assets;
use W\Components\ControllerBase;
use Common\Models\IShareInfo;

class ShareController extends ControllerBase {

	public function toAction(){

        /* $conf = conf();
        $settings = settings();

        $path = explode('/',$this->request->get('_url'));
        $token = $path[count($path)-1];

        
        $this->view->setVars([
            'android_download_url'=> $settings['android_download_url'] ? $settings['android_download_url'] : $conf['android_download_url'],
            'android_scheme'=> $settings['android_scheme'] ? $settings['android_scheme'] : $conf['android_scheme'],
            'ios_download_url'=> $settings['ios_download_url'] ? $settings['ios_download_url'] : $conf['ios_download_url'],
            'ios_scheme'=> $settings['ios_scheme'] ? $settings['ios_scheme'] : $settings['ios_scheme'],
            'token'=>$token
        ]); */

        $ShareInfo = IShareInfo::findFirst(1);
        if(!$ShareInfo){
            $response = new Response();

            $response->setStatusCode(404, 'Not Found');
            $response->setContent("Sorry, the page doesn't exist");
            $response->send();  
        }
        $this->view->pick('share/index');
        $this->view->setVars([
            'ShareInfo'=>$ShareInfo
        ]); 
		
	}

}