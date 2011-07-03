<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ URL Class
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2011 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 *
 */
 
class Vz_url_ft extends EE_Fieldtype {

	public $info = array(
		'name'			=> 'VZ URL',
		'version'		=> '2.1.2'
	);
	
	/**
	 * Fieldtype Constructor
	 */
	function Vz_url_ft()
	{
		parent::EE_Fieldtype();

		if (!isset($this->EE->session->cache['vz_url']))
		{
			$this->EE->session->cache['vz_url'] = array('jscss' => FALSE, 'theme_url' => FALSE);
		}
		$this->cache =& $this->EE->session->cache['vz_url'];
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get the URL of the VZ URL files
	 */
	private function _theme_url()
	{
		if (!$this->cache['theme_url'])
		{
			// Construct the url
			$theme_url = $this->EE->config->item('theme_folder_url');
			if (substr($theme_url, -1) != '/') $theme_url .= '/';
			
			// And cache it
			$this->cache['theme_url'] = $theme_url . 'third_party/vz_url/';
		}
		
		return $this->cache['theme_url'];
	}
	
	/**
	 * Include the JS and CSS files,
	 * but only the first time
	 */
	private function _include_jscss()
	{
		if (!$this->cache['jscss'])
		{
            $this->EE->lang->loadfile('vz_url');
            
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().'styles/vz_url.css" />');
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().'scripts/vz_url.js"></script>');
			$this->EE->javascript->output(
				'vzUrl.errorText="' . addslashes(lang('vz_url_error_text')) . '";' .
				'vzUrl.redirectText="' . addslashes(lang('vz_url_redirect_text')) . '";' .
				'vzUrl.nonlocalText="' . addslashes(lang('vz_url_nonlocal_text')) . '";' .
				'vzUrl.proxyUrl="' . $this->_theme_url() . 'proxy.php";' .
				'vzUrl.init();'
			);
			
			$this->cache['jscss'] = TRUE;
		}
	}
	
	// --------------------------------------------------------------------
    
    /**
     * Display Field Settings
     */
    function display_settings($settings)
    {
		$this->EE->load->library('table');
		$this->EE->lang->loadfile('vz_url');
		
        $limit_local = isset($settings['vz_url_limit_local']) && $settings['vz_url_limit_local'] == 'y';
		
		$settings_ui = array(
			lang('vz_url_limit_local_label', 'vz_url_limit_local'),
			form_radio('vz_url_limit_local', 'y', $limit_local, 'id="vz_url_limit_local_yes"') . ' ' .
			form_label(lang('yes'), 'vz_url_limit_local_yes') .
			'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
			form_radio('vz_url_limit_local', 'n', !$limit_local, 'id="vz_url_limit_local_no"') . ' ' .
			form_label(lang('no'), 'vz_url_limit_local_no')
		);
		
        $this->EE->table->add_row($settings_ui);
    }
    
	/**
	 * Display Cell Settings
	 */
    function display_cell_settings($settings)
    {
		$this->EE->load->library('table');
		$this->EE->lang->loadfile('vz_url');
		
        $limit_local = isset($settings['vz_url_limit_local']) && $settings['vz_url_limit_local'] == 'y';
		
		$settings_ui = array(
			lang('vz_url_limit_local_label', 'vz_url_limit_local'),
			form_checkbox('vz_url_limit_local', 'y', $limit_local)
		);
		
        return array($settings_ui);
    }
	
    /**
     * Save Field Settings
     */
    function save_settings()
    {
        return array('vz_url_limit_local' => $this->EE->input->post('vz_url_limit_local'));
    }
	
	// --------------------------------------------------------------------
	
	/**
	 * Display Field on Publish
	 */
	function display_field($data, $cell = FALSE)
	{
        $this->_include_jscss();
        
        $limit_local = isset($this->settings['vz_url_limit_local']) && $this->settings['vz_url_limit_local'] == 'y';
        
        // Fill in http:// if the field is empty
        if (!$data && $limit_local)
        {
            $data = $this->EE->config->item('site_url');
        }
        elseif (!$data)
        {
            $data = 'http://';
        }
        
        // Is it a Matrix cell?
        $name = $cell ? $this->cell_name : $this->field_name;
        
        return form_input($name, $data, 'class="vz_url_field'.($limit_local ? ' local' : '').'"');
	}
    
    /**
     * Display Cell
     */
    function display_cell($data)
    {
        return $this->display_field($data, TRUE);
    }

    /**
     * Save Field
     */
    function save($data)
    {
        // Remove http:// if it's the only thing in the field
        return ($data == 'http://' || $data == '/') ? '' : $data;
    }
    
    /**
     * Save Cell
     */
    function save_cell($data)
    {
    	return $this->save($data);
    }
    
    /**
     * Use redirect="" parameter to immediately redirect the page 
     * Thanks to Brian Litzinger for the idea and code
     */
    function replace_tag($data, $params = '', $tagdata = '')
    {
        if (isset($params['redirect']) && $params['redirect'] == 'yes' && $data != '')
        {
            header("Location: {$data}");
            exit;
        }
        else
        {
            return $data;
        }
    } 

}

/* End of file ft.vz_url.php */