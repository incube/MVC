<?php
namespace Incube\Mvc\View;
/** @author incubatio 
  * @depandancies Incube_HTML, Incube_View_Helper_Router
  * @licence GPLv3.0 http://www.gnu.org/licenses/gpl.html
  *
  */
class HtmlHelper extends RouterHelper {

	/** @param string $label
	  * @param mixed $param 
	  *
	  * USAGE: 
	  * link("linkLabel", "controller_action")
	  *       OR
	  * link("linkLabel", array("controller" => $value, "action" => $value) */
	public function link($label, $params) {
		if(!is_array($params)) {
			//TODO: move that params management
			$keys = array("controller", "action");
			$params = explode("_", $params);
			$params = array_combine($keys, $params);
			$paramsUrl["href"] = $this->url($params);
		} else { 
			$paramsUrl = $params;
		}
		return Incube_Encoder_HTML::create_tag("a", $paramsUrl, $label);
	}  

	//TODO: using tag and a helper's function is not the same, check every functionalities ...
	/** @param array $params
	  * @return string */
	public function img(array $params) {
		return Incube_Encoder_HTML::create_tag("img", $params);
	}

	/** @param array $params
	  * @param string JsCode
	  * @return string */
	public function script(array $params, $jsCode = "") {
		return Incube_Encoder_HTML::create_tag("script", $params, $jsCode);
	}

	/** @param array $params
	  * @return string */
	public function js($params) {
		$uri = $this->url($params);
		$params['type'] = "text/javascript";
		return $this->script($params);
	}

	/** @param array $params
	  * @return string */
	public function css($params) {
		$uri = $this->url($params);
        $params['rel']  = "stylesheet";
        $params['type'] ="text/css";
		return Incube_Encoder_HTML::create_tag("link", $params);
	}
}
