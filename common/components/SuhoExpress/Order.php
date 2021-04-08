<?php
namespace Common\Components\SuhoExpress;

// 下单类
class Order extends Base
{
    // 存储代理编号
    public $hao;

    // 保险金额
    public $baoxian;

    // 接口数据
    protected $data =
    [
        "FechaOperacion" => "",
        "CodEtiquetador" => "534H",
        "Care"           => "000000",
        "ModDevEtiqueta" => "2",

        // 发件人信息
        "Remitente" => [
            // 姓名
            "Identificacion" => [
                "Nombre"    => "",
                "Apellido1" => "",
                "Nif"       => ""
            ],
            // 地址等
            "DatosDireccion" => [
                "Direccion" => "",
                "Localidad" => ""
            ],
            // 邮编
            "CP" => '',
            // 电话
            "Telefonocontacto" => '',
            // 短信
            "DatosSMS" => [
                "NumeroSMS" => '',
                "Idioma"    => 1
            ]
        ],

        // 收件人信息
        "Destinatario" => [
            // 姓名
            "Identificacion"   => [
                "Nombre"    => "",
                "Apellido1" => ""
            ],
            "DatosDireccion"   => [
                "Direccion" => "",
                "Localidad" => ""
            ],
            "DatosDireccion2" => array(

                "Direccion" => "",
                "Localidad" => ""
            ),
            "Pais"             => '',
            "CP"               => '',
            "ZIP"              => '',
            "Telefonocontacto" => ""
        ],

        "Envio" => [
            "CodProducto"       => "",
            "ReferenciaCliente" => "",
            "TipoFranqueo"      => "FP",
            "ModalidadEntrega"  => "ST",
            "Pesos"             => [
                "Peso" => [
                    "TipoPeso" => 'R',
                    "Valor"    => ''
                ],
            ],

            'Observaciones1'    => '',

            "InstruccionesDevolucion" => "D",

            'ValoresAnadidos'   => [
                'SeguroLI'        => '',
                'ImporteSeguroLI' => ''
            ],

            'Aduana' => [
                "TipoEnvio"    => 3,
                "DescAduanera" => [
                    "DATOSADUANA" => []
                ]
            ]
        ]
    ];

    // 返回数据
    private $res;

    // 基本             寄件类型 国家代码 代理编号
    public function base($code, $pais, $hao)
    {
        $this->data['Envio']['CodProducto'] = $code;
        $this->data['Destinatario']['Pais'] = $pais;
        $this->data['Envio']['ReferenciaCliente'] = $hao;
        $this->hao = $hao;
        return $this;
    }

    // 发件人
    public function fa($data)
    {
        $this->make($this->data['Remitente'], $data);
        $this->make($this->data['Remitente']['Identificacion'], $data);
        $this->make($this->data['Remitente']['DatosDireccion'], $data);
        $this->data['Remitente']['DatosSMS']['NumeroSMS'] = $this->data['Remitente']['Telefonocontacto'];

        return $this;
    }

    // 收件人
    public function shou($data)
    {
        $this->make($this->data['Destinatario'], $data);
        $this->make($this->data['Destinatario']['Identificacion'], $data);
        $this->make($this->data['Destinatario']['DatosDireccion'], $data);
        $this->data['Destinatario']['ZIP'] = $this->data['Destinatario']['CP'];
        if($this->data['Destinatario']['Pais'] == 'PT')
        {
        	$this->data['Destinatario']['CP']  = '';
        }
		
        return $this;
    }

    // 重量
    public function weight($data)
    {
        $this->data['Envio']['Pesos']['Peso']['Valor'] = $data * 1000;

        return $this;
    }

    // 物品描述
    public function wupin($data)
    {
        // 处理金额
        foreach((array) $data as $k => $v)
        {
            if(isset($v['Valorneto']))
            {
                $data[$k]['Valorneto'] = $this->price($v['Valorneto']);
            }
        }

        $this->data['Envio']['Aduana']['DescAduanera']['DATOSADUANA'] = $data;

        return $this;
    }

    // 保险
    public function baoxian($data)
    {
        // 金额处理
        if(isset($data['ImporteSeguroLI']) && $data['SeguroLI'] == 'S')
        {
            $this->baoxian = $data['ImporteSeguroLI'] = $this->price($data['ImporteSeguroLI']);
        }

        $this->make($this->data['Envio']['ValoresAnadidos'], $data);

        return $this;
    }

    // 备注
    public function beizhu($data)
    {
        $this->data['Envio']['Observaciones1'] = $data;

        return $this;
    }

    // 发送
    public function send()
    {
        $this->res = $this->PreRegistro();
    }

    // 取返回
    public function res()
    {
        return $this->res;
    }

    // 取回错误
    public function error()
    {
        if(isset($this->res->BultoError->Error))
        {
            return true;
        }

        if(isset($this->res->faultcode))
        {
            return true;
        }


        return false;
    }

    // 取错误信息
    public function errorDesc()
    {
        if(isset($this->res->BultoError->DescError))
        {
            return $this->res->BultoError->DescError;
        }
        return '邮局接口出现错误，请稍后尝试';
    }
    // 取返回
    public function getres()
    {
        return $this->res->Bulto->Etiqueta->Etiqueta_pdf->Fichero;
    }
    // 取pdf文件路径
    public function getPDF()
    {
        return $this->putPDF($this->getCode(), $this->res->Bulto->Etiqueta->Etiqueta_pdf->Fichero);
    }

    // 取订单号
    public function getCode()
    {
        return $this->res->Bulto->CodEnvio;
    }

    // 取代理号
    public function getHao()
    {
        return $this->hao;
    }

    // 获取保险金额
    public function getBaoxian()
    {
        return $this->baoxian / 100;
    }

    // 获取发件人
    public function getName()
    {
        return $this->data['Remitente']['Identificacion']['Apellido1'] . ' ' . $this->data['Remitente']['Identificacion']['Nombre'];
    }

    // 获取发件人电话
    public function getTel()
    {
        return $this->data['Remitente']['Telefonocontacto'];
    }

    // 获取收人
    public function getShouName()
    {
        return $this->data['Destinatario']['Identificacion']['Apellido1'] . ' ' . $this->data['Destinatario']['Identificacion']['Nombre'];
    }

    // 获取收件人电话
    public function getShouTel()
    {
        return $this->data['Destinatario']['Telefonocontacto'];
    }

    // 金额处理
    public function price($price)
    {
        $price = round($price, 2) * 100;
        return str_pad($price, 6, 0, STR_PAD_LEFT);
    }
}
