<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;

class Assets extends Component {

	public $js = [];

	public $css = [];

	/**
	 * 获取资源内容
	 * @param  [type] $type js or css
	 * @param  [type] $pos  head or foot
	 * @return [type]       [description]
	 */
	public function get($type, $pos) {
		/*if(is_array($this->{$type}[$pos]) and !empty($this->{$type}[$pos])){
				$cacheId = $this->router->getModuleName().'-'.$this->router->getControllerName().'-'.$this->router->getActionName().'.'.$pos.'.'.$type.'.php' ;
			}
			else{
				$cacheId = 'global.'.$pos.'.'.$type.'.php';
			}
		*/
		$content = null;
		if ($content === null) {
			$content = $this->_genarate($type, $pos);
			$content = implode("\n", $content);
			echo $content;

			//$this->di->get('cache')->save();
		} else {
			echo $content;
		}
	}

	/**
	 * @param $type
	 * @param $pos
	 * @return mixed
	 */
	protected function _genarate($type, $pos) {
		$return = [];
		$config = $this->di->get('config');
		$configAssets = require APP_PATH . '/apps/' . $this->router->getModuleName() . '/config/' . $type . '.php';

		if (is_array($this->{$type}[$pos])) {
			$assets = array_merge($configAssets[$pos], $this->{$type}[$pos]);
		} else {
			$assets = $configAssets[$pos];
		}

		foreach ($assets as $key => $value) {
			$setting = explode('::', $value);
			if ($setting[0] == 'inc') {
				$return[$key] = $this->{'_inc' . $type}($setting[2] ? $setting[2] : $setting[1]);
			} elseif ($setting[0] == 'raw') {
				$return[$key] = $this->{'_raw' . $type}($setting[2] ? $setting[2] : $setting[1]);
			}

			if ($setting[2]) {
				$return[$key] = $this->_if($setting[0], $setting[1], $return[$key]);
			}
		}

		return $return;
	}

	/**
	 * @param $content
	 */
	protected function _incJs($content) {
		if (strtolower(substr($content, 0, 4)) == 'http') {
			return '<script src="' . $content . '"></script>';
		}
		else{
			return '<script src="' . $this->di->get('config')->params->staticsPath . $content . '"></script>';
		}
	}

	/**
	 * @param $content
	 * @return mixed
	 */
	protected function _rawJs($content) {
		$return = '<script type="text/javascript">' . "\n";
		$return .= $content;
		$return .= '</script>';
		return $return;
	}

	/**
	 * @param $content
	 * @return mixed
	 */
	protected function _incCss($content) {
		if (strtolower(substr($content, 0, 4)) == 'http') {
			$return = '<link rel="stylesheet" href="' . $content . '" />';
		} else {
			$return = '<link rel="stylesheet" href="' . $this->config->params->staticsPath . $content . '" />';
		}
		return $return;
	}

	/**
	 * @param $content
	 * @return mixed
	 */
	protected function _rawCss($content) {
		$return = '<style type="text/css">' . "\n";
		$return .= $content;
		$return .= '</style>';
		return $return;
	}

	/**
	 * @param $method
	 * @param $condition
	 * @param $content
	 * @return mixed
	 */
	protected function _if($method, $condition, $content) {
		if ('inc' == $method) {
			$return = "<!--[$condition]> \n";
		} elseif ('raw' == $method) {
			$return = "<!--[$condition]> -->\n";
		}

		$return .= $content;

		if ('inc' == $method) {
			$return .= "\n <![endif]-->";
		} elseif ('raw' == $method) {
			$return .= "\n<!-- <![endif]-->";
		}
		return $return;
	}

}
