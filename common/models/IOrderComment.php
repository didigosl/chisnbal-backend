<?php

namespace Common\Models;

use Common\Libs\Func;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class IOrderComment extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $order_comment_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $order_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $content;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $star;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $update_time;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("i_order_comment");
        $this->belongsTo('order_id', 'Common\Models\IOrder', 'order_id', ['alias' => 'Order']);
        $this->belongsTo('user_id', 'Common\Models\IUser', 'user_id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_order_comment';
    }

    static public function getPkCol(){
        return 'order_comment_id';
    }
    
    public function afterCreate(){
        $this->Order->flag = 5;
        $this->Order->save();
    }

    public function beforeSave(){
        $this->order_id = (int)$this->order_id;
        $this->user_id = (int)$this->user_id;

    }

    static public function getComments($spu_id,$page=1,$limit=20){
        $page = $page ? $page : 1;
        $limit = $limit ? $limit : 20;

        $ret = [];
        
        $builder =\Phalcon\Di::getDefault()->get('modelsManager')->createBuilder()
                ->columns(['c.*'])
                ->from(['c'=>'Common\Models\IOrderComment'])
                ->join('Common\Models\IOrderSku','c.order_id=os.order_id','os')
                ->where('os.spu_id=:spu_id:',['spu_id'=>$spu_id])
                ->orderBy('c.create_time DESC');

        $paginator = new PaginatorQueryBuilder(array(
            "builder" => $builder,
            "limit" => $limit,
            "page" => $page,
            'adapter' => 'queryBuilder',
        ));

        $paginate = $paginator->getPaginate();

        $ret = [
            'total'=>$paginate->total_items,
            'total_pages'=>$paginate->total_pages,
            'page_limit'=>$limit,
            'page'=>$page,
        ];
        if($paginate->items){
            foreach($paginate->items as $item){
            
                $ret['list'][] = [
                    'order_comment_id'=>$item->order_comment_id,
                    'content'=>$item->content,
                    'star'=>$item->star,
                    'user_id'=>$item->user_id,
                    'user_name'=>$item->User->name,
                    'avatar'=>Func::staticPath($item->User->avatar),
                    'order_create_time'=>$item->Order->create_time
                ];
            }
        }
        return $ret;
    }

}
