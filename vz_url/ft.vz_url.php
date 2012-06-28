<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ URL Fieldtype
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010-2012 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
 
class Vz_url_ft extends EE_Fieldtype {

    public $info = array(
        'name'    => 'VZ URL',
        'version' => '2.2.0'
    );
    
    var $has_array_data = TRUE;

    var $debug = FALSE;
    
    /**
     * Fieldtype Constructor
     */
    function Vz_url_ft()
    {
        parent::EE_Fieldtype();

        if (!isset($this->EE->session->cache['vz_url']))
        {
            $this->EE->session->cache['vz_url'] = array('jscss' => FALSE);
        }
        $this->cache =& $this->EE->session->cache['vz_url'];
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Include the JS and CSS files,
     * but only the first time
     */
    private function _include_jscss()
    {
        if (!$this->cache['jscss'])
        {
            $this->EE->lang->loadfile('vz_url');

            $css = file_get_contents(PATH_THIRD . '/vz_url/assets/styles' . ($this->debug ? '' : '.min') . '.css');
            $css = str_replace('IMAGE_URL', PATH_CP_GBL_IMG, $css);
            $this->EE->cp->add_to_head('<style type="text/css">' . $css . '</style>');

            $scripts = file_get_contents(PATH_THIRD . '/vz_url/assets/scripts' . ($this->debug ? '' : '.min') . '.js');
            $scripts = str_replace('CP_URL', BASE, $scripts);
            $this->EE->javascript->output(
                $scripts .
                'window.vzUrl_settings = {' .
                'errorText:"' . addslashes(lang('vz_url_error_text')) . '",' .
                'redirectText:"' . addslashes(lang('vz_url_redirect_text')) . '",' .
                'redirectUpdate:"' . addslashes(lang('vz_url_redirect_update')) . '",' .
                'nonlocalText:"' . addslashes(lang('vz_url_nonlocal_text')) . '",' .
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
        
        // Prompt user to update redirected URLs
        $show_redirects = !(isset($settings['vz_url_show_redirects']) && $settings['vz_url_show_redirects'] == 'n');
        $settings_ui = array(
            lang('vz_url_show_redirects_label', 'vz_url_show_redirects'),
            form_radio('vz_url_show_redirects', 'y', $show_redirects, 'id="vz_url_show_redirects_yes"') . ' ' .
            form_label(lang('yes'), 'vz_url_show_redirects_yes') .
            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
            form_radio('vz_url_show_redirects', 'n', !$show_redirects, 'id="vz_url_show_redirects_no"') . ' ' .
            form_label(lang('no'), 'vz_url_show_redirects_no')
        );
        $this->EE->table->add_row($settings_ui);
        
        // Limit to local URLs
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
        $this->EE->lang->loadfile('vz_url');
        $settings_ui = array();
        
        // Prompt user to update redirected URLs
        $show_redirects = !(isset($settings['vz_url_show_redirects']) && $settings['vz_url_show_redirects'] != 'y');
        $settings_ui[] = array(
            lang('vz_url_show_redirects_label', 'vz_url_show_redirects'),
            form_checkbox('vz_url_show_redirects', 'y', $show_redirects)
        );

        // Limit to local URLs
        $limit_local = isset($settings['vz_url_limit_local']) && $settings['vz_url_limit_local'] == 'y';
        $settings_ui[] = array(
            lang('vz_url_limit_local_label', 'vz_url_limit_local'),
            form_checkbox('vz_url_limit_local', 'y', $limit_local)
        );
        
        return $settings_ui;
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
        return array(
            'vz_url_show_redirects' => $this->EE->input->post('vz_url_show_redirects'),
            'vz_url_limit_local'    => $this->EE->input->post('vz_url_limit_local')
        );
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
        
        $show_redirects = !(isset($this->settings['vz_url_show_redirects']) && $this->settings['vz_url_show_redirects'] == 'n');
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
            'class' => 'vz_url_field' . ($limit_local ? ' local' : ''). ($show_redirects ? ' show_redirect' : ''),
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
    
    // --------------------------------------------------------------------

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
    
    // --------------------------------------------------------------------
    
    /**
     * Parse template tag
     * 
     * Use redirect="yes" parameter to immediately redirect the page 
     * Thanks to Brian Litzinger for the idea and code
     *
     * Use as tag pair to make the URL's component parts available
     */
    function replace_tag($data, $params=array(), $tagdata=FALSE)
    {
        if ($data == '') return;

        if ($tagdata)
        {
            $parts = array_merge(
                array(
                    'url'      => $data,
                    'scheme'   => '',
                    'host'     => '',
                    'port'     => '',
                    'user'     => '',
                    'pass'     => '',
                    'path'     => '',
                    'query'    => '',
                    'fragment' => ''
                ),
                parse_url($data)
            );

            return $this->EE->TMPL->parse_variables_row($tagdata, $parts);
        }
        else
        {
            if ($this->EE->TMPL->fetch_param('redirect') == 'yes')
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

    /**
     * Output an HTML <a> tag
     */
    function replace_link($data, $params=array(), $tagdata=FALSE)
    {
        if ($data == '') return;

        $out = '<a href="' . $data . '"';

        foreach (array('accesskey', 'class', 'id', 'rel', 'tabindex', 'target', 'title') as $attr)
        {
            if (isset($params[$attr]))
            {
                $out .= ' ' . $attr . '="' . $params[$attr] . '"';
            }
        }

        $text = isset($params['text']) ? $params['text'] : $data;
        $out .= '>' . $text . '</a>';

        return $out;
    }
}

/* End of file ft.vz_url.php */