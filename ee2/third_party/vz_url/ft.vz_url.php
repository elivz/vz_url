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
        'name'          => 'VZ URL',
        'version'       => '2.2'
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
            
            $this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $this->_theme_url() . 'styles/vz_url.css" />');
            $this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $this->_theme_url() . 'scripts/vz_url.js"></script>');
            $this->EE->javascript->output(
                'window.vzUrl_settings = {' .
                'errorText:"' . addslashes(lang('vz_url_error_text')) . '",' .
                'redirectText:"' . addslashes(lang('vz_url_redirect_text')) . '",' .
                'redirectUpdate:"' . addslashes(lang('vz_url_redirect_update')) . '",' .
                'nonlocalText:"' . addslashes(lang('vz_url_nonlocal_text')) . '",' .
                'proxyUrl:"' . $this->_theme_url() . 'proxy.php"' .
                '};'
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
     * Display Low Variable Settings
     */
    function display_var_settings($settings)
    {
        return $this->display_cell_settings($settings);
    }
    
    /**
     * Save Field Settings
     */
    function save_settings()
    {
        return array('vz_url_limit_local' => $this->EE->input->post('vz_url_limit_local'));
    }
    
    /**
     * Save Low Variables Settings
     */
    function save_var_settings()
    {
        return $this->save_settings();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Display Field on Publish
     */
    function display_field($data, $name=FALSE)
    {
        $this->_include_jscss();
        
        if (empty($name)) $name = $this->field_name;
        
        $limit_local = isset($this->settings['vz_url_limit_local']) && $this->settings['vz_url_limit_local'] == 'y';
        
        // Fill in http:// if the field is empty
        if (!$data)
        {
            $data = $limit_local ? $this->EE->config->item('site_url') : 'http://';
        }
        
        $out = '<div class="vz_url_wrapper">';
        $out .= form_input(array(
            'name' => $name,
            'value' => $data,
            'class' => 'vz_url_field' . ($limit_local ? ' local' : ''),
            'id' => $name
        ));
        $out .= '<div class="vz_url_msg"></div></div>';

        return $out;
    }
    
    /**
     * Display Cell
     */
    function display_cell($data)
    {
        return $this->display_field($data, $this->cell_name);
    }
    
    /**
     * Display Low Variable
     */
    function display_var_field($data)
    {
        return $this->display_field($data);
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
     * Save Low Variable
     */
    function save_var_field($data)
    {
        return $this->save($data);
    }
    
    /**
     * Use redirect="yes" parameter to immediately redirect the page 
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