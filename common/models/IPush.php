<?php
namespace Common\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Exception;
use JPush\Client as JPush;

class IPush extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $push_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $platform;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $audience;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $content;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $err;

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=11, nullable=false)
     */
    public $err_code;

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
        $this->setSource("i_push");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_push';
    }

    static public function getPkCol(){
        return 'push_id';
    }
    

    public function beforeCreate()
    {
        parent::beforeCreate();
        $this->status = 1;
        if (empty($this->platform)) {
            $this->platform = 'all';
        }

        $conf = $this->getDi()->get('conf');
        // var_dump($conf['jiguang_app_key'], $conf['jiguang_secret']);exit;
        if ($conf['enable_push']) {
            //推生产环境
            $client = new JPush($conf['jiguang_app_key'], $conf['jiguang_secret']);
            $pusher = $client->push();
            $pusher->setPlatform($this->platform);
            $pusher->addAllAudience();
            // $pusher->setNotificationAlert($this->content);
            $pusher->iosNotification($this->content,[
                'badge' => '+1',
            ]);
            $pusher->androidNotification($this->content,[]);
            $pusher->options([
                'apns_production'=>true,
            ]);
            try {
                $pusher->send();
                $this->status = 2;
            } catch (\JPush\Exceptions\JPushException $e) {

                $this->status = -1;
                $this->err = $e->getMessage();
                $this->err_code = $e->getCode();
            }

            //推开发环境
            $client = new JPush($conf['jiguang_app_key'], $conf['jiguang_secret']);
            $pusher = $client->push();
            $pusher->setPlatform($this->platform);
            $pusher->addAllAudience();
            // $pusher->setNotificationAlert($this->content);
            $pusher->iosNotification($this->content,[
                'badge' => '+1',
            ]);
            $pusher->androidNotification($this->content,[]);
            $pusher->options([
                'apns_production'=>false,
            ]);
            try {
                $pusher->send();
                $this->status = 2;
            } catch (\JPush\Exceptions\JPushException $e) {

                $this->status = -1;
                $this->err = $e->getMessage();
                $this->err_code = $e->getCode();
                // var_dump($e->getMessage(),$e->getCode());exit;
            }
        }

    }

    static public $attrNames = [
        'platform' => '平台',
        'audience' => '接收人',
        'content' => '内容',
        'status' => '状态',
        'create_time' => '发送时间'
    ];

    public static function getStatusContext($var = null)
    {
        $data = [
            1 => '待发送',
            2 => '已发送',
            -1 => '失败'
        ];
        if ($var !== null) {
            $return = $data[$var] ? $data[$var] : '';
        } else {
            $return = $data;
        }
        return $return;
    }
}