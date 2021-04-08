<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\IShare;
use Common\Libs\Func;
use Endroid\QrCode\QrCode;

class ShareController extends ControllerAuth {

	public function createAction(){

		$url = Func::staticPath('/w/share/to/'.$this->User->token);
		$qrcode_path = '/uploads/qrcode/'.$this->User->token.'.png';
		if(!file_exists(SITE_PATH.$qrcode_path)){

			if(!is_dir(SITE_PATH.'/uploads/qrcode')){
                mkdir(SITE_PATH.'/uploads/qrcode',0777);
			}
			
			$QR = new QrCode();
            $QR ->setText($url)
                ->setSize(500)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setImageType(QrCode::IMAGE_TYPE_PNG)
			;
			
			$QR->save(SITE_PATH.$qrcode_path);
		}

		$qrcode = Func::staticPath('/uploads/qrcode/'.$this->User->token.'.png');

		$this->sendJSON([
			'data'=>[
				'url'=>$url,
				'qrcode'=>$qrcode
			]
		]);

		
	}
	
}
