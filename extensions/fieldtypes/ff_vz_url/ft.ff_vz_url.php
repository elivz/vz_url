<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * VZ URL Class
 *
 * @package   FieldFrame
 * @author    Eli Van Zoeren <design@elivz.com>
 * @copyright Copyright (c) 2009 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
class Ff_vz_url extends Fieldframe_Fieldtype {

	/**
	 * Fieldtype Info
	 * @var array
	 */
	var $info = array(
		'name'             => 'VZ URL',
		'version'          => '0.9.7',
		'desc'             => 'Textbox with ajax url validation',
		'docs_url'         => 'http://elivz.com/blog/single/vz_url_extension/',
		'versions_xml_url' => 'http://elivz.com/files/version.xml'
	);
	
	var $requires = array(
		'ff'        => '0.9.5',
		'cp_jquery' => '1.1',
	);
    
	var $hooks = array('publish_form_headers');

	var $default_site_settings = array('vz_url_error_text' => "Please check your url, as it seems to be invalid.");


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
	* Add script to validate the urls to the header
	*/
	function publish_form_headers()
	{
		$r = $this->get_last_call('').NL.NL;

		$r .= "<script type='text/javascript' charset='utf-8'>$(document).ready(function() {
	$('.vz_url_field').blur(function() {
		field = $(this);
		$.get( '".FT_URL."ff_vz_url/proxy.php', {path: field.val()}, function(response) { if (response) { field.next().slideUp(); } else { field.next().slideDown(); } });
	});
});</script>".NL.NL;

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
		$SD = new Fieldframe_SettingsDisplay();
		
		// Fill in http:// if the field is empty
		$val = ($field_data) ? $field_data : 'http://';
		
		return $SD->text($field_name, $val, array('style' => 'vz_url_field'))
			  .'<p class="highlight" style="display:none">'.$this->site_settings['vz_url_error_text'].'</p>';
		
	}


	/**
	 * Save Field
	 * 
	 * @param  string  $field_name		The field's name
	 * @param  array  $field_settings	The field settings
	 */
	function save_field($field_data, $field_settings)
	{
		// Remove http:// if it's the only thing in the field
		return ($field_data ==  'http://') ? '' : $field_data;
	}

}


/* End of file ft.ff_vz_url.php */
/* Location: ./system/fieldtypes/ff_vz_instruct/ft.ff_vz_url.php */