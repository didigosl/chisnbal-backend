<?php
namespace Admin\Controllers;

use Common\Models\ICsMsg;
use Common\Models\ICsSession;
use Admin\Components\ControllerAuth;
use Common\Libs\Func;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Exception;

/**
 * @aclDesc 客服对话
 * @acl shopadmin,superadmin
 * @aclCustom super,single_shop
 */
class ICsMsgController extends ControllerAuth {

	public function initialize(){
		parent::initialize();		

		$this->controller_name = '客服会话';
		$this->view->setVar('controller_name',$this->controller_name);
    }
    
    public function indexAction(){
        $user_id = $this->request->getQuery('user_id','int');
        $cs_session_id = $this->request->getQuery('id','int');
        $page = $this->request->getQuery("p", "int");
        $page = $page ? $page : 1;
        
        if(empty($cs_session_id)){
           
            $CsSession = ICsSession::findFirst([
                'shop_id=:shop_id: AND user_id=:user_id:',
                'bind'=>[
                    'shop_id'=>$this->auth->getShopId(),
                    'user_id'=>$user_id
                ]
            ]);

            if(!$CsSession){
                $Admin = $this->auth->getUser();
                $CsSession = new ICsSession;
                $CsSession->assign([
                    'shop_id'=>$this->auth->getShopId(),
                    'user_id'=>$user_id,
                    'admin_id'=>$Admin->id,
                ]);
                if(!$CsSession->save()){
                    throw new \Exception('发起对话失败 '.$CsSession->getErrorMsg());
                }
            }

            $cs_session_id = $CsSession->cs_session_id;
        }
        else{
            $CsSession = ICsSession::findFirst($cs_session_id);
            if(!$CsSession){
                throw new \Exception('对话不存在');
            }
        }

        $username = $CsSession->User->phone;
        if($CsSession->User->name){
            $username .= '('.$CsSession->User->name.')';
        }

        $this->view->setVars([
			'controller_name'=>$this->controller_name,
			'action_name'=>'和【'.$username.'】的对话',
			'vars' => [
                'cs_session_id'=>$cs_session_id,
                'page'=>$page,
            ],
		]);
    }

    public function queryAction(){
        $cs_session_id = $this->request->getQuery('cs_session_id','int');
        $cs_msg_id = $this->request->getQuery('cs_msg_id');
        // $cs_msg_id = 1;
        $page = $this->request->getQuery("p", "int");
		$page = $page ? $page : 1;

		$conditions = [];
        $params = [];

        if($cs_session_id){
            $conditions[] = 'cs_session_id=:cs_session_id:';
		    $params['cs_session_id'] = $cs_session_id;
        } 
        
        if($cs_msg_id){
            $conditions[] = 'cs_msg_id>:cs_msg_id:';
		    $params['cs_msg_id'] = $cs_msg_id;
        }        


		$conditionSql = implode(' AND ', $conditions);
		$conditionSql = $conditionSql ? $conditionSql : ' 1 ';
		
		$builder = $this->modelsManager->createBuilder()
                ->columns('*')
                ->from('Common\Models\ICsMsg')
                ->where($conditionSql,$params)
                ->orderBy(ICsMsg::getPkCol().' desc');

		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $builder,
			"limit" => 50,
			"page" => $page,
			'adapter' => 'queryBuilder',
        ));

        $list = [];
        $paginate = $paginator->getPaginate();
        foreach($paginate->items as $k => $item){
            $item->adminRead();
            $list[$k] = $item->toArray();
            $list[$k]['content'] = $item->getFmtContent();
            if($item->user_id){
                $list[$k]['user_name'] = $item->User->name.'('.$item->User->phone.')';
                $list[$k]['user_avatar'] = $item->User->avatar ? Func::staticPath($item->User->avatar) : '';
            }
            if($item->admin_id){
                $list[$k]['admin_name'] = $item->Admin->username;
            }
            
        }

        // $list = array_reverse($list);

        $cs_msg_id = $this->db->fetchColumn("SELECT cs_msg_id FROM i_cs_msg WHERE cs_session_id=:cs_session_id ORDER BY cs_msg_id DESC",[
            'cs_session_id'=>$cs_session_id
        ]);


        $this->sendJSON([
            'data'=>[
                'list'=>$list,
                'total_pages'=>$paginate->total_pages,
                'cs_msg_id'=>$cs_msg_id,
                'cs_session_id'=>$cs_session_id,
                // 'total_pages'=>10

            ]
        ]);
    }

    public function createAction(){
        if($this->request->isPost()){
            $data['cs_session_id'] = $this->request->getPost('cs_session_id');
            $data['content'] = $this->request->getPost('content');
            $data['content_type'] = $this->request->getPost('content_type');
            $data['content_type'] = $data['content_type']? $data['content_type'] : 'text';
            $data['width'] = (int)$this->request->getPost('width');
            $data['height'] = (int)$this->request->getPost('height');

            $Admin = $this->auth->getUser();
            $data['admin_id'] = $Admin->id;

            $Msg = new ICsMsg;
            $Msg->assign($data);

            if($Msg->save()){
                $this->sendJSON([
                    'status'=>1,
                    'code'=>0,
                    'msg'=>'发送成功'
                ]);
            }
            else{
                $this->sendJSON([
                    'status'=>1,
                    'code'=>0,
                    'msg'=>'发送成功'
                ]);
            }
        }
    }

    public function hasUnreadAction(){
        $this->db->execute("UPDATE i_cs_session set admin_unread_total=(SELECT count(1) FROM i_cs_msg WHERE i_cs_msg.cs_session_id=i_cs_session.cs_session_id AND user_id>0 AND has_read=0)");
        $has_unread = $this->db->fetchColumn("SELECT count(1) as total FROM i_cs_msg WHERE user_id>0 AND has_read=0");
        $this->sendJSON([
            'data'=>$has_unread
        ]);
    }

}