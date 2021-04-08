<?php
namespace Common\Components\SuhoExpress;

// 海关
class Haiguan extends Base
{
    // 接口数据
    protected $data =
    [
        'codCertificado' => ''
    ];

    // 返回数据
    private $res;

    // 包裹号
    public function code($code)
    {
        $this->data['codCertificado'] = $code;

        return $this;
    }


    // 发送
    public function send()
    {
        $this->res = $this->DocumentacionAduaneraCN23CP71Op();

        return $this;
    }
    public function getres()
    {
        return $this->res->Fichero;
    }
    // 取pdf路径
    public function getPDF()
    {
        if(isset($this->res->Fichero))
        {
            return $this->putPDF($this->data['codCertificado'] . '_hai', $this->res->Fichero);
        }

        return null;
    }
}
