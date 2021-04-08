<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\IArticle;
use Common\Libs\Func;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ArticleController extends ControllerBase {


	public function listAction(){

		$conditions = [];
		$params = [];

		$article_menu_id = 1;

		if($this->post['article_menu_id']){
			$article_menu_id = $this->post['article_menu_id'];
		}
		else{
			$article_menu_id = 1;
		}

		if($article_menu_id){
			$conditions[] = 'article_menu_id=:article_menu_id:';
			$params['article_menu_id'] = $article_menu_id;
		}


		$conditions[] = 'status=10';

           		$conditionSql = implode(' AND ', $conditions);
                            		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
   

		$limit = $this->post['page_limit'] ? (int)$this->post['page_limit'] : 20;
		$page = $this->post['page'] ? (int)$this->post['page'] : 1;

		$builder = $this->modelsManager->createBuilder()
                ->columns(['article_id,title'])
                ->from('Common\Models\IArticle')
                ->where($conditionSql,$params)
                ->orderBy('article_id asc');
        

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => $limit,
			"page" => $page,
			'adapter' => 'queryBuilder',
		));

		$paginate = $paginator->getPaginate();
		$list = [];
		if($paginate->items){
			foreach($paginate->items as $item){
				$list[] = [
					'article_id'=>$item['article_id'],
					'title'=>$item['title'],
				];
			}
		}

		$this->sendJSON([
			'data'=>[
				'total_pages'=>$paginate->total_pages,
				'page_limit'=>$limit,
				'page'=>$page,
				'list'=>$list,
			]
		]);
	}

	public function getAction(){

		if(!$this->post['article_id']){
			throw new \Exception("必须提供文章ID", 2001);
			
		}

		$Article = IArticle::findFirst($this->post['article_id']);
		if(!$Article){
			throw new \Exception("Error Processing Request", 1);
			
		}

		$this->sendJSON([
			'data'=>[
				'article_id'=>$Article->article_id,
                'title'=>$Article->title,
                'cover_path'=>Func::staticPath($Article->cover_path),
				'content'=>Func::contentStaticPath($Article->content),
			]
		]);
	}

	public function getRegTermAction(){

		//$Article = IArticle::findFirst(1);
		$Article = IArticle::findFirst(["alias='tos'"]);
		if(!$Article){
			throw new \Exception("Error Processing Request", 1);
			
		}

		$this->sendJSON([
			'data'=>Func::contentStaticPath($Article->content)
		]);
	}

	public function getAboutUsAction(){

		$Article = IArticle::findFirst(["alias='about-us'"]);
		if(!$Article){
			throw new \Exception("Error Processing Request", 1);
			
		}

		$this->sendJSON([
			'data'=>Func::contentStaticPath($Article->content)
		]);
	}
	
}
