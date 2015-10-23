<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ URL Module Install/Update File
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010-2015 Eli Van Zoeren
 * @license   http://opensource.org/licenses/MIT
 */

class Vz_url_upd
{

    public $version = '3.0.0';

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

        return true;
    }


    // ----------------------------------------------------------------


    /**
     * Update Module
     *
     * @return  boolean     TRUE
     */
    public function update($current = '')
    {
        return false;
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

        return true;
    }

}
