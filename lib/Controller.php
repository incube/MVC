<?php
namespace Incube\Mvc;
/** @author incubatio */
//TOTHINK: maybe add customisation of the static params, maybe remove the params
use Incube\Mvc\Controller\Action;
class Controller {

  /** @var String **/
  protected static $_action_suffix = 'Action';

  /** @var String **/
  protected static $_file_suffix = '.php';

  /** @var Array **/
  protected static $_params = array();

	/** Main method of every action of a controller
	  * @param ControllerException $action_name
	  * @return string */
	public static function act(Action $action) {
		$action_method = $action->get_name() . self::$_action_suffix;
		$action->pre_act();
		$result = $action->$action_method();
		$action->post_act();

		return $action->render($action->get_name(), $result);
	}

	/** @param string $controller_path
	  * @param string $action
	  * @return ControllerException */
	public static function action_factory($controller_path, $action_name) {
		if(!file_exists($controller_path)) throw new ControllerException("Resource not found or does not exists");
		include_once $controller_path;

		$action_classname = basename($controller_path, self::$_file_suffix);

		$action = new $action_classname();
		$action->init(self::$_params);

    // TOFIX: the command below indicate indirectly a dependancy with a Incube_Pattern_IURI object
		$action_name = $action->init_content_type($action_name);
		$action_method = $action_name . self::$_action_suffix;
		if(!method_exists($action, $action_method)) {
			throw new ControllerException("The action you're attempting to join doesn't exists");
		}
		$action->set_name($action_name);
		return $action;
	}

	/** @param array $params */
  public static function inject(array $params) {
    self::$_params = $params;
  }

}
