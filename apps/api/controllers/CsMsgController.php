<?php

namespace Api\Controllers;

use Api\Components\ControllerAuth;
use Common\Models\ICsSession;
use Common\Models\ICsMsg;
use Common\Libs\Func;
use Common\Components\ValidateMsg;
use Common\Components\Upload;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class CsMsgController extends ControllerAuth {

	public function createAction(){

		$data = [
            'content'=>$this->post['content'],
		];	

		$Msg = new ICsMsg;
        $data['user_id'] = $this->User->user_id;
        
        if ($this->request->hasFiles()) {

            $Upload = new Upload;
            $files = $Upload->exec('cs', ['image' => 'image', 'audio' => 'audio']);
            if ($files['image']) {
                $image = '/uploads/' . $files['image']['path'];
            }

            if ($files['audio']) {
                $audio = '/uploads/' . $files['audio']['path'];
            }
        }

        if($image){
            $data['content'] = $image;
            $data['content_type'] = 'image';
            $data['width'] = (int)$this->post['width'];
            $data['height'] = (int)$this->post['height'];
        }
        elseif($audio){
            $data['content'] = $audio;
            $data['content_type'] = 'audio';
            $data['duration'] = (int)$this->post['duration'];
        }
        else{
            $data['content_type'] = 'text';
        }

        if(empty($data['content'])){
            throw new \Exception('请填写消息内容');
        }
		$this->log($data);
		$Msg->assign($data);
		if($Msg->save()){
			$this->sendJSON([]);;
		}
		else{

			throw new \Exception($Msg->getErrorMsg(), 2001);
			
		}

    }
    
    public function listAction(){

        $cs_msg_id = $this->post['cs_msg_id'];
        $limit = $this->post['page_limit'] ? (int)$this->post['page_limit'] : 20;
        $page = $this->post['page'] ? (int)$this->post['page'] : 1;
        
        $Session = ICsSession::findFirst([
            'user_id=:user_id:',
            'bind'=>[
                'user_id'=>$this->User->user_id,
            ]
        ]);

        $conditions = [];
		$params = [];

        if(!$Session){
            $this->sendJSON([
                'data'=>[
                    'total_pages'=>0,
                    'page_limit'=>$limit,
                    'page'=>1,
                    'list'=>[],
                ]
            ]);
        }
        else{

            $conditions[] = 'cs_session_id=:cs_session_id:';
            $params['cs_session_id'] = $Session->cs_session_id;

            if($cs_msg_id){
                $conditions[] = 'cs_msg_id>:cs_msg_id:';
                $params['cs_msg_id'] = $cs_msg_id;
            } 

            $conditionSql = implode(' AND ', $conditions);
            $conditionSql = $conditionSql ? $conditionSql : ' 1 ';

            $builder = $this->modelsManager->createBuilder()
                    ->from('Common\Models\ICsMsg')
                    ->where($conditionSql,$params)
                    ->orderBy('cs_msg_id DESC');            

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
                    $item_data = $item->toArray();
                    $item_data['content'] = $item->getFmtContent();
                    unset($item_data['has_read'],$item_data['read_time']);
                    $item_data['user_name'] = $item->User->name;
                    $item_data['admin_name'] = $item->Admin->username;
                    $item_data['user_avatar'] = $item->User->avatar ? Func::staticPath($item->User->avatar) : '';
                    $item_data['admin_avatar'] = $this->config->params->staticsPath.'back/avatars/kf.png';
                    
                    $list[] = $item_data;
                }
            }

            $cs_msg_id = $this->db->fetchColumn("SELECT cs_msg_id FROM i_cs_msg WHERE cs_session_id=:cs_session_id ORDER BY cs_msg_id DESC",[
                'cs_session_id'=>$Session->cs_session_id
            ]);

            $this->sendJSON([
                'data'=>[
                    'cs_msg_id'=>$cs_msg_id,
                    'cs_session_id'=>$Session->cs_session_id,
                    'total_pages'=>$paginate->total_pages,
                    'page_limit'=>$limit,
                    'page'=>$page,
                    'list'=>$list,
                ]
            ]);
        }
		
    }
}
