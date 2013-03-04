<?php
namespace Incube\Mvc;
/** @author incubatio 
  * @licence GPLv3.0 http://www.gnu.org/licenses/gpl.html
  */

class View {

    /** @var string */
    protected $_path = "";

    /** @var string */
    protected $_view_extention = ".phtml";

    /** @var string */
    protected $_filename;

	/** @var Class_I18n */
	protected $_i18n;

	/** @var string */
	protected $_layout_name;

	/** @var view_helpers */
	protected $_view_helpers = array();

    /** @param array $options */
    public function __construct(array $options = array()) {
        foreach($options as $key => $value) {
            $this->{"_$key"} = $value;
        }
    }

    /** @param string $filename
      * @return string
      */
    public function render($filename = null) {
        if(!$filename) $filename = $this->_filename;
        $filePath = $this->_path . DIRECTORY_SEPARATOR . $filename . $this->_view_extention;
		$contents = $this->_include($filePath);

		if($this->_layout_name) {
			$layoutPath = $this->_layoutPath . DIRECTORY_SEPARATOR . $this->_layout_name . $this->_view_extention;
			$this->contents = $contents;
			$contents = $this->_include($layoutPath);
		}
		return $contents;
    }

    /** @param string $contents
      * @return string */
    public function render_text($contents) {

		if($this->_layout_name) {
			$layoutPath = $this->_layoutPath . DIRECTORY_SEPARATOR . $this->_layout_name . $this->_view_extention;
			$this->contents = $contents;
			$contents = $this->_include($layoutPath);
		}
		return $contents;
    }

    /** @param string $filePath
      * @return string */
	protected function _include($filePath) {
		ob_start();
		if(file_exists($filePath)) include $filePath;
		//else trigger_error("$filePath doesn't exists");
		//TODO:File_Explorer could ensure read files( include + ob_get_clean )
		return ob_get_clean();
	}  


    /** @param string $filename */
    public function set_filename($filename) {
        $this->_filename = $filename;
    }

    /** @param string $filename */
	public function set_layout($filename) {
		$this->_layout_name = $filename;
	}
	
	/** @return bool */
	public function is_layout() {
		return !empty($this->_layout_name);
	}

	/** Clean render by unseting view and layout */
	public function no_render() {
		$this->set_layout(null);
		$this->set_filename(null);
	}

	public function add_view_helper($view_helper) {
		$this->_view_helpers[] = $view_helper;
	}

    /** @param string $method 
	  * @param array
	  * return mixed */
	public function __call($method, $args) {
		foreach($this->_view_helpers as $view_helper) {
			if(method_exists($view_helper, $method)) return call_user_func_array(array($view_helper, $method), $args);
		}
	//	debug_print_backtrace();
		//TODO: Ameliorer le bug tracking
		trigger_error("$method doesn't exists or isn't available from the view's helpers", E_USER_ERROR);
	}


    /** @return string */
    public function get_filename() {
        return $this->_filename;
    }

    /** @return string */
    public function get_path() {
        return $this->_path;
    }

    /** @param string $path 
      * @return View
      */
    public function set_path($path) {
        $this->_path = $path;
        return $this;
    }
}
