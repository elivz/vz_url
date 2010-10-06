<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * VZ URL Class
 *
 * @package   FieldFrame
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2009 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
 
class Vz_url extends Fieldframe_Fieldtype {

	/**
	 * Fieldtype Info
	 * @var array
	 */
	var $info = array(
		'name'             => 'VZ URL',
		'version'          => '2.0.0',
		'desc'             => 'Textbox with ajax URL validation',
		'docs_url'         => 'http://elivz.com/blog/single/vz_url_extension/',
		'versions_xml_url' => 'http://elivz.com/files/versions.xml'
	);
	
	var $requires = array(
		'ff'        => '1.4.0',
		'cp_jquery' => '1.1.1',
	);
  
	var $default_site_settings = array(
		'vz_url_error_text' => 'That URL appears to be invalid.',
		'vz_url_redirect_text' => '{{old_url}} redirects to {{new_url}}, {{update="update it"}}.'
	);


	/**
	 * Get the default settings text
	 */
	private function _get_default_settings()
	{
		global $LANG;
		
		$settings = array(
			'vz_url_error_text' => $LANG->line('vz_url_error_text'),
			'vz_url_redirect_text' => $LANG->line('vz_url_redirect_text')
		);
		
		return $settings;
	}


	/**
	 * Display Site Settings
	 */
	function display_site_settings()
	{
		global $LANG;
		$SD = new Fieldframe_SettingsDisplay();
		
		$r = $SD->block($LANG->line('vz_url_settings_title'));
		$r .= $SD->row(array(
			$SD->label($LANG->line('vz_url_error_text_label'), ''),
			$SD->text('vz_url_error_text', $this->site_settings['vz_url_error_text'])
		));
		$r .= $SD->row(array(
			$SD->label($LANG->line('vz_url_redirect_text_label'), '') . $LANG->line('vz_url_redirect_hint'),
			$SD->text('vz_url_redirect_text', $this->site_settings['vz_url_redirect_text'])
		));
		$r .= $SD->block_c();
		return $r;
	}
	
	
	/**
	 * Get the URL of the VZ URL files
	 *
	 */
	private function _theme_url()
	{
		if (! isset($this->_theme_url))
		{
			global $PREFS;
			
			// Construct the url
			$theme_url = $PREFS->ini('theme_folder_url', 1);
			if (substr($theme_url, -1) != '/') $theme_url .= '/';
			
			// And cache it
			$this->_theme_url = $theme_url . 'third_party/vz_url/';
		}
		
		return $this->_theme_url;
	}
	
	/**
	 * Include the JS and CSS files,
	 * but only the first time
	 *
	 */
	private function _include_jscss()
	{
		if (!$this->_has_jscss)
		{
			$this->insert('head', '<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().'styles/vz_url.css" />');
			$this->insert('body', '<script type="text/javascript" src="'.$this->_theme_url().'scripts/vz_url.js"></script>');
			$this->insert_js(
				'vzUrl.errorText="' . addslashes($this->site_settings['vz_url_error_text']) . '";' . NL .
				'vzUrl.redirectText="' . addslashes($this->site_settings['vz_url_redirect_text']) . '";' . NL .
				'vzUrl.proxyUrl="' . $this->_theme_url() . 'proxy.php";'
			);
			
			$this->_has_jscss = TRUE;
		}
	}
	
    
	/**
	 * Display Field
	 * 
	 * @param  string  $field_name      The field's name
	 * @param  mixed   $field_data      The field's current value
	 * @param  array   $field_settings  The field's settings
	 * @return string  The field's HTML
	 */
	function display_field($field_name, $field_data, $field_settings)
	{
		$this->_include_jscss();

		$SD = new Fieldframe_SettingsDisplay();
		
		// Fill in http:// if the field is empty
		$val = ($field_data) ? $field_data : 'http://';
		
		return $SD->text($field_name, $val, array('style' => 'vz_url_field', 'width' => ''));
	}
	
    
	/**
	 * Display Cell
	 * 
	 * @param  string  $cell_name      The cell's name
	 * @param  mixed   $cell_data      The cell's current value
	 * @param  array   $cell_settings  The cell's settings
	 * @return string  The field's HTML
	 */
	function display_cell($cell_name, $cell_data, $cell_settings)
	{
		$this->_include_jscss();

		$SD = new Fieldframe_SettingsDisplay();
		
		// Fill in http:// if the field is empty
		$val = ($cell_data) ? $cell_data : 'http://';
		
		return $SD->text($cell_name, $val, array('style' => 'vz_url_field', 'width' => ''));
	}


	/**
	 * Save Field
	 * 
	 * @param  string  $field_data		The field's post data
	 * @param  array  $field_settings	The field settings
	 */
	function save_field($field_data, $field_settings)
	{
		// Remove http:// if it's the only thing in the field
		return ($field_data ==  'http://') ? '' : $field_data;
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
/* Location: ./system/fieldtypes/vz_url/ft.vz_url.php */