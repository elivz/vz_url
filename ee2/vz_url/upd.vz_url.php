<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ URL Module Install/Update File
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010-2014 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

class Vz_url_upd {

    public $version = '2.4.3';

    // ----------------------------------------------------------------

    /**
     * Install Module
     *
     * @return  boolean     TRUE
     */
    public function install()
    {
        // Install the module
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


    // ----------------------------------------------------------------


    /**
     * Update Module
     *
     * @return  boolean     TRUE
     */
    public function update($current = '')
    {
        return FALSE;
    }


    // ----------------------------------------------------------------


    /**
     * Uninstall Module
     *
     * @return  boolean     TRUE
     */
    public function uninstall()
    {
        ee()->db->where('module_name', 'Vz_url')
            ->delete('modules');

        ee()->db->where('class', 'Vz_url')
            ->delete('actions');

        return TRUE;
    }

}

/* End of file upd.vz_url.php */