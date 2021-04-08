<?php

namespace Api\Controllers;

use Api\Components\ControllerBase;
use Common\Models\ICategory;
use Common\Models\ISort;
use Common\Libs\Func;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class IndexController extends ControllerBase {


	public function recommendAction(){

        $limit = $this->post['limit'];
        $label = $this->post['label'];

        $params = [];

        $limitSql = '';
        if(is_numeric($limit)){
            $limitSql = ' LIMIT '.$limit;
        }

        $labelSql = '';
        if($label){
            $labelSql = ' AND spu.labels like :labels';
            $params['labels'] = '%,'.$label.',%';
        }

		$categories = ICategory::find([
			'recommend_flag=1',
			'order'=>'seq ASC,category_id ASC'
		]);
//        var_dump($categories);die;

		$data = [];
		if($categories){
			foreach($categories as $Item){

                $itemParams = [
                    'merger'=>'%,'.$Item->category_id.',%',
                    'category_id'=>$Item->category_id
                ];
                $itemParams = array_merge($itemParams,$params);

				$spus = $this->db->fetchAll('SELECT spu.*
					FROM i_goods_spu AS spu 
					JOIN i_spu_category AS sc 
						ON spu.spu_id=sc.spu_id AND sc.seq=1
					JOin i_category as c
						ON sc.category_id=c.category_id
					WHERE (c.category_id=:category_id OR c.merger like :merger) AND spu.status=1 and spu.remove_flag=0 '.$labelSql.'
					ORDER BY onsale_time DESC 
					'.$limitSql,\Phalcon\Db::FETCH_ASSOC,$itemParams);
				foreach($spus as $k=>$v){
					$spus[$k]['cover'] = Func::staticPath($v['cover']);
                    $spus[$k]['price'] = fmtMoney($v['price']);
                    $spus[$k]['price2'] = fmtMoney($v['price2']);
                    $spus[$k]['price3'] = fmtMoney($v['price3']);
                    $spus[$k]['price4'] = fmtMoney($v['price4']);
                    $spus[$k]['price5'] = fmtMoney($v['price5']);
				}
				$data[] = [
					'category_id'=>$Item->category_id,
					'category_name'=>$Item->category_name,
					'recommend_pic'=>Func::staticPath($Item->recommend_pic),
					'list'=>$spus
				];
			}
		}

		$this->sendJSON([
			'data'=>$data
		]);
	}

    public function recommend1Action(){

        $limit = $this->post['limit']?$this->post['limit']:3;
//        $page = $this->post['page']?$this->post['page']:1;
        $label = $this->post['label'];

        $params = [];

        $limitSql = '';
        if(is_numeric($limit)){
            $limitSql = ' LIMIT '.$limit;
        }

        $labelSql = '';
        if($label){
            $labelSql = ' AND spu.labels like :labels';
            $params['labels'] = '%,'.$label.',%';
        }
//        if(!$page){
//            $page=1;
//        }
//        $offset=($page-1)*5;

        //$categories="select * from i_category where recommend_flag=1 order by seq ASC,category_id ASC limit '{$offset}',5";


        $categories = ICategory::find([
            'recommend_flag=1',
            'order'=>'seq ASC,category_id ASC'
        ]);
//        var_dump($categories);die;

        $data = [];
        if($categories){
            foreach($categories as $Item){

                $itemParams = [
                    'merger'=>'%,'.$Item->category_id.',%',
                    'category_id'=>$Item->category_id
                ];
                $itemParams = array_merge($itemParams,$params);

                $spus = $this->db->fetchAll('SELECT spu.*
					FROM i_goods_spu AS spu 
					JOIN i_spu_category AS sc 
						ON spu.spu_id=sc.spu_id AND sc.seq=1
					JOin i_category as c
						ON sc.category_id=c.category_id
					WHERE (c.category_id=:category_id OR c.merger like :merger) AND spu.status=1 and spu.remove_flag=0 '.$labelSql.'
					ORDER BY onsale_time DESC 
					'.$limitSql,\Phalcon\Db::FETCH_ASSOC,$itemParams);
                foreach($spus as $k=>$v){
                    $spus[$k]['cover'] = Func::staticPath($v['cover']);
                    $spus[$k]['price'] = fmtMoney($v['price']);
                    $spus[$k]['price2'] = fmtMoney($v['price2']);
                    $spus[$k]['price3'] = fmtMoney($v['price3']);
                    $spus[$k]['price4'] = fmtMoney($v['price4']);
                    $spus[$k]['price5'] = fmtMoney($v['price5']);
                }
                $data[] = [
                    'category_id'=>$Item->category_id,
                    'category_name'=>$Item->category_name,
                    'recommend_pic'=>Func::staticPath($Item->recommend_pic),
                    'list'=>$spus
                ];
            }
        }

        $this->sendJSON([
            'data'=>$data
        ]);
    }

	public function recommendWithShopAction(){

		$sorts = ISort::find([
			'recommend_flag=1',
			'bind'=>['parent_id'=>$parent_id],
			'order'=>'seq ASC,sort_id ASC'
		]);

		$data = [];
		if($sorts){
			foreach($sorts as $Item){
				$spus = $this->db->fetchAll('SELECT spu.*
					FROM i_goods_spu AS spu 
					JOin i_sort as s
						ON spu.sort_id=s.sort_id
					WHERE s.merger like :sort_id AND spu.status=1
					ORDER BY onsale_time DESC 
					LIMIT 5',\Phalcon\Db::FETCH_ASSOC,['sort_id'=>'%,'.$Item->sort_id.',%']);
				foreach($spus as $k=>$v){
					$spus[$k]['cover'] = Func::staticPath($v['cover']);
                    $spus[$k]['price'] = fmtMoney($v['price']);
                    $spus[$k]['price2'] = fmtMoney($v['price2']);
                    $spus[$k]['price3'] = fmtMoney($v['price3']);
                    $spus[$k]['price4'] = fmtMoney($v['price4']);
                    $spus[$k]['price5'] = fmtMoney($v['price5']);
				}
				$data[] = [
					'sort_id'=>$Item->sort_id,
					'sort_name'=>$Item->sort_name,
					'recommend_pic'=>Func::staticPath($Item->recommend_pic),
					'list'=>$spus
				];
			}
		}

		$this->sendJSON([
			'data'=>$data
		]);
	}

}
