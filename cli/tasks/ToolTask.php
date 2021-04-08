<?php
use \Common\Models\IUser;
use \Common\Models\ICategory;
use \Common\Models\IGoodsSpu;
use \Common\Models\ISpuCategory;
use \Common\Models\IArea;

use \Phalcon\Paginator\Factory;

class ToolTask extends \Phalcon\Cli\Task
{

    public function initialize(){

    }

    public function testAction(){
    		echo 'test'.PHP_EOL;
    }

    public function fakeAction(){
       
  			$users = IUser::find();
  			foreach($users as $User){
  					if(!$User->money){
  							$User->money = 10000;
  							$User->save();
  					}
  					$User->genFakeData();
  			}
    }

    public function repairCateAction(){
        $Cates = ICategory::find('parent_id=0');
        foreach($Cates as $Cate){
            $Cate->save();
            if($Cate->sons){
                foreach($Cate->sons as $Son){
                $Son->save();
            }
          }
        }
    }

   
    public function updateSpuCategoryAction(){
        $list = ISpuCategory::find();
        foreach($list as $Spu){

            $Spu->save();
        }

    }

    public function genUserTokenAction(){
        $list = IUser::find([
            'email is not null OR email=""'
        ]);
        foreach($list as $User){
            $User->token = $User->genToken();
            $User->save();
        }

    }

    public function genAreaNameFirstLetterAction(){

        $builder = $this->modelsManager->createBuilder()->from('\Common\Models\IArea');
        $letters = range('A','Z');
        $p = 1;
        do{
            $options = [
                'builder' => $builder,
                'limit'   => 20,
                'page'    => $p,
                'adapter' => 'queryBuilder',
            ];
    
            $paginator = Factory::load($options);
            $page = $paginator->getPaginate();

            foreach($page->items as $item){

                $first_letter  = $item->getFirstLetter();
                echo $item->area_id.':'.$first_letter.PHP_EOL;
                if(in_array($first_letter,$letters)){
                    $item->first_letter = $first_letter;
                    $ret = $item->save();
                }
               
            }

            $p = $page->next;
        } while(  $page->current < $page->total_pages );
        

        
    }

}