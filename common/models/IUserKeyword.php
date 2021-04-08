<?php

namespace Common\Models;

class IUserKeyword extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_keyword_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $content;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $total;

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
        $this->setSource("i_user_keyword");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'i_user_keyword';
    }

    static public function getPkCol()
    {
        return 'user_keyword_id';
    }

    public function beforeCreate()
    {
        parent::beforeCreate();
        $this->update_time = $this->create_time;
    }
    public function beforeSave()
    {
        $this->user_id = (int)$this->user_id;
        $this->total = (int)$this->total;
    }

    static public function log($user_id, $content)
    {

        $Keyword = self::findFirst([
            'user_id=:user_id: AND content like :content:',
            'bind' => [
                'user_id' => $user_id,
                'content' => $content
            ]
        ]);

        if (!$Keyword) {
            $Keyword = new self;
            $Keyword->user_id = $user_id;
            $Keyword->content = $content;
            $Keyword->total = 0;
        }

        $Keyword->total = $Keyword->total + 1;
        return $Keyword->save();
    }

}
