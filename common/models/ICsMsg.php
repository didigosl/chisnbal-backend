<?php

namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Common\Libs\Func;
use JPush\Client as JPush;

class ICsMsg extends Model
{

    public $cs_msg_id;

    public $cs_session_id;

    public $user_id;

    public $admin_id;

    public $poster;

    public $content;

    public $content_type;

    public $duration;

    public $width;

    public $height;

    public $has_read;

    public $shop_id;

    public $read_time;

    public $create_time;

    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_cs_msg");

        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
        $this->belongsTo('admin_id', 'Common\Models\SAdmin', 'id', ['alias' => 'Admin']);
        $this->belongsTo('cs_session_id', 'Common\Models\ICsSession', 'cs_session_id', ['alias' => 'CsSession']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_cs_msg';
    }

    static public function getPkCol(){
        return 'cs_msg_id';
    }

    public function validation() {

        $validator = self::validator();        
        return $this->validate($validator);        
    }

    static public function validator($cols=[]){

        $validator = new Validation();

        $cols_length = count($cols);
      
        // if($cols_length==0 OR in_array('cs_session_id',$cols)){
        //      $validator->add(
        //         'cs_session_id',
        //         new PresenceOf()
        //     );
        // }

        if($cols_length==0 OR in_array('content',$cols)){
             $validator->add(
                'content',
                new PresenceOf()
            );
        }

        return $validator;
    }

    public function beforeCreate(){
        parent::beforeCreate();
        if(empty($this->shop_id)){
            $this->shop_id = 1;
        }

        if($this->admin_id){
            $this->poster = 'admin';
        }
        else{
            $this->poster = 'user';
        }

        if($this->cs_session_id){
            $Session = ICsSession::findFirst($this->cs_session_id);
        }
        elseif($this->user_id){
            $Session = ICsSession::findFirst([
                'user_id=:user_id:',
                'bind'=>[
                    'user_id'=>$this->user_id
                ]
            ]);
        }
        else{
            throw new \Exception('会话不存',2002);
        }

        if($Session){
            $this->cs_session_id = $Session->cs_session_id;
            if($this->admin_id){
                $Session->admin_id = $this->admin_id;
                
            }
            $Session->save();
        }
        else{
            $Session = new ICsSession;
            $Session->assign([
                'user_id'=>$this->user_id,
                'shop_id'=>$this->shop_id,
            ]);
            if($Session->save()){
                $this->cs_session_id = $Session->cs_session_id;
            }
        }

        $conf = conf();
        if ($conf['enable_push']) {
            if($this->admin_id>0 && $this->CsSession->user_id){
                if($this->CsSession->User){
                    try{
                        $client = new JPush($conf['jiguang_app_key'], $conf['jiguang_secret']);
                        $pusher = $client->push();
                        $pusher->setPlatform('all');
                        $pusher->addAlias($this->CsSession->User->token);
                        $pusher->iosNotification($this->content,[
                            'alert'=>'客服回复',
                            'badge' => '+1',
                        ]);
                        $pusher->androidNotification('test cotnent',[]);
                        $pusher->options([
                            'apns_production'=>true,
                        ]);
                        $pusher->send();

                        //推开发环境
                        /* $client = new JPush($conf['jiguang_app_key'], $conf['jiguang_secret']);
                        $pusher = $client->push();
                        $pusher->setPlatform('all');
                        $pusher->addAlias($this->CsSession->User->token);
                        $pusher->iosNotification('test cotnent',[
                            'alert'=>'客服回复',
                            'badge' => '+1',
                        ]);
                        $pusher->androidNotification($this->content,[]);
                        $pusher->options([
                            'apns_production'=>false,
                        ]);
                        $pusher->send(); */
                    } catch (\Exception $e){
                        
                    }
                    
                }
                
            }
            
        }
        
    }

    public function afterCreate(){
        if($this->content_type=='image'){
            $this->CsSession->lastest_msg = '[图片]';
        }
        elseif($this->content_type=='audio'){
            $this->CsSession->lastest_msg = '[音频]';
        }
        else{
            $this->CsSession->lastest_msg = $this->content;
        }
        
        $this->CsSession->msg_total++;
        if($this->user_id){
            $this->CsSession->user_total++;
            $this->CsSession->lastest_from = 'user';
        }
        if($this->admin_id){
            $this->CsSession->admin_total++;
            $this->CsSession->lastest_from = 'admin';
        }
        $this->CsSession->update_time = $this->create_time;
        $this->CsSession->save();
    }

    public function getFmtContent(){
        $ret = '';
        if($this->content_type=='image' || $this->content_type=='audio'){
            if($this->content){
                $ret = Func::staticPath($this->content);
            }
        }
        else{
            $ret = $this->content;
        }

        return $ret;
    }

    public function adminRead(){
        $db = $this->getDi()->get('db');
        if($this->user_id && !$this->has_read){
            $this->read_time = date('Y-m-d H:i:s');
            $this->has_read = 1;
            // die($this->read_time);
            if($this->save()){
                $admin_unread_total = $db->fetchColumn("SELECT count(1) FROM i_cs_msg where cs_session_id=:cs_session_id AND user_id>0 AND has_read=0",['cs_session_id'=>$this->cs_session_id]);
                $this->CsSession->admin_unread_total = $admin_unread_total;
                $this->CsSession->save();
            }
        }
    }

    public function userRead(){
        $db = $this->getDi()->get('db');
        if($this->admin_id && !$this->has_read){
            $this->read_time = date('Y-m-d H:i:s');
            $this->has_read = 1;
            if($this->save()){
                $user_unread_total = $db->fetchColumn("SELECT count(1) FROM i_cs_msg where cs_session_id=:cs_session_id AND admin_id>0 AND  has_read=0",['cs_session_id'=>$this->cs_session_id]);
                $this->CsSession->user_unread_total = $user_unread_total;
                $this->CsSession->save();
            }
        }
    }
}
