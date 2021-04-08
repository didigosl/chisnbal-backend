<?php
namespace Common\Components\Widgets;

use Common\Components\Widget;
use Phalcon\Exception;
// use Phalcon\Mvc\View\Simple as SimpleView;

class Grid extends Widget {

	protected $viewFile = 'grid';
	protected $hideBtnCol = 0;	//是否隐藏按钮列
	protected $hidePage = 0;	//是否隐藏页码
	protected $hidePageStat = 0;	//是否隐藏页码
	protected $defaultButtons = ['view','update','delete'];
	protected $defaultButtonsCfg = [
		'view'=>[
			'title'=>'查看',
			'url'=>'view',
			'url_param'=>'id',
			'type'=>'ajax',// ajax or href
			'iconCss'=>'fa-eye',
			'btnCss'=>'btn-inverse viewBtn',
		],
		'update'=>[
			'title'=>'更新',
			'url'=>'update',
			'url_param'=>'id',
			'type'=>'ajax',
			'iconCss'=>'fa-pencil-square-o',
			'btnCss'=>'btn-yellow formBtn',
		],
		'delete'=>[
			'title'=>'删除',
			'url'=>'delete',
			'url_param'=>'id',
			'type'=>'ajax',
			'iconCss'=>'fa-trash-o',
			'btnCss'=>'btn-danger deleteBtn'
		],
	];
	public $defaultFormType = 'ajax';
	public $rowCheckbox = false;

	public $buttons = [];
	public $buttonsCfg = [];
	public $bottomButtons = [];
	public $vars;	//外部传入的其他变量
	public $primeData = '';	//当一条数据来自多个数据表时，需要指名一个主要数据源
	public $action = 'index';	//当前默认的页面action


	public function initilize($params=[]){
		$params = parent::initilize($params);
		foreach ($params['buttons'] as $name) {
			$params['buttonsCfg'][$name]['title'] = $params['buttonsCfg'][$name]['title'] ? $params['buttonsCfg'][$name]['title'] : $this->defaultButtonsCfg[$name]['title'];
			$params['buttonsCfg'][$name]['url'] = $params['buttonsCfg'][$name]['url'] ? $params['buttonsCfg'][$name]['url'] : $this->defaultButtonsCfg[$name]['url'];
			$params['buttonsCfg'][$name]['url_param'] = $params['buttonsCfg'][$name]['url_param'] ? $params['buttonsCfg'][$name]['url_param'] : ($this->defaultButtonsCfg[$name]['url_param']? $this->defaultButtonsCfg[$name]['url_param'] : 'id');
			$params['buttonsCfg'][$name]['type'] = $params['buttonsCfg'][$name]['type'] ? $params['buttonsCfg'][$name]['type'] : $this->defaultButtonsCfg[$name]['type'];
			$params['buttonsCfg'][$name]['iconCss'] = $params['buttonsCfg'][$name]['iconCss'] ? $params['buttonsCfg'][$name]['iconCss'] : $this->defaultButtonsCfg[$name]['iconCss'];
			$params['buttonsCfg'][$name]['btnCss'] = $params['buttonsCfg'][$name]['btnCss'] ? $params['buttonsCfg'][$name]['btnCss'] : $this->defaultButtonsCfg[$name]['btnCss'];

			
		}
		$params['hideBtnCol'] = $params['hideBtnCol'] ? $params['hideBtnCol'] : $this->hideBtnCol;	
		$params['formType'] = $params['formType'] ? $params['formType'] : $this->defaultFormType;
		$params['rowCheckbox'] = $params['rowCheckbox'] ? $params['rowCheckbox'] : $this->rowCheckbox;
		$params['bottomButtons'] = $params['bottomButtons'] ? $params['bottomButtons'] : $this->bottomButtons;	
		$params['action'] = $params['action'] ? $params['action'] : $this->action;
		
		return $params;
	}

}
