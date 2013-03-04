<?php
namespace Incube\Mvc;
/** @author Incubatio
  * @depandancies Incube_Pattern_Iuri, Incube_Pattern_IChecker, Incube_Pattern_IFilter, Controller, Controller_Action, Incube_View, Incube_Application_Exception
  * @licence GPL3.0 http://www.gnu.org/licenses/gpl.html
  *  
  * For User: Before construct an Application_MVC Initialise every component common 
  * to your application: Config, uri, DataModel, Internationalisation, session.
  *
  * $application = new Application_MVC:
  * - Init $_uri and resources
  *
  * For User: It's now time to add $_checkers, $_viewHelper and $_filters
  *
  * $application->start():
  * - Init Router Run Checkers (e.g. acl)
  * - Init application component: view, view's helpers, controller
  * give $_resources to controller, run the action 
  *
  * For User: Don't Forget to try catch $application in case of an Exception
  */

use Incube\Base\Pattern\IApplication,
    Incube\Base\Pattern\IUri,
    Incube\Base\DataObject,
    Incube\Web\Router,
    Incube\Events\EventManager,
    Incube\Mvc\Controller,
    Incube\Mvc\View\HtmlHelper;
class Application implements IApplication {

	/** @var string */
	protected $_name;

	/** @var array | DataObject
	 * $_option->config contains application configurations
	 * $_option->config->router can be set to modify default router behavior
	 * $_option->anything will be available as controller attribute */
	protected $_resources;

	/** @var IUri */
	protected $_uri;

    /** @var EventManager */
    protected $_events;

	/** @var string 
	  * Exception handled outside or inside by a controller name contained in $_exceptionHandler */
	protected $_exception_controller = "ExceptionController.php";

    protected $_application_path;

	/** @param string $app_name
	  * @param Incube_Pattern_Iuri $uri
	  * @param array $resources */
	public function __construct($app_name, IUri $uri, array $resources = array()) {
		$this->_name = $app_name;
		$this->_uri = $uri;
		//TOTHINK: parse resources in stdClass ?
		$resources = DataObject::from_array($resources);
        if(!$resources->has('events')) {
            $this->_events = new \Incube\Event\EventManager();
            $resources->set('events', $this->_events); 
        } else $this->_events = $resources->get('event');


        $this->_application_path = $resources->has('application_path') ? $resources->get('application_path') : ROOT_PATH . '/app';
        $this->_resources = $resources;

		// TOTHINK: check the declaration or the accessibility of dynamics var.
		//foreach($resources as $key => $option) {
		//$this->{"_$key"} = $option;
		//}

	}


	public function start() {
		try {
			//TODO: add more customization capabilities.
			// Check for custom application configuration in resources->config
			$router_config =  $this->_resources->has('config') && $this->_resources->get('config')->has('router') 
                ? $this->_resources->get('config')->get('router') : array();
			if (is_array($router_config)) $router_config = DataObject::from_array($router_config);

			// Prepare router
			$router = new Router($this->_application_path, $this->_uri->get_main_params(), $router_config->to_array());
			$router->set_base_url($this->_uri->get_website_base_url());
            $this->_resources->set('router', $router);

			// Prepare the view object
			$view = new View(array('path' => $router->get_path('view'), 'layoutPath' => $router->get_path('layout')));
			$view->add_view_helper(new HtmlHelper($router));
            $this->_resources->set('view', $view);

			// Authorisation check: authentications, Acl, security token ... (possible 401: Unauthorised access)
            $this->_events->trigger('checkers', $this);

			// Prepare Action and check existance of ressource uried (possible 404: Ressource not found)
            Controller::inject($this->_resources->to_array());
			$action = Controller::action_factory($router->get_class_path('controller'), $this->_uri->get_param('action'));

			// Execute Action
			$contents = Controller::act($action);
		} catch (Exception $e) {
			if(!$this->_exception_controller || !file_exists($router->get_path("controller") . DS . $this->_exception_controller)) throw $e;

			$params["e"] = $e;
            Controller::inject($this->_resources);
			$action = Controller::action_factory($router->get_path("controller") . DS . $this->_exception_controller, "index");
			$contents = Controller::act($action);
		}
		// TOTHINK: For better performances and more flexibility, filter should be added from outside the app
		//$this->addFilter(new Incube_Filter_Tag($view));
        $this->_events->trigger('render', $this, array('contents' => $contents));
        if(is_string($contents) && !empty($contents)) echo $contents;
        // TODO: manage rendering
        //echo $view->render();
	}

    public function get_resources() {
        return $this->_resources;
    }
    public function get_resource($key) {
        return $this->_resources->get($key);
    }
    public function set_resources(array $resources) {
		$this->_resources = DataObject::from_array($resources);
        return $this;
    }

//		foreach($this->_checkers as $checker) {
//			if(!$checker->isCheckable($this->_uri->get_params())) throw New Incube_Application_Exception("access refused to this resource, you must be authenticated and have the suficient privileges");

//		foreach(array_reverse($this->_filters) as $key => $value) {
//			$contents = $value->run($contents);
//		}
}
