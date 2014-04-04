<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * -
 *
 * @package		Mx_stop_spammers
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Max Lazar
 * @link		http://www.eec.ms
 */
class Mx_stop_spammers_mcp
{
    var $base; // the base url for this module			
    var $form_base; // base url for forms
    var $module_name = 'Mx_stop_spammers';
	var $ext_name = 'Mx_stop_spammers_ext';
    var $perpage = 50;
    var $search_url = "";
	var $settings        = array();

    
    function Mx_stop_spammers_mcp($switch = TRUE)
    {
        // Make a local reference to the ExpressionEngine super object
        $this->EE =& get_instance();
        $this->base      = BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=' . $this->module_name;
        $this->form_base = 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=' . $this->module_name;
        
        // uncomment this if you want navigation buttons at the top
        $this->EE->cp->set_right_nav(array(
           lang('members_list')  => $this->base,
          //  'Batch checking' => $this->base . AMP . 'method=batch', 
            lang('settings') => $this->base . AMP . 'method=settings'
        ));
        
		$this->settings = $this->_getSettings();
    }
    
    function settings()
    {
        $vars   = array();
		
			 // Create the variable array
			$vars = array(
				'addon_name' => $this->module_name,
				'error' => FALSE,
				'input_prefix' => __CLASS__,
				'message' => FALSE,
				'settings_form' =>FALSE,
				'export_out' => false
			);
		
		
        $vars['settings'] = $this->settings;
        
		if($new_settings = $this->EE->input->post(__CLASS__))
		{
					$vars['settings'] = $new_settings;
					 $this->_saveSettingsToDB($new_settings);
					$vars['message'] = $this->EE->lang->line('settings_saved_success');			
		}
		
        if (!empty($errors)) {
            $vars['message'] = $this->EE->lang->line('problems');
        }
        $vars['errors'] = (isset($errors)) ? $errors : false;
        
        return $this->content_wrapper('settings', $this->EE->lang->line('Settings'), $vars);
    }
    
