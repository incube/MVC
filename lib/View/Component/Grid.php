<?php
namespace Incube\View\Component;
/** @author incubatio 
  * @depandancy Incube_HTML
  * @licence GPLv3.0 http://www.gnu.org/licenses/gpl.html
  */

use Incube\Encoder\HTML;
class Grid {
	
	/** @var array */
    protected $_data = array();

	/** @var array */
    protected $_columns = array();

    public function __construct() {

    }

    /** @param array $data */
    public function set_data(array $data) {
        $this->_data = $data;
    }

    /** @param array $columns */
    public function set_columns(array $columns) {
        $this->_columns = $columns;
    }

    /** @param string $action_url
      * @param string $action_label
      * @param array $params
      * @param string $column_title */
    public function add_row_action($action_url, $action_label, array $params = array(), $column_title = "actions") {
        if(!in_array($column_title, $this->_columns)) $this->_columns[] = $column_title;
        foreach($this->_data as $key => $data) {
            $temp = array_key_exists($column_title, $data) ?  $this->_data[$key][$column_title] : "";
            $params_url="";
            foreach($params as $param) {
                $params_url[] = $param . DIRECTORY_SEPARATOR . $data[$param];
            }
            $this->_data[$key][$column_title] = $temp . ' ' . HTML::create_tag("a", array("href" => $action_url . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR,$params_url)), $action_label);
        }
    }

    /** @param string $mode
      * @return string */
    public function render($mode = "xhtml") {
		$firstCol = key($this->_columns);
		switch($mode) {
			case "xhtml":
				//foreach($this->_data as $key => $value) {
					//$firstKey = key($value);
					//$htmlObject = Incube_HTML_Element::factory($firstKey, $value[$firstKey], array("type" => "checkbox"));
					////Incube_Debug::dump($htmlObject->render());die;

					//$this->_data[$key][$firstKey] = $htmlObject->render();
				//}
				//unset($this->_columns[$firstCol]);
				return $this->html_grid($this->_columns, $this->_data);
			//case "text":
				//return implode(array_merge($this->_columns, $this->_data));
			case "json":
				return json_encode($this->_data);
			default:
				return "unexistent render mode";

		}
    }


    /** @param array $ths
      * @param array $tds */
	public function html_grid($ths, $tds) {
        $out = array();
		$out[] = "<table class=\"tgrid\">";
		$out[] = "<thead>";
        $out[] = "<tr>";
        //$out[] = "<td/>"; //ths
        foreach($ths as $th) {
            $out[] = "<th>" . ucfirst($th) . "</th>";
        }   
        $out[] ="</tr>";
        $out[] ="</thead>";
        $out[] ="<tbody>";
        $out[] = "<tr>";
        foreach($tds as $td) {

			//$fkey = key($td);
			//$out .= "<td>$td[$fkey]</td>";
			//unset($td[$fkey]);
            foreach($td as $key => $value) {
                if(in_array($key, $ths)) $out[] = "<td>$value</td>";
            }   
            $out[] = "</tr>";
        }   

        $out[] = "</tr>";
        $out[] = "<tbody>";
        $out[] = "</table>";
        return implode("\n", $out);
	}
}
?>
