<?php
namespace Common\Components\SuhoExpress;

// 查询
class Query extends Base
{
    // 查询地址
    protected $url = 'https://online.correos.es/servicioswebLocalizacionMI/localizacionMI.asmx';

    // 接口数据
    protected $data;

    // 返回
    protected $res;

    // 包裹号
    public function code($code)
    {
        $this->data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <soapenv:Body>
                 <ConsultaLocalizacionEnviosFases xmlns="ServiciosWebLocalizacionMI/">
                       <XMLin>&lt;?xml version="1.0" encoding="Windows1252"?&gt;&lt;ConsultaXMLin Idioma="1" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchemainstance"&gt;&lt;Consulta&gt;&lt;Codigo&gt;' .strtoupper($code) . '&lt;/Codigo&gt;&lt;/Consulta&gt;&lt;/ConsultaXMLin&gt;</XMLin>
                </ConsultaLocalizacionEnviosFases>
            </soapenv:Body>
        </soapenv:Envelope>';

        return $this;
    }

  
    // 发送
    public function send()
    {
        $res = $this->class->__doRequest($this->data, $this->url, 'ConsultaLocalizacionEnviosFases', 1, 0);
        // 解析xml
        $res = $this->xml($res);

        if(isset($res->Respuestas->DatosIdiomas->DatosEnvios->Datos))
        {
            $this->res = $res->Respuestas->DatosIdiomas->DatosEnvios->Datos;
            return $this;
        }

        return false;
    }

    // 错误 
    public function error()
    {
        if(isset($this->res->Descripcion))
        {
            return true;
        }

        return false;
    }

    // 错误 信息
    public function errorDesc()
    {
        return $this->res->Descripcion;
    }

    // 取出数据
    public function get()
    {
        // 如果是数组，直接返回
        if(is_array($this->res))
        {
            return array_reverse($this->res);
        }

        // 如果不是，转为一多维数组返回
        return [$this->res];
    }

    // 解析xml
    protected function xml($data)
    {
        // 过滤soap
        $xmls = strip_tags($data);
        // 转义xml
        $xmls = htmlspecialchars_decode($xmls);
        // 加载xml
        $xmls = simplexml_load_string($xmls);
        // 解析xml
        return json_decode(json_encode($xmls));  
    }
}
