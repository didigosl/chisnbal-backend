<?php
namespace Common\Components\SuhoExpress;

use SoapClient;

// 基础设置
ini_set('default_socket_timeout', 1000);
ini_set('soap.wsdl_cache_enabled', '1');
ini_set('soap.wsdl_cache_ttl', '100000');

// 接口基础类
class Base
{
    const wsdl = 'https://preregistroenvios.correos.es/?wsdl';
    const url  = 'https://preregistroenvios.correos.es/preregistroenvios';
    const user = 'w0944893';
    const pass = 'kDdicJEk';

    // 接口类
    protected $class;

    // 实例化
    public function __construct()
    {
        $this->class = new SoapClient(self::wsdl, array(
            'trace' => true,
            'exceptions' => false,
            'location' => self::url,
            'login' => self::user,
            'password' => self::pass,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ])
        ));
    }

    // 填充数据
    public function make(&$data, $res)
    {
        foreach($data as $k => $v)
        {
            // 如果存在就合并
            if(isset($res[$k]))
            {
                $data[$k] = $res[$k];
            }
        }
    }

    // 使用class方法
    public function __call($name, $arguments)
    {
        echo '<pre>';
        var_export($this->data);
        echo '</pre>';
        // exit;
        return $this->class->$name($this->data);
    }

    // 输出pdf
    protected function putPDF($name, $pdf)
    {
        // 输出真实目录
        $dir = "/pdf/" . date('Y-m') . '/' . date('d');
        $path = APP_PATH.$dir;
        if(!is_dir($path))
        {
            mkdir($path, 0777, true); 
        }

        // 输出文件 使用真实路径
        $name =  '/' . $name . '.pdf';
        file_put_contents($path . $name, $pdf);

        // 返回路径
        return $dir . $name;
    }

}
