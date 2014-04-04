<?php  

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MX Stop Spammers!
 * 
 * MX Stop Spammers!  Adds the Ability to Clone Entries 
 * 
 * @package		ExpressionEngine
 * @category	Extension
 * @author    Max Lazar <max@eec.ms>
 * @copyright Copyright (c) 2010 Max Lazar (http://eec.ms)
 * @license   http://creativecommons.org/licenses/MIT/  MIT License
 * @version 1.0.2
 * @Thanks to Leevi Graham (http://leevigraham.com/) for permission to use his SET/GET settings function for EE2!
 */


class Mx_stop_spammers_ext
{
		var $settings        = array();
    
		var $addon_name     = 'MX Stop Spammers!';
		var $name			       = 'MX Stop Spammers!';
		var $version         = '1.0.2';
		var $description     = 'A module for easily members management';
		var $settings_exist  = 'n';
		var $docs_url        = '';

		 /**
		* Defines the ExpressionEngine hooks that this extension will intercept.
		*
		* @since Version 1.0.1
		* @access private
		* @var mixed an array of strings that name defined hooks
		* @see http://codeigniter.com/user_guide/general/hooks.html
		**/
		private $hooks = array(
							'member_member_register'       => 'member_member_register'
							
		);
    	// -------------------------------
		// Constructor 
		// -------------------------------             
		function Mx_stop_spammers_ext($settings=''){
			$this->EE =& get_instance();
			$this->settings = $settings;
			
		}
	
		 public function __construct($settings=FALSE)
		{
			$this->EE =& get_instance();

			// define a constant for the current site_id rather than calling $PREFS->ini() all the time
			if(defined('SITE_ID') == FALSE)
				define('SITE_ID', $this->EE->config->item('site_id'));

			// set the settings for all other methods to access
			$this->settings = ($settings == FALSE) ? $this->_getSettings() : $this->_saveSettingsToSession($settings);
		}


		 /**
		* Prepares and loads the settings form for display in the ExpressionEngine control panel.
		* @since Version 1.0.0
		* @access public
		* @return void
		**/
		public function settings_form()
		{
		

		}
		
		public function member_member_register($data, $member_id)
		{

			$enable =  (isset($this->settings['auto_checking'])) ? (($this->settings['auto_checking'] == 'n') ? false : true) : true;
			
			if ($enable) {
				$xml_raw = @simplexml_load_file("http://www.stopforumspam.com/api?username=" . $data['username'] ."&email=" . urlencode($data['email']) . "&ip=" . $data['ip_address'] . "&f=xmlcdata");
				
				if (is_object($xml_raw) AND ($xml_raw instanceof SimpleXMLElement)) { 
			
					if($xml_raw->email->appears == 1 OR ($xml_raw->ip->appears == 1 AND $xml_raw->username->appears == 1))
					{
						$this-> _ban_member($member_id);
						$this->EE->lang->loadfile('mx_stop_spammers');
						$this->EE->load->library('logger');
						$this->EE->logger->log_action(str_replace(Array ('{member_id}', '{username}'), array ($member_id, $data['username']), lang('member_ban')))	;	
						return true;
					}
				}
				else {
						$this->EE->lang->loadfile('mx_stop_spammers');
						$this->EE->load->library('logger');
						$this->EE->logger->log_action(str_replace(Array ('{member_id}', '{username}'), array ($member_id, $data['username']), lang('recive_data_error')))	;			
				}
			}
			
			return false;
		}
		
		function _ban_member  ($member_id)
		{
	        $this->EE->db->where_in('member_id', $member_id);
            $this->EE->db->update('members', array('group_id' => 2 ));
            $this->EE->db->update('members', array('trusted' => '' ));
			return true;
		}
		// END
		
	
		// --------------------------------
		//  Activate Extension
		// --------------------------------

		function activate_extension()
		{
			$this->_createHooks();
		}
		
		/**
		* Saves the specified settings array to the database.
		*
		* @since Version 1.0.0
		* @access protected
		* @param array $settings an array of settings to save to the database.
		* @return void
		**/
		private function _getSettings($refresh = FALSE)
		{
			$settings = FALSE;
			if(isset($this->EE->session->cache[$this->addon_name][__CLASS__]['settings']) === FALSE || $refresh === TRUE)
			{
				$settings_query = $this->EE->db->select('settings')
				->where('enabled', 'y')
				->where('class', __CLASS__)
				->get('extensions', 1);

			if($settings_query->num_rows())
			{
				$settings = unserialize($settings_query->row()->settings);
				$this->_saveSettingsToSession($settings);
			}
			}
			else
			{
				$settings = $this->EE->session->cache[$this->addon_name][__CLASS__]['settings'];
			}
			return $settings;
		}

		 /**
		* Saves the specified settings array to the session.
		* @since Version 1.0.0
		* @access protected
		* @param array $settings an array of settings to save to the session.
		* @param array $sess A session object
		* @return array the provided settings array
		**/
		private function _saveSettingsToSession($settings, &$sess = FALSE)
		{
			// if there is no $sess passed and EE's session is not instaniated
			if($sess == FALSE && isset($this->EE->session->cache) == FALSE)
			return $settings;

			// if there is an EE session available and there is no custom session object
			if($sess == FALSE && isset($this->EE->session) == TRUE)
			$sess =& $this->EE->session;

			// Set the settings in the cache
			$sess->cache[$this->addon_name][__CLASS__]['settings'] = $settings;

			// return the settings
			return $settings;
		}


		/**
		* Saves the specified settings array to the database.
		*
		* @since Version 1.0.0
		* @access protected
		* @param array $settings an array of settings to save to the database.
		* @return void
		**/
		private function _saveSettingsToDB($settings)
		{
			$this->EE->db->where('class', __CLASS__)
			->update('extensions', array('settings' => serialize($settings)));
		}
		 /**
		* Sets up and subscribes to the hooks specified by the $hooks array.
		* @since Version 1.0.0
		* @access private
		* @param array $hooks a flat array containing the names of any hooks that this extension subscribes to. By default, this parameter is set to FALSE.
		* @return void
		* @see http://codeigniter.com/user_guide/general/hooks.html
		**/
		private function _createHooks($hooks = FALSE)
		{
			if (!$hooks)
			{
			$hooks = $this->hooks;
			}

			$hook_template = array(
				'class' => __CLASS__,
				'settings' =>'',
				'version' => $this->version,
			);

			$hook_template['settings']['min_frequency'] = '1';
			$hook_template['settings']['auto_checking'] = 'y';
			
			foreach ($hooks as $key => $hook)
			{
				if (is_array($hook))
				{
					$data['hook'] = $key;
					$data['method'] = (isset($hook['method']) === TRUE) ? $hook['method'] : $key;
					$data = array_merge($data, $hook);
				}
				else
				{
					$data['hook'] = $data['method'] = $hook;
				}

				$hook = array_merge($hook_template, $data);
				$hook['settings'] = serialize($hook['settings']);
				$this->EE->db->query($this->EE->db->insert_string('exp_extensions', $hook));
			}
		}

		 /**
		* Removes all subscribed hooks for the current extension.
		*
		* @since Version 1.0.0
		* @access private
		* @return void
		* @see http://codeigniter.com/user_guide/general/hooks.html
		**/
		private function _deleteHooks()
		{
			$this->EE->db->query("DELETE FROM `exp_extensions` WHERE `class` = '".__CLASS__."'");
		}

	
		// END
	


	
		// --------------------------------
		//  Update Extension
		// --------------------------------  

		function update_extension ( $current='' )
		{
	
			if ($current == '' OR $current == $this->version)
			{
				return FALSE;
			}

			if ($current < '2.0.1')
			{
				// Update to next version
			}

			$this->EE->db->query("UPDATE exp_extensions SET version = '".$this->EE->db->escape_str($this->version)."' WHERE class = '".get_class($this)."'");
		}
		// END
	
		// --------------------------------
		//  Disable Extension
		// --------------------------------

		function disable_extension()
		{

			$this->EE->db->delete('exp_extensions', array('class' => get_class($this)));
		}
		// END
}

/* End of file ext.mx_stop_spammers.php */
/* Location: ./system/expressionengine/third_party/mx_stop_spammers/ext.mx_stop_spammers.php */