    function index()
    {


        $group_id      = 5;
        $perpage       = 50;
        $offset        = 0;
        $search_value  = '';
        $order         = array();
        $column_filter = 'all';
        
        $vars['have_bio']         = '';
        $vars['num_in_username']  = '';
        $vars['have_signature']   = '';
        $vars['links_in_bio']     = '';
        $vars['no_last_visit']    = '';
        $vars['perpage_selected'] = '';
        $vars['forum_flag'] = false;
		
        $search_settings = array(
            'have_bio' => '',
            'num_in_username' => '',
            'have_signature' => '',
            'links_in_bio' => '',
            'no_last_visit' => '',
            'member_group' => '',
            'rownum' => 0,
            'perpage_selected' => 50,
            'trusted' => '',
			'have_comments' => '',
			'have_topics' => ''
        );
        
		if ($this->EE->input->post('toggle'))
		{

			foreach ($_POST['toggle'] as $key => $val)
			{
				if 	($_POST['action'] == 'ban') {
					$this->_ban_member($val);
				}
				if 	($_POST['action'] == 'trusted') {
					$this->add2trusted($val);
				}
				if 	($_POST['action'] == 'untrusted') {
					$this->add2trusted($val, true);
				}
			
			}
			
		}
		
		
		
        foreach ($search_settings as $v => $k) {
            if ($this->EE->input->get_post($v) !== FALSE) {
                $search_settings[$v] = $this->EE->input->get_post($v);
            }
        }
     
        $group_id = ($search_settings['member_group'] == '') ? 5 : $search_settings['member_group'];
        
        $have_bio_c = ($search_settings['have_bio'] == '') ? '' : " AND NOT ISNULL(members.bio) AND members.bio != ''";
        
        $num_in_username_c = ($search_settings['num_in_username'] == '') ? '' : " AND members.username REGEXP '[0-9]'";
        
        $have_signature_c = ($search_settings['have_signature'] == '') ? '' : " AND NOT ISNULL(members.signature) AND members.signature !=''";
        
        $links_in_bio_c = ($search_settings['links_in_bio'] == '') ? '' : " AND members.bio REGEXP 'http'";
        
        $no_last_visit_c = ($search_settings['no_last_visit'] == '') ? '' : " AND  members.last_visit = 0";
        
        $trusted_c = ($search_settings['trusted'] == '') ? " AND (members.trusted = '' OR ISNULL(members.trusted))" : '';
		
		$have_comments_c = ($search_settings['have_comments'] == '') ? '' :  " AND (total_comments > 0)";
		
        
        $vars['toggle'] = $this->EE->input->post('toggle');
        
        $vars = array_merge($vars, $search_settings);
    
        
        $this->search_url = "";
        foreach ($search_settings as $v => $k) {
            if ($search_settings[$v] != "") {
                $this->search_url .= AMP . $v . '=' . $k;
            }
            
        }
        
        $query = $this->EE->member_model->get_member_groups();
        foreach ($query->result() as $group) {
            $vars['member_groups'][$group->group_id] = $group->group_title;
        }
        
        $vars['member_group_selected'] = $group_id;
        $member_ids                    = array();
        
        $this->EE->db->select('member_id');
        $this->EE->db->where('group_id', $group_id);
        $query = $this->EE->db->get('members');
        
        foreach ($query->result() as $member) {
            $member_ids[] = $member->member_id;
        }
        
        $this->EE->load->library('javascript');
        $this->EE->load->library('table');
        $this->EE->load->helper('form');
        
        
        $group_c = "AND members.group_id = " . $group_id;
		$forum_sql = "";
		$have_topics_c="";
		
		if( $this->EE->db->table_exists('exp_forum_topics') ) {        
			$forum_sql =  ", members.total_forum_topics+members.total_forum_posts as total_forum_posts";
			$vars['forum_flag'] = true;
			$have_topics_c = ($search_settings['have_topics'] == 'yes') ? " AND (members.total_forum_topics+members.total_forum_posts > 0) " : '' ;
		}
		
        //total_comments 	total_forum_topics 	total_forum_posts 
		
        $sql                = "SELECT members.username, members.member_id, members.join_date, members.url, members.bio, members.trusted, members.screen_name, members.email, members.join_date, members.total_entries, members.total_comments, members.total_forum_topics, members.total_forum_posts, members.last_visit, member_groups.group_title, members.ip_address, member_groups.group_id, members.member_id, members.in_authorlist $forum_sql FROM exp_members  as members LEFT JOIN exp_member_groups as member_groups ON member_groups.group_id = members.group_id WHERE members.group_id != 1  $group_c AND  member_groups.site_id = " . $this->EE->config->item('site_id') . $links_in_bio_c . $have_signature_c . $num_in_username_c . $have_bio_c . $no_last_visit_c . $trusted_c . $have_comments_c. $have_topics_c . "";
        
		$query              = $this->EE->db->query($sql);

        $vars["total_rows"] = $query->num_rows();

        $sql .= " ORDER BY members.join_date desc LIMIT " . $search_settings['rownum'] . ",  " . $vars['perpage_selected'] . "";
	
		$this->perpage = $vars['perpage_selected'];
        $query               = $this->EE->db->query($sql);
        $vars['members']     = $query->result();
        $vars['members_num'] = $query->num_rows();
        
        
        
        $vars['export_out'] = false;
        $vars['message']    = false;
        
        $vars['cp_url'] = BASE;
        
        $vars['total'] = $this->EE->member_model->count_members();
        
        $this->EE->load->library('pagination');
        $p_config = $this->pagination_config('index', $vars["total_rows"]);
        $this->EE->pagination->initialize($p_config);
        $pagination_links   = $this->EE->pagination->create_links();
        $vars['pagination'] = $pagination_links;
        
        $vars['form_options'] = array(       
            "ch_batch" => lang('ch_batch'),
			"ban" => lang('toban'),
			"trusted" => lang('add2trusted'),
            "untrusted" => lang('untrusted')
        );
		
        if ($this->settings['sfs_api'] != "") {
			 $vars['form_options']["banandsend"] = lang('banandsend');
		}
		
        $vars['perpage'] = array(
            20 => 20,
            50 => 50,
            100 => 100,
            200 => 200
        );
        
        $this->EE->cp->load_package_js('main');
        $this->EE->cp->load_package_css('style');
        return $this->content_wrapper('index', 'welcome', $vars);
    }
    
