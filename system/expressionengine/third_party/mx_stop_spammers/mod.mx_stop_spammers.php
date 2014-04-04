<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * -
 *
 * @package		Mx_stop_spammers
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Max Lazar
 * @link		http://www.eec.ms
 */
class Mx_stop_spammers {

	var $return_data;
	
	function Mx_stop_spammers()
	{		
		$this->EE =& get_instance(); // Make a local reference to the ExpressionEngine super object
	}
	
		
	/**
     * Helper function for getting a parameter
	 */		 
	function _get_param($key, $default_value = '')
	{
		$val = $this->EE->TMPL->fetch_param($key);
		
		if($val == '') {
			return $default_value;
		}
		return $val;
	}

	/**
	 * Helper funciton for template logging
	 */	
	function _error_log($msg)
	{		
		$this->EE->TMPL->log_item("mx_stop_spammers ERROR: ".$msg);		
	}		
}

/* End of file mod.mx_stop_spammers.php */ 
/* Location: ./system/expressionengine/third_party/mx_stop_spammers/mod.mx_stop_spammers.php */ 