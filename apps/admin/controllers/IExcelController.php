<?php
namespace Admin\Controllers;

use Common\Models\IExcel;
use Common\Models\SAdminLog;
use Admin\Components\ControllerAuth;
use Common\Components\Upload;
use Common\Components\File;
use Common\Components\AnalyseExcelRow;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc Excel导入文档
 * @acl shopadmin
 * @aclCustom single_shop,multi_shop
 */
class IExcelController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = 'Excel导入文档';
		$this->view->setVar('controller_name',$this->controller_name);
	}

	/**
	 * @aclDesc 查看记录
	 * @return [type] [description]
	 */
	public function indexAction() {

		$page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$id = $this->request->getQuery('id');

		$conditions = [];
		$params = [];

		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\IExcel')
                ->where($conditionSql,$params)
                ->orderBy(IExcel::getPkCol().' DESC');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 20,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		$this->breadcrumbs[] =[
			'text'=>$this->controller_name,
		];

		$this->view->setVars([
			'action_name'=>'列表',
			'page' => $paginator->getPaginate(),

		]);

	}

	/**
	 * @aclDesc 新增
	 * @return [type] [description]
	 */
	public function createAction(){

		$this->modify();
	}


	protected function form(){

		$id = $this->request->getQuery('id','int');

		$M = IExcel::findFirst($id);
		if(!$M){
			$M = new IExcel;
		}
		
		$this->view->setVars([
			'M'=>$M
			]);		
		
		if($this->request->isAjax()){	
			$this->view->disable();
			$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
			$html = $this->view->getRender($this->dispatcher->getControllerName(),'form');
			$data = [
				'status'=>'1',
				'code'=>'',
				'data'=>$html
			];
			$this->sendJSON($data);
		}
		else{
			$this->view->pick($this->dispatcher->getControllerName().'/form');

		}
	}

	protected function modify(){

		if($this->request->isPost()){
			// var_dump($_POST);exit;
			$Model = new IExcel;
			$Upload = new Upload;
			$upload_dir = 'excels';
			$files = $Upload->exec($upload_dir, ['path'=>'excel']);

			if ($files['path']['path']) {

				$data['path'] = $this->config->params['uploadDir'].$files['path']['path'];
				$data['name'] = $files['path']['name'];
				$data['size'] = $files['path']['size'];
			}
			else{
				throw new \Exception("请正确上传Excel文档", 1);
				
			}
			$data['zip'] = $this->request->getPost('zip');
			// var_dump($data);exit;
			$Model->assign($data);

			try{
				if($Model->save()===false){
					throw new \Exception('保存Excel失败，'.$Model->getErrorMsg(), 1);
					
				}
				else{
					$Model->refresh();
					SAdminLog::add($Model->getSource(),$this->dispatcher->getActionName(),$Model->excel_id,$Model->name);
					$this->flashSession->success('批量导入完成：成功导入了'.$Model->total.'条新的商品数据');
					$this->jump($this->url->get($this->base_url.'/create'));
					
				}
			} catch(\Exception $e){
				// var_dump($e->getMessage());
				// echo $this->d->one($e->getTraceAsString());
				// exit;
				$this->flashSession->error('批量导入发生错误：'.$e->getMessage());
				$this->jump($this->url->get($this->base_url.'/create'));
				exit;
			}

		}
		else{
			$this->form();
		}
	}

	public function testAction(){
		$this->view->disable();
		error_reporting(E_ALL);
		try{
			$path = APP_PATH.'/public/uploads/excels/2c/b8/2cb827b3c55cf556fef4b04eca31d79d11061.xlsx';
			// echo $path;
			$Analyse = new AnalyseExcelRow;
	        $Analyse->analyse($path);
		} catch (\Exception $e){
			var_dump($e->getMessage());
			echo '<pre>';
			var_dump($e->getTrace());
			echo '</pre>';
			exit;
		}
		
        exit;
	}

	public function testUnzipAction(){

		$excel_id = 1;
		$config = $this->config;
		$shop_id = $this->auth->getShopId();
		$zip = APP_PATH.'/public/uploads/1/zip/e8/d7/desktop.zip';
		echo $zip;
		// exit;
		$ZipArchive = new \ZipArchive;
        $res = $ZipArchive->open($zip);
        if ($res === TRUE) {
        	$shop_dir = $shop_id ? 'shop'.$shop_id.'/' : '';
	        $base_dir = APP_PATH;
	        $images_dir = 'uploads/'.$shop_dir.$excel_id;
	        // var_dump($base_dir.$images_dir);exit;
	        if(!file_exists($base_dir.$images_dir)){
	            File::createDir($base_dir,$images_dir);
	        }
	        $ZipArchive->extractTo($base_dir.$images_dir);
	        $ZipArchive->close();
	        var_dump($base_dir.$images_dir);exit;
        }
        else{
        	 throw new \Exception("解压图片压缩包失败:".$ZipArchive->getStatusString(), 1);
        	 exit;
        }
        
	}


	public function testImagesAction(){
		$full_images_dir = APP_PATH.'/public/uploads/shop1/9';
		$images = [];
		if(file_exists($full_images_dir)){
            $Dir = dir($full_images_dir);
            while (false !== ($name = $Dir->read())) {
                if($name!='.' && $name!='..'){
                    $arr = explode('-', $name);
                    var_dump($arr);
                    // exit;
                    if(!empty($arr[0] && !empty($arr[1]))){
                        if(strpos($arr[1], 'cover')===0){
                            $images[$arr[0]]['cover'] = $images_dir.'/'.$name;
                        }
                        elseif(strpos($arr[1], 'p')===0){
                            $images[$arr[0]]['pics'][] = $images_dir.'/'.$name;
                        }
                    }
                    else{
                        throw new \Exception("图片命名错误", 1);
                        
                    }
                    
                }
                
            }

            var_dump($images);
        }
        exit;
	}


}