    function pagination_config($method, $total_rows)
    {
        // Pass the relevant data to the paginate class
        
        
        $config['base_url']             = ($this->search_url == '') ? $this->base . AMP . 'method=' . $method : $this->base . AMP . 'method=' . $method . $this->search_url;
        $config['total_rows']           = $total_rows;
        $config['per_page']             = $this->perpage;
        $config['page_query_string']    = TRUE;
        $config['query_string_segment'] = 'rownum';
        $config['full_tag_open']        = '<p id="paginationLinks">';
        $config['full_tag_close']       = '</p>';
        $config['prev_link']            = '<img src="' . $this->EE->cp->cp_theme_url . 'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
        $config['next_link']            = '<img src="' . $this->EE->cp->cp_theme_url . 'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
        $config['first_link']           = '<img src="' . $this->EE->cp->cp_theme_url . 'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
        $config['last_link']            = '<img src="' . $this->EE->cp->cp_theme_url . 'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';
        
        return $config;
    }
    
    function add2trusted($member_id, $reverse = false)
    {
   //     $member_id = $this->EE->input->get_post('member_id');
	//	$reverse = ($this->EE->input->get_post('reverse') == 'yes') ? true : false;
        if ($member_id != '') {
		
			$this->EE->db->where('member_id', $member_id)
			->update('members', array('trusted' => (($reverse) ? '' : 'trusted')));
			

        }
       return true;
    }
    
    function batch()
    {
        $config['upload_path']   = '';
        $config['allowed_types'] = 'zip|png|application/zip';
        $config['max_size']      = 3072000000;
        $config['overwrite']     = FALSE;
        $config['encrypt_name']  = TRUE;
        
        $this->EE->load->library('upload', $config);
        
        $this->EE->upload->do_upload();
        $config['file_name'] = "asdasd.zip";
        $upload_data         = $this->EE->upload->data();
        $file_name           = $upload_data['file_name'];
        echo $file_name;
        
        
        
        
        
        $vars = array(
            'module_name' => $this->module_name,
            'error' => FALSE,
            'input_prefix' => __CLASS__,
            'message' => FALSE,
            'settings_form' => FALSE
        );
        return $this->content_wrapper('batch', 'welcome', $vars);
    }
	
    function _ban_member  ($member_id)
	{
	        $this->EE->db->where_in('member_id', $member_id);
            $this->EE->db->update('members', array('group_id' => 2 ));
            $this->EE->db->update('members', array('trusted' => '' ));
			return true;
	}
	
	
    function toban()
    {
        $username   = $this->EE->input->get_post('username');
        $ip_address = str_replace("_", ".", $this->EE->input->get_post('ip_address'));
        $email      = urlencode($this->EE->input->get_post('email'));
        $member_id  = trim($this->EE->input->get_post('member_id'));
        $sfs        = ($this->EE->input->get_post('sfs') == 'yes') ? true : false;
        
		
        if ($member_id != '') {

			$this->_ban_member($member_id);
		
            if ($sfs && $this->settings['sfs_api'] != "") {
                $api = $this->settings['sfs_api'];
                die($this->posttoSFS("username=" . $username . "&ip_addr=" . $ip_address . "&email=" . $email . "&api_key=" . $api));
            }
            
        }
        die(true);
    }
    
