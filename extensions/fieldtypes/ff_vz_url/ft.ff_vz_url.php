<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * VZ Url Class
 *
 * @package   FieldFrame
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2009 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
 
class Ff_vz_url extends Fieldframe_Fieldtype {

	/**
	 * Fieldtype Info
	 * @var array
	 */
	var $info = array(
		'name'             => 'VZ Url',
		'version'          => '1.1.4',
		'desc'             => 'Textbox with ajax url validation',
		'docs_url'         => 'http://elivz.com/blog/single/vz_url_extension/',
		'versions_xml_url' => 'http://elivz.com/files/versions.xml'
	);
	
	var $requires = array(
		'ff'        => '1.3.0',
		'cp_jquery' => '1.1.1',
	);
    
	var $default_site_settings = array('vz_url_error_text' => "That url seems to be invalid.");


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
		$r .= $SD->block_c();
		return $r;
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
		$this->include_css('styles/ff_vz_url.css');
		$this->include_js('ff_vz_url.js');
		$this->insert_js('vzUrl.errorText = "'.$this->site_settings['vz_url_error_text'].'"');

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
		$this->include_css('styles/ff_vz_url.css');
		$this->include_js('ff_vz_url.js');
		$this->insert_js('vzUrl.errorText = "'.$this->site_settings['vz_url_error_text'].'"');

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


/* End of file ft.ff_vz_url.php */
/* Location: ./system/fieldtypes/ff_vz_instruct/ft.ff_vz_url.php */