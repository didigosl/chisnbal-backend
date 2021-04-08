<?php
namespace Admin\Controllers;

use \Common\Components\SuhoExpress\Express;
use \Common\Components\SuhoExpress\Order;
use \Common\Components\SuhoExpress\Haiguan;
use \Common\Components\SuhoExpress\Query;
use Admin\Components\ControllerBase;
use Common\Models\Admin;

class TestController extends ControllerBase {

	public function testAction(){

        $price = Express::fee(1,[
            'length'=>80,
            'width'=>80,
            'height'=>20,
        ]);

        dump($price);
        exit;
    }
    
    public function orderAction(){
       
        $fa = [
            'Apellido1'=>'Ding',
            'Nombre'=>'Yingzhe',
            'Nif'=>'Y0921353X',
            'Telefonocontacto'=>'601309724',
            'Localidad'=>'MADRID',
            'CP'=>'28019',
            'Direccion'=>'Calle ZORZAL ,7'
        ];
        $shou = [
            'Apellido1'=>'xu',
            'Nombre'=>'xian',
            'Telefonocontacto'=>'0861301212121',
            'Localidad'=>'zhejiang',
            'CP'=>'784541',
            'Direccion'=>'xihu bian',
        ];
        $wupin = [
            [
                'Descripcion'=>'36',//物品类型
                'ms'=>'个人用品',
                'Cantidad'=>'2',//数量
                'Pesoneto'=>'100',//重量
                'Valorneto'=>'0500',//价值
            ]
        ];
        $baoxian = '';
        $beizhu = '';


        // 取出价格
        $price = Express::fee(1,[
            'length'=>80,
            'width'=>80,
            'height'=>20,
        ]);
        // 生成代理使用的编号
        $hao = time();

        // 开始下单
        $order = (new Order)->base('S0034', 'CN', $hao);

        // 发货人 收货人 描述 重量 保险 备注
        $order->fa($fa)
            ->shou($shou)
            ->wupin($wupin)
            ->weight($price->weight)
            ->baoxian($baoxian)
            ->beizhu($beizhu);

        // 正式下单
        $order->send();

        // 返回信息
        if($order->error())
        {
            echo 'order error:<br>'.PHP_EOL;
            echo ($order->errorDesc());
        }
        else{
            echo 'order success<br>'.PHP_EOL;
        }

        // 获取海关文件
        $haiguan = (new Haiguan)->code($order->getCode())->send();

        $haipdf = $haiguan->getPDF();
        $compdf = $order->getPDF();

        if($haipdf)
        {
            include('pdfmerger/PDFMerger.php');
            $pdf = new \PDFMerger();
            $str = $pdf->addPDF(substr($haipdf,1), 'all')
                ->addPDF(substr($compdf,1), 'all')
                ->merge('string', substr($compdf,1));
            file_put_contents(substr($compdf,1),$str);
        }
        

        exit;
    }

}