    function toban_prototype()
    {
        $username   = $this->EE->input->get_post('username');
        $ip_address = str_replace("_", ".", $this->EE->input->get_post('ip_address'));
        $email      = urlencode($this->EE->input->get_post('email'));
        $member_id  = $this->EE->input->get_post('member_id');
        
        if ($member_id != '') {
             
            die('OK');
        }
        
    }
    
    function posttoSFS($data)
    {
        $fp = fsockopen("www.stopforumspam.com", 80);
        fputs($fp, "POST /add.php HTTP/1.1\n");
        fputs($fp, "Host: www.stopforumspam.com\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
        fputs($fp, "Content-length: " . strlen($data) . "\n");
        fputs($fp, "Connection: close\n\n");
        $rsp = fputs($fp, $data);
        fclose($fp);
        return $this->parseHttpResponse($rsp);
    }
    
    function validateHttpResponse($headers = null)
    {
        if (!is_array($headers) or count($headers) < 1) {
            return false;
        }
        switch (trim(strtolower($headers[0]))) {
            case 'http/1.0 100 ok':
            case 'http/1.0 200 ok':
            case 'http/1.1 100 ok':
            case 'http/1.1 200 ok':
                return true;
                break;
        }
        return false;
    }
    
    function parseHttpResponse($content = null)
    {
        if (empty($content)) {
            return false;
        }
        // split into array, headers and content.
        $hunks = explode("\r\n\r\n", trim($content));
        if (!is_array($hunks) or count($hunks) < 2) {
            return false;
        }
        $header  = $hunks[count($hunks) - 2];
        $body    = $hunks[count($hunks) - 1];
        $headers = explode("\n", $header);
        unset($hunks);
        unset($header);
        if (!$this->verifyHttpResponse($headers)) {
            return false;
        } else {
            return true;
        }
    }
    
    function checker()
    {
        $username   = $this->EE->input->get_post('username');
        $ip_address = str_replace("_", ".", $this->EE->input->get_post('ip_address'));
        $email      = urlencode($this->EE->input->get_post('email'));
        $xml_url    = 'http://www.stopforumspam.com/api?username=' . $username . '&ip=' . $ip_address . '&email=' . $email . '&f=json';
        
        $xml_raw = file_get_contents($xml_url);
        
        header("Content-type: application/json"); //RFC 4627
        header("Cache-control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past 
        
        die($xml_raw);
    }
    
    function content_wrapper($content_view, $lang_key, $vars = array())
    {
        $vars['content_view'] = $content_view;
        $vars['_base']        = $this->base;
        $vars['_form_base']   = $this->form_base;
		$vars['_search_url']   = $this->search_url;
		
        $this->EE->view->cp_page_title =lang($lang_key);
        $this->EE->cp->set_breadcrumb($this->base, lang('mx_stop_spammers_module_name'));
        
        return $this->EE->load->view('_wrapper', $vars, TRUE);
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
			if(isset($this->EE->session->cache[$this->module_name][__CLASS__]['settings']) === FALSE || $refresh === TRUE)
			{
				$settings_query = $this->EE->db->select('settings')
				->where('module_name', $this->module_name)
				->get('modules', 1);

			if($settings_query->num_rows())
			{
				$settings = unserialize($settings_query->row()->settings);
				$this->_saveSettingsToSession($settings);
			}
			}
			else
			{
				$settings = $this->EE->session->cache[$this->module_name][__CLASS__]['settings'];
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
			$sess->cache[$this->module_name][__CLASS__]['settings'] = $settings;

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
			$this->EE->db->where('module_name', $this->module_name)
			->update('modules', array('settings' => serialize($settings)));
			$this->EE->db->where('class', $this->ext_name)
			->update('extensions', array('settings' => serialize($settings)));
		}		
}

/* End of file mcp.mx_stop_spammers.php */
/* Location: ./system/expressionengine/third_party/mx_stop_spammers/mcp.mx_stop_spammers.php */