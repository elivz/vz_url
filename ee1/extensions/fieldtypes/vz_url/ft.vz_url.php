<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * VZ URL Class
 *
 * @package   FieldFrame
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2011 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
 
class Vz_url extends Fieldframe_Fieldtype {

	/**
	 * Fieldtype Info
	 * @var array
	 */
	var $info = array(
		'name'             => 'VZ URL',
		'version'          => '2.1.1',
		'desc'             => 'Textbox with ajax URL validation',
		'docs_url'         => 'http://elivz.com/blog/single/vz_url_extension/',
		'versions_xml_url' => 'http://elivz.com/files/versions.xml'
	);
	
	var $requires = array(
		'ff'        => '1.4.0',
		'cp_jquery' => '1.1.1',
	);
	
	/**
	 * Get the URL of the VZ URL files
	 */
	private function _theme_url()
	{
		if (!isset($this->_theme_url))
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
	 */
	private function _include_jscss()
	{
		if (!isset($this->_has_jscss))
		{
            global $LANG;
            $LANG->fetch_language_file('vz_url');
            
			$this->insert('head', '<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().'styles/vz_url.css" />');
			$this->insert('body', '<script type="text/javascript" src="'.$this->_theme_url().'scripts/vz_url.js"></script>');
			$this->insert_js(
				'vzUrl.errorText="'.addslashes($LANG->line('vz_url_error_text')).'";'.
				'vzUrl.redirectText="'.addslashes($LANG->line('vz_url_redirect_text')).'";' .
				'vzUrl.nonlocalText="'.addslashes($LANG->line('vz_url_nonlocal_text')).'";' .
				'vzUrl.proxyUrl="'.$this->_theme_url().'proxy.php";' .
				'vzUrl.init();'
			);
			
			$this->_has_jscss = TRUE;
		}
	}
	
	// --------------------------------------------------------------------
    
    /**
     * Create the settings UI
     */
    function _display_settings($settings)
    {
        global $LANG;
        $SD = new Fieldframe_SettingsDisplay();
		
        if (!isset($settings['vz_url_limit_local'])) $settings['vz_url_limit_local'] = 'n';
		
		$settings = array(
            $SD->label('vz_url_limit_local_label'),
            $SD->radio_group(
                'vz_url_limit_local',
                $settings['vz_url_limit_local'],
                array(
                    'y' => $LANG->line('yes'),
                    'n' => $LANG->line('no')
                )
            )
        );
        
        return array($settings);
    }
    
    /**
     * Display Field Settings
     */
    function display_field_settings($settings)
    {
        return array('rows' => $this->_display_settings($settings));
    }
    
	/**
	 * Display Cell Settings
	 */
    function display_cell_settings($settings)
    {
		return $this->_display_settings($settings);
    }
	
	// --------------------------------------------------------------------
    
	/**
	 * Display Field
	 */
	function display_field($field_name, $data, $settings)
	{
        global $PREFS;
		$SD = new Fieldframe_SettingsDisplay();
		
		$this->_include_jscss();
        
        $limit_local = isset($this->settings['vz_url_limit_local']) && $this->settings['vz_url_limit_local'] == 'y';
		
		// Fill in http:// if the field is empty
        if (!$data && $limit_local)
        {
            $data = $PREFS->ini('site_url');
        }
        elseif (!$data)
        {
            $data = 'http://';
        }
		
		return $SD->text($field_name, $data, array('style' => 'vz_url_field'.($limit_local ? ' local' : '').'', 'width' => ''));
	}
	
	/**
	 * Display Matrix Cell
	 */
	function display_cell($cell_name, $data, $settings)
	{
        return $this->display_field($cell_name, $data, $settings);
	}

	/**
	 * Save Field
	 */
	function save_field($data, $settings)
	{
		// Remove http:// if it's the only thing in the field
        return ($data == 'http://' || $data == '/') ? '' : $data;
	}

	/**
	 * Save Cell
	 */
	function save_cell($data, $settings)
	{
		return $this->save_field($data, $settings);
	}
	
	/**
	 * Use redirect="" parameter to immediately redirect the page 
	 * Thanks to Brian Litzinger for the idea and code
	 */
    function display_tag($params = '', $tagdata = '', $data)
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
/* Location: ./system/fieldtypes/vz_url/ft.vz_url.php */