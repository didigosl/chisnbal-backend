<?php
namespace Common\Models;


class IArticle extends Model
{

    /**
     *
     * @var integer
     */
    public $article_id;
      
    /**
     *
     * @var integer
     */
    public $article_menu_id;

    /**
     *
     * @var string
     */
    public $alias;

    /**
     *
     * @var string
     */
    public $title;
     
    /**
     *
     * @var string
     */
    public $content;
     
    /**
     *
     * @var string
     */
    public $cover_path;
     
    /**
     *
     * @var string
     */
    public $video_path;

    /**
     *
     * @var string
     */
    public $videos;
     
    /**
     *
     * @var string
     */
    public $audio_path;

     /**
     *
     * @var string
     */
    public $audios;
     
    /**
     *
     * @var string
     */
    public $publish_datetime;
     
    /**
     *
     * @var string
     */
    public $start_datetime;
     
    /**
     *
     * @var string
     */
    public $end_datetime;

    /**
     *
     * @var string
     */
    public $intro;

    /**
     *
     * @var string
     */
    public $author;

    /**
     *
     * @var integer
     */
    public $is_hot;
     
     
    /**
     *
     * @var integer
     */
    public $status;
     
    /**
     *
     * @var integer
     */
    public $like_number;


    static public $attrNames = [
        'article_menu_id'=>'文章分类',
        'title'=>'标题',
        'cover_path'=>'封面图',
        'video_path'=>'视频',
        'audio_path'=>'音频',
        'publish_datetime'=>'发表于',
        'content'=>'内容',
        'start_datetime'=>'开始时间',
        'end_datetime'=>'结束时间',
        'like_number'=>'点赞',
        'status'=>'状态',
        'is_hot'=>'是否热门',
        'intro'=>'摘要',
        'author'=>'作者',
    ];

    static public function getPkCol(){
        return 'article_id';
    }

    public function getSource()
    {
        return 'i_article';
    }

    public static function getStatusContext($var = null) {
        $data = [
            -1 => '删除',
            1 => '未发布',
            10 => '发布',
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }


    public function initialize() {
        $this->useDynamicUpdate(true);
        $this->belongsTo('article_menu_id', 'Common\Models\IArticleMenu', 'article_menu_id', ['alias' => 'Menu']);
    }

    public function beforeCreate(){
        $this->version = 1;
        if(empty($this->publish_datetime)){
            $this->publish_datetime = date('Y-m-d H:i:s');
        }
    }

    public function beforeSave(){
        $this->status = (int)$this->status;
        $this->article_menu_id = (int)$this->article_menu_id;
        $this->like_number = (int)$this->like_number;
        $this->is_hot = (int)$this->is_hot;
    }

    public function beforeDelete(){
        if($this->article_id<100){
            throw new \Exception("这是系统文案，不可删除！", 1);
            
        }
    }

}
