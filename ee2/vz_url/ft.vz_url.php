<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ Url Class
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 *
 */
 
class Vz_url_ft extends EE_Fieldtype {

	var $info = array(
		'name'			=> 'VZ Url',
		'version'		=> '2.0.0'
	);
	
	/**
	 * Fieldtype Constructor
	 *
	 */
	function Vz_url_ft()
	{
		parent::EE_Fieldtype();

		if (!isset($this->EE->session->cache['vz_url']))
		{
			$this->EE->session->cache['vz_url'] = array('jscss' => FALSE, 'ft_url' => FALSE);
		}
		$this->cache =& $this->EE->session->cache['vz_url'];
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Install Fieldtype
	 *
	 */
	function install()
	{
    $this->EE->lang->loadfile('vz_url');
    
		return array(
			'vz_url_error_text'	=> lang('vz_url_error_text'),
			'vz_url_redirect_text'	=> lang('vz_url_redirect_text')
		);
	}
	
	/**
	 * Get the url of the VZ Url files
	 *
	 */
	private function _ft_url()
	{
		if (!$this->cache['ft_url'])
		{
			// Construct the url
			$cp_url = $this->EE->config->item('cp_url');
			$cp_url = str_ireplace('index.php','',$cp_url);
			if (substr($cp_url, -1) != '/') $cp_url .= '/';
			
			// And cache it
			$this->cache['ft_url'] = $cp_url . 'expressionengine/third_party/vz_url/';
		}
		
		return $this->cache['ft_url'];
	}
	
	/**
	 * Include the JS and CSS files,
	 * but only the first time
	 *
	 */
	private function _include_jscss()
	{
		if (!$this->cache['jscss'])
		{
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_ft_url().'css/vz_url.css" />');
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_ft_url().'javascript/vz_url.js"></script>');
			$this->EE->cp->add_to_foot(
				'<script type="text/javascript">/*<![CDATA[ */' . NL .
				'vzUrl.errorText="' . addslashes($this->settings['vz_url_error_text']) . '";' . NL .
				'vzUrl.redirectText="' . addslashes($this->settings['vz_url_redirect_text']) . '";' . NL .
				'vzUrl.proxyUrl="' . $this->_ft_url() . 'proxy.php";' . NL .
				'// ]]></script>'
			);
			
			$this->cache['jscss'] = TRUE;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Display Global Settings
	 *
	 */
	function display_global_settings()
	{	
		$val = array_merge($this->settings, $_POST);
		
		// load the table lib
		$this->EE->load->library('table');

		// load the language file
		$this->EE->lang->loadfile('vz_url');

		// Table template
		$this->EE->table->set_template(array(
			'table_open'    => '<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">',
			'row_start'     => '<tr class="even">',
			'row_alt_start' => '<tr class="odd">'
		));
		
		// Draw the settings table
		$this->EE->table->set_heading(array('data' => lang('preference'), 'style' => 'width: 50%'), lang('setting'));
		
		$this->EE->table->add_row(
			lang('vz_url_error_text_label', 'vz_url_error_text'),
			form_input('vz_url_error_text', $val['vz_url_error_text'], 'id="vz_url_error_text"')
		);
		
		$this->EE->table->add_row(
			lang('vz_url_redirect_text_label', 'vz_url_redirect_text').' <div class="subtext">'.lang('vz_url_redirect_hint').'</div>',
			form_input('vz_url_redirect_text', $val['vz_url_redirect_text'], 'id="vz_url_redirect_text"')
		);

		return $this->EE->table->generate();
	}
	
	/**
	 * Save Global Settings
	 *
	 */
	function save_global_settings()
	{
		return array_merge($this->settings, $_POST);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Display Field on Publish
	 *
	 */
	function display_field($data, $cell = FALSE)
	{
    $this->_include_jscss();
    
		// Fill in http:// if the field is empty
		$val = ($data) ? $data : 'http://';
		
		// Is it a Matrix cell?
		$name = $cell ? $this->cell_name : $this->field_name;
		
		return form_input($name, $val, 'class="vz_url_field"');
	}

	/**
	 * Save Field
	 *
	 */
	function save($data)
	{
		// Remove http:// if it's the only thing in the field
		return ($data == 'http://') ? '' : $data;
	}

	/**
	 * Display Cell
	 *
	 */
	function display_cell($data)
	{
		return $this->display_field($data, TRUE);
	}

	/**
	 * Save Cell
	 *
	 */
	function save_cell($data)
	{
		// Remove http:// if it's the only thing in the cell
		return ($data == 'http://') ? '' : $data;
	}

}

/* End of file ft.vz_url.php */