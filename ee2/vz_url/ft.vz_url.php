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
        'version' => '2.3.3'
    );

    var $has_array_data = TRUE;

    var $debug = FALSE;

    /**
     * Fieldtype Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->EE->lang->loadfile('vz_url');
    }

    /*
     * Register acceptable content types
     */
    public function accepts_content_type($name)
    {
        return ($name == 'channel' || $name == 'grid');
    }


    // --------------------------------------------------------------------


    /**
     * Include the JS and CSS files,
     * but only the first time
     */
    private function _include_jscss($content_type='field')
    {
        if ( ! $this->EE->session->cache(__CLASS__, 'jscss'))
        {
            $css = file_get_contents(PATH_THIRD . '/vz_url/assets/styles' . ($this->debug ? '' : '.min') . '.css');
            $css = str_replace('IMAGE_URL', PATH_CP_GBL_IMG, $css);
            $this->EE->cp->add_to_head('<style type="text/css">' . $css . '</style>');

            $scripts = file_get_contents(PATH_THIRD . '/vz_url/assets/scripts' . ($this->debug ? '' : '.min') . '.js');
            $this->EE->javascript->output(
                'var vzUrl_settings={' .
                'errorText:"' . addslashes(lang('vz_url_error_text')) . '",' .
                'redirectText:"' . addslashes(lang('vz_url_redirect_text')) . '",' .
                'redirectUpdate:"' . addslashes(lang('vz_url_redirect_update')) . '",' .
                'nonlocalText:"' . addslashes(lang('vz_url_nonlocal_text')) . '",' .
                'openText:"' . addslashes(lang('vz_url_open_text')) . '"' .
                '};'.
                $scripts
            );

            $this->EE->session->set_cache(__CLASS__, 'jscss', TRUE);
        }
    }


    // --------------------------------------------------------------------


    /**
     * Display Field Settings
     */
    public function display_settings($settings)
    {
        $this->EE->load->library('table');

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
     * Display Grid Cell Settings
     */
    public function grid_display_settings($data)
    {
        $show_redirects = ! (isset($data['vz_url_show_redirects']) && $data['vz_url_show_redirects'] != 'y');
        $limit_local = isset($data['vz_url_limit_local']) && $data['vz_url_limit_local'] == 'y';

        return array(
            $this->grid_checkbox_row(
                lang('vz_url_show_redirects_label'),
                'vz_url_show_redirects',
                'y',
                $show_redirects
            ),
            $this->grid_checkbox_row(
                lang('vz_url_limit_local_label'),
                'vz_url_limit_local',
                'y',
                $limit_local
            )
        );
    }

    /**
     * Display Matrix Cell Settings
     */
    public function display_cell_settings($data)
    {
        $show_redirects = ! (isset($settings['vz_url_show_redirects']) && $settings['vz_url_show_redirects'] != 'y');
        $limit_local = isset($settings['vz_url_limit_local']) && $settings['vz_url_limit_local'] == 'y';

        return array(
            array(
                lang('vz_url_show_redirects_label', 'vz_url_show_redirects'),
                form_checkbox('vz_url_show_redirects', 'y', $show_redirects)
            ),
            array(
                lang('vz_url_limit_local_label', 'vz_url_limit_local'),
                form_checkbox('vz_url_limit_local', 'y', $limit_local)
            )
        );
    }

    /**
     * Display Low Variable Settings
     */
    public function display_var_settings($settings)
    {
        return $this->display_cell_settings($settings);
    }


    // --------------------------------------------------------------------


    /**
     * Save Field Settings
     */
    public function save_settings()
    {
        return array(
            'vz_url_show_redirects' => $this->EE->input->post('vz_url_show_redirects'),
            'vz_url_limit_local'    => $this->EE->input->post('vz_url_limit_local')
        );
    }

    /**
     * Save Low Variables Settings
     */
    public function save_var_settings()
    {
        return $this->save_settings();
    }


    // --------------------------------------------------------------------


    /**
     * Display Field on Publish
     */
    public function display_field($data, $name=FALSE)
    {
        $this->_include_jscss();

        if (empty($name)) $name = $this->field_name;

        $show_redirects = !(isset($this->settings['vz_url_show_redirects']) && $this->settings['vz_url_show_redirects'] == 'n');
        $limit_local = isset($this->settings['vz_url_limit_local']) && $this->settings['vz_url_limit_local'] == 'y';

        // Fill in http:// if the field is empty
        if (!$data)
        {
            $data = $limit_local ? '' : 'http://';
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
    public function display_cell($data)
    {
        $this->_include_jscss(TRUE);
        $data = str_replace('&amp;', '&', $data);
        return $this->display_field($data, $this->cell_name);
    }

    /**
     * Display Low Variable
     */
    public function display_var_field($data)
    {
        return $this->display_field($data);
    }


    // --------------------------------------------------------------------


    /**
     * Validate Field
     */
    public function validate($data)
    {
        if ($this->settings['field_required'] == 'y')
        {
            if ($data == '' || $data == 'http://')
            {
                return lang('required');
            }
        }

        return TRUE;
    }

    /**
     * Save Field
     */
    public function save($data)
    {
        // Remove http:// if it's the only thing in the field
        return ($data == 'http://' || $data == 'http://') ? '' : $data;
    }

    /**
     * Save Cell
     */
    public function save_cell($data)
    {
        return $this->save($data);
    }

    /**
     * Save Low Variable
     */
    public function save_var_field($data)
    {
        return $this->save($data);
    }


    // --------------------------------------------------------------------


    /**
     * Parse template tag
     *
     * Use as tag pair to make the URL's component parts available
     */
    public function replace_tag($data, $params=array(), $tagdata=FALSE)
    {
        if ($data == '') return;

        if ($tagdata)
        {
            $parts = array_merge(
                array(
                    'url'      => $data,
                    'the_url'  => $data,
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
            if (isset($params['redirect']) && $params['redirect'] == 'yes')
            {
                $this->replace_redirect($data);
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
    public function replace_link($data, $params=array(), $tagdata=FALSE)
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

    /**
     * Immediately redirect to the URL
     * Thanks to Brian Litzinger for the idea and code
     */
    public function replace_redirect($data, $params=array(), $tagdata=FALSE)
    {
        if ($data == '') return;

        header("Location: {$data}");
        exit;
    }

    /**
     * Replace the tag for Low Variables
     */
    public function display_var_tag($data, $params=array(), $tagdata=FALSE)
    {
        return $this->replace_tag($data, $params, $tagdata);
    }
}

/* End of file ft.vz_url.php */