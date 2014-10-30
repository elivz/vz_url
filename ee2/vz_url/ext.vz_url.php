<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ URL Extension
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010-2014 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

class Vz_url_ext {

    public $name           = 'VZ URL - Deprecated, do not install';
    public $description    = 'Support file for VZ URL fieldtype';
    public $docs_url       = 'https://github.com/elivz/vz_url';
    public $settings_exist = 'n';
    public $version        = '2.4.3';


    // ----------------------------------------------------------------------


    /**
     * Activate Extension
     */
    public function activate_extension()
    {
        $data = array(
            'class'    => __CLASS__,
            'method'   => 'proxy',
            'hook'     => 'sessions_start',
            'settings' => '',
            'version'  => $this->version,
            'enabled'  => 'y'
        );

        // Enable the extension
        ee()->db->insert('extensions', $data);
    }

    /**
     * Update Extension
     */
    public function update_extension($current = '')
    {
        if ($current < '2.4.0')
        {
            // The extension is not needed as of 2.4.0, so uninstall it
            ee()->db->where('class', 'Vz_url_ext');
            ee()->db->delete('extensions');

            // Install the new module
            $mod_data = array(
                'module_name'           => 'Vz_url',
                'module_version'        => $this->version,
                'has_cp_backend'        => 'n',
                'has_publish_fields'    => 'n'
            );
            ee()->db->insert('modules', $mod_data);

            // Add the validation action
            $data = array(
                'class'     => 'Vz_url' ,
                'method'    => 'validate_url'
            );
            ee()->db->insert('actions', $data);

            return TRUE;
        }
    }

    /**
     * Disable Extension
     */
    public function disable_extension()
    {
        // Remove the extension settings
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
    }


    // --------------------------------------------------------------------


    public function proxy()
    {

    }

}

/* End of file ext.vz_url.php */