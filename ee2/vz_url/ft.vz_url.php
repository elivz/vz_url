<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ Url Class
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
 
class Vz_url_ft extends EE_Fieldtype {

	var $info = array(
		'name'			=> 'VZ Url',
		'version'		=> '2.0.0'
	);
	
	/**
	 * Fieldtype Constructor
	 */
	function Vz_url_ft()
	{
		parent::EE_Fieldtype();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Install Fieldtype
	 *
	 * @access	public
	 * @return	default global settings
	 *
	 */
	function install()
	{
		return array(
			'vz_url_error_text'	=> 'That url seems to be invalid.'
		);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Display Global Settings
	 *
	 * @access	public
	 * @return	form contents
	 *
	 */
	function display_global_settings()
	{
		$val = array_merge($this->settings, $_POST);
		$form = form_label('vz_url_error_text', 'vz_url_error_text').NBS.form_input('vz_url_error_text', $val['vz_url_error_text']).NBS.NBS.NBS.' ';
		
		return $form;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Save Global Settings
	 *
	 * @access	public
	 * @return	global settings
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
	 * @access	public
	 * @param	existing data
	 * @return	field html
	 *
	 */
	function display_field($data, $cell = FALSE)
	{
		$this->EE->cp->load_package_css('vz_url');
		$this->EE->cp->load_package_js('vz_url');
		$this->EE->cp->add_to_foot(
			'<script type="text/javascript">/*<![CDATA[ */'.NL.
			'vzUrl.errorText="'.$this->settings['vz_url_error_text'].'";' . NL .
			'vzUrl.proxyUrl="' . BASE . 'expressionengine/third_party/vz_url/proxy.php";' . NL .
			'// ]]></script>'
		);
		
		// Fill in http:// if the field is empty
		$val = ($data) ? $data : 'http://';
		
		return form_input($this->field_name, $val, 'class="vz_url_field"');
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		$this->_include_theme_js('scripts/matrix2.js');

		return $this->display_field($data, TRUE);
	}

	/**
	 * Save Field
	 * 
	 * @param  string  $data		The field's post data
	 */
	function save($data)
	{
		// Remove http:// if it's the only thing in the field
		return ($data ==  'http://') ? '' : $data;
	}


	/**
	 * Save Cell
	 * 
	 * @param  string  $cell_data		The field's post data
	 * @param  array  $fcell_settings	The field settings
	 */
	function save_cell($cell_data, $cell_settings)
	{
		// Remove http:// if it's the only thing in the cell
		return ($cell_data ==  'http://') ? '' : $cell_data;
	}

}

/* End of file ft.vz_url.php */