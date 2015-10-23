<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ URL Fieldtype
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010-2015 Eli Van Zoeren
 * @license   http://opensource.org/licenses/MIT
 */

class Vz_url_ft extends EE_Fieldtype
{

    public $info = array(
        'name'    => 'VZ URL',
        'version' => VZ_URL_VERSION
    );

    public $has_array_data = true;


    // --------------------------------------------------------------------


    /**
     * Fieldtype Constructor
     */
    public function __construct()
    {
        parent::__construct();
        ee()->lang->loadfile('vz_url');
    }

    /*
     * Register acceptable content types
     */
    public function accepts_content_type($name)
    {
        return in_array($name, array('channel', 'grid', 'low_variables'));
    }


    // --------------------------------------------------------------------


    /**
     * Include the JS and CSS files, but only the first time
     */
    private function _include_js_css($content_type='field')
    {
        if ( ! ee()->session->cache(__CLASS__, 'js_css'))
        {
            // Output CSS and JS
            $css = file_get_contents(PATH_THIRD . '/vz_url/css/vz_url.css');
            ee()->cp->add_to_head('<style type="text/css">' . $css . '</style>');

            // Put various settings into an array for access from JS
            $action_id = ee()->cp->fetch_action_id('Vz_url', 'validate_url');
            $action_url = ee()->functions->fetch_site_index() . QUERY_MARKER . 'ACT=' . $action_id;
            $settings = json_encode(array(
                'actionUrl' => $action_url,
                'lang' => array(
                    'errorText' => lang('vz_url_error_text'),
                    'redirectText' => lang('vz_url_redirect_text'),
                    'redirectUpdate' => lang('vz_url_redirect_update'),
                    'openText' => lang('vz_url_open_text'),
                ),
            ));
            $js = file_get_contents(PATH_THIRD . '/vz_url/javascript/vz_url.js');
            ee()->javascript->output("window.vzUrlSettings=$settings;$js");

            // Make sure we only load them once
            ee()->session->set_cache(__CLASS__, 'js_css', TRUE);
        }
    }


    // --------------------------------------------------------------------


    /**
     * Generate the settings fields array
     */
    private function _settings_fields($settings) {
        $show_redirects = isset($settings['show_redirects']) && $settings['show_redirects'] == 'y';

        return array(
            // Prompt user to update redirected URLs
            array(
                'title' => 'vz_url_show_redirects_label',
                'fields' => array(
                    'show_redirects' => array(
                        'type' => 'yes_no',
                        'value' => $show_redirects
                    )
                )
            )
        );
    }

    /**
     * Display Field Settings
     */
    public function display_settings($settings)
    {
        return array('field_options_vz_url' => array(
            'label' => 'field_options',
            'group' => 'vz_url',
            'settings' => $this->_settings_fields($settings)
        ));
    }

    /**
     * Display Grid Cell Settings
     */
    public function grid_display_settings($settings)
    {
        return array(
            'field_options' => $this->_settings_fields($settings)
        );
    }

    // --------------------------------------------------------------------


    /**
     * Save Field Settings
     */
    public function save_settings($settings)
    {
        return array(
            'show_redirects' => $settings['show_redirects']
        );
    }

    /**
     * Save var Settings
     */
    public function var_save_settings($settings)
    {
        return array(
            'show_redirects' => ee('Request')->post('show_redirects')
        );
    }

    // --------------------------------------------------------------------


    /**
     * Display Field on Publish
     */
    public function display_field($data, $is_grid = FALSE)
    {
        $this->_include_js_css();

        $show_redirects = isset($this->settings['show_redirects']) && $this->settings['show_redirects'] == 'y';

        $out  = '<div class="vzurl-wrapper">';
        $out .= form_input(array(
            'name' => $this->field_name,
            'value' => $data,
            'class' => 'vzurl-field' . ($show_redirects ? ' follow-redirects' : ''),
            'id' => $this->field_name,
            'placeholder' => 'http://'
        ));
        $out .= '<em class="vzurl-msg"></em></div>';

        if (!$is_grid) {
            ee()->javascript->output("new VzUrl($('#{$this->field_name}'));");
        }

        return $out;
    }

    /**
     * Display Grid Field
     */
    public function grid_display_field($data)
    {
        return $this->display_field($data, TRUE);
    }

    /**
     * Display Low Variable (required for now)
     */
    public function var_display_field($data)
    {
        return $this->display_field($data);
    }


    // --------------------------------------------------------------------


    /**
     * Validate Field
     */
    public function validate($data)
    {
        if (
            (isset($this->settings['field_required']) && $this->settings['field_required'] == 'y')
            ||
            (isset($this->settings['col_required']) && $this->settings['col_required'] == 'y' )
        ) {
            if ($data == '' || $data == 'http://')
            {
                return lang('required');
            }
        }

        return TRUE;
    }


    // --------------------------------------------------------------------


    /**
     * Save Field
     */
    public function save($data)
    {
        // Remove http:// if it's the only thing in the field
        return ($data == 'http://' || $data == 'https://') ? '' : $data;
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

            return ee()->TMPL->parse_variables_row($tagdata, $parts);
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
    public function var_replace_tag($data, $params=array(), $tagdata=FALSE)
    {
        $method = 'replace_' . (isset($params['modifier']) ? $params['modifier'] : 'tag');

        return (method_exists($this, $method))
            ? $this->$method($data, $params, $tagdata)
            : FALSE;
    }
}
