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
class Mx_stop_spammers_upd {
		
	var $version        = '1.1.0'; 
	var $module_name = "Mx_stop_spammers";
	
    function Mx_stop_spammers_upd( $switch = TRUE ) 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
    } 

    /**
     * Installer for the Mx_stop_spammers module
     */
    function install() 
	{				
						
		$data = array(
			'module_name' 	 => $this->module_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		);

		$this->EE->db->insert('modules', $data);		
		
		$this->EE->load->dbforge();

		try{
			if (!$this->EE->db->field_exists('trusted', 'members')) {
				$column= array('trusted'	 => array('type' => 'TEXT'));				
				$this->EE->dbforge->add_column('members', $column);
			}
			if (!$this->EE->db->field_exists('settings', 'modules')) {
				$column= array('settings'	 => array('type' => 'TEXT'));
				$this->EE->dbforge->add_column('modules', $column);
			}

			return true;
		}
		catch(Exception $e){return true;} 		
		
		
		
		//
		// Add additional stuff needed on module install here
		// 
																									
		return TRUE;
	}

	
	/**
	 * Uninstall the Mx_stop_spammers module
	 */
	function uninstall() 
	{ 				
		
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->module_name));
		
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
		
		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');
		
		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');
		
		$this->EE->db->where('class', $this->module_name.'_mcp');
		$this->EE->db->delete('actions');
		
		$this->EE->load->dbforge();

		if (!$this->EE->db->field_exists('settings', 'modules')) {
		$this->EE->dbforge->drop_column('members', 'trusted');}
		
		if (!$this->EE->db->field_exists('settings', 'modules')) {
		$this->EE->dbforge->drop_column('modules', 'settings');
		}
		
		
		return TRUE;
	}
	
	/**
	 * Update the Mx_stop_spammers module
	 * 
	 * @param $current current version number
	 * @return boolean indicating whether or not the module was updated 
	 */
	
	function update($current = '')
	{
		return FALSE;
	}
    
}

/* End of file upd.mx_stop_spammers.php */ 
/* Location: ./system/expressionengine/third_party/mx_stop_spammers/upd.mx_stop_spammers.php */ 