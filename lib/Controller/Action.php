<?php
namespace Incube\Mvc\Controller;
/** @author Incubatio 
  * @depandancies Incubatio_uri, Incube_Action_Exception
  * @licence GPLv3.0 http://www.gnu.org/licenses/gpl.html
  *
  * TODO: Manage contentyp and header in a SingleTime class
  */
use Incube\Base\Pattern\IUri;
class Action {

	/** @var Class_Config **/
	protected $_config;

    /** Refer to the name of the Actions' set
	  * @var String **/
    protected $_name;

    /** @var String **/
    protected $_suffix = 'Controller';

    /** @var Class_View **/
    protected $_view;

    /** @var Class_Connection **/
    protected $_datamodel;

    /** @var Class_Router **/
    protected $_router;

    /** @var Class_uri **/
    protected $_uri;

	/** Refer to the current action which is going to trigger
	  * @var String **/
	protected $_current;

	/** @var array **/
	//TODO: Contenttypes doesn't belong to controller action, but to HTTP class or ??.
	protected $_content_types = array(
		"text"  => "text/plain",
		"html"  => "text/html",
		"rich"  => "text/enriched",
		"css"   => "text/css",
		"css"   => "text/html",
		"js"    => "text/javascript",
		"gif"   => "image/gif",
		"png"   => "image/x-png",
		"jpeg"  => "image/jpeg",
		"tiff"  => "image/tiff",
		"bmp"   => "image/x-ms-bmp",
		"svf"   => "image/vnd.svf",
		"pdf"   => "application/pdf",
		"zip"   => "application/zip",
		"json"  => "application/json",
		"xhtml" => "application/xhtml+xml",
		"xml"	=> "application/xml",
		"latex" => "application/x-latex"
		);  

	/** @var string **/
	protected $_content_type = "html";
	
	/** @var bool **/
	protected $_is_content_typeset;

	/** @param array $options */
	public function __construct(array $options = array()) {
		$this->_name = str_replace($this->_suffix, '', get_class($this));
		$this->init($options);
	}

	//optional
	/** @param Class_Config **/
	public function set_config($config) {
		$this->_config = $config;
	}

	//compulsory
	/** @param Object $router */
	public function set_router($router) {
		$this->_router = $router;
	}

	//compulsory
	/** @param IUri $uri */
	public function set_uri(IUri $uri) {
		$this->_uri = $uri;
	}

	/** @param string $key */
	public function get_param($key) {
		return $this->_uri->get_param($key);
	}

	//optional
	/** @param Object | null $view */
	public function set_view($view) {
		$this->_view = $view;
	}

	/** @param string $name */
	public function set_name($name) {
		$this->_current = $name;
	}

	/** @return string */
	public function get_name() {
		return $this->_current;
	}


	/** Possibilities to add common behavour before every actions */
	public function pre_act() {
	}

	/** Possibilities to add common behavour after every action */
	public function post_act() {
	}

	/** @param array $schemeParams
	  * @param array $params */
	protected function _redirect(array $schemeParams, array $params = array()) {
		$url = $this->_router->formatUrl($schemeParams, $params);
		header("Location: $url");
	}


	/** @param string $controller_name
	  * @param string $action_name
	  * @param array  $params 
    * @return string */
    protected function _call($controller_name, $action_name, array $params = array()) {
        $action = Controller::action_factory($this->_router->get_path("controller") . DIRECTORY_SEPARATOR . $controller_name . 'Controller.php', $action_name, $params);
        return Controller::act($action);
    }

	/** @param array $params */
	public function init(array $params) {
		foreach($params as $key => $param) {
			$this->{"_$key"} = $param;
		}
	}

	/** @param string $action_name */
	public function init_content_type($action_name) {
		// check content-type + little hack to give dynamic response changes
		if(preg_match("/\./", $action_name)) {
			list($action_name, $extention) = explode('.', $action_name);
			if(array_key_exists($extention, $this->_content_type)) {
        // TOFIX: what is that thing below about content_type(...)
				$this->_content_type(array($extention));
				header('Content-type: ' . $this->_content_type[$extention]);
			} else $this->_content_type = array();
		} else {
			$this->_content_type = $this->_uri->get_content_type();	
		}
		return $action_name;
	}

	/** @param string $content_type
	  * @return boolean */
	public function content_is($content_type) {
		return in_array($content_type, $this->_content_type);
	}


	/** Default Render function, format handled by the view
	  * @param string $action_name
	  * @param string $contents 
	  * @return string */
	public function render($action_name, $contents = null) {
		switch(true) {
			case in_array("html", $this->_content_type):
			case in_array("xhtml+xml", $this->_content_type):
			case in_array("xaml+xml", $this->_content_type):
				// Prepare view
				if(isset($this->_view)) {
					//TOTHINK:check usability of controller_name and ActionName inside View
					$this->_view->controller_name = $this->_name;
					$this->_view->action_name = $action_name;
					$view_filename = $this->_view->get_filename();
					if(empty($view_filename)) $view_filename = $this->_name .  DIRECTORY_SEPARATOR . $action_name;
				}
			return $contents ? $this->_view->render_text($contents) : $this->_view->render($view_filename);

			case in_array("json", $this->_content_type):
				if(isset($contents)) return json_encode($contents);

			default:
				throw new ActionException("Please disable render, content-type is not suppported or result is empty");
		}
	}
}
