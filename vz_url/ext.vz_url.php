<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ URL Extension
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010-2012 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

class Vz_url_ext {
    
    public $description    = 'Support file for VZ URL fieldtype';
    public $docs_url       = 'http://elivz.com/blog/single/vz_url_extension/';
    public $name           = 'VZ URL';
    public $settings_exist = 'n';
    public $version        = '2.2.0';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->EE =& get_instance();
    }
    
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
        $this->EE->db->insert('extensions', $data);
    }

    /**
     * Update Extension
     */
    public function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }
    
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->update('extensions', array('version' => $this->version));
    }

    /**
     * Disable Extension
     */
    public function disable_extension()
    {
        // Remove the extension settings
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('extensions');
    }
    
    // ----------------------------------------------------------------------
    
    /**
     * Proxy function for checking URLs
     */
    public function proxy($session)
    {
        // Check for the special query string that means we want the log
        if (
            AJAX_REQUEST &&
            $this->EE->input->get('url') && 
            $this->EE->input->get('caller') == 'vz_url'
        )
        {
            $url = urldecode(trim($_GET['url']));
            $prefix = '';

            // If the url is relative to the root, 
            // convert to an absolute url
            if (substr($url, 0, 1) == '/')
            {
                $prefix = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'] : "http://".$_SERVER['SERVER_NAME'];
            }

            // Create the CURL session and set options
            $session = curl_init($prefix.$url);

            curl_setopt($session, CURLOPT_HEADER, true);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_VERBOSE, false);
            curl_setopt($session, CURLOPT_TIMEOUT, 15);
            curl_setopt($session, CURLOPT_MAXREDIRS, 8);

            // Spoof a real browser, or Facebook freaks out
            curl_setopt($session, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');

            if (!ini_get('safe_mode') && !ini_get('open_basedir'))
            {
                // open_basedir is off, request the location normally
                curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
                curl_exec($session);
                $info = curl_getinfo($session);
            }
            else
            {
                // When open_basedir is set, we need to use a 
                // recursive function to follow the redirects
                $info = curl_redirect_exec($session);
            }

            curl_close($session);

            $return = array(
                'original'  => $url,
                'final_url' => str_replace($prefix, '', $info['url']),
                'http_code' => $info['http_code']
            );

            if ($_GET['callback'])
            {
                echo $_GET['callback'] . '(' . json_encode($return) . ');';
            }
            else
            {
                echo json_encode($return);
            }
            die();
        }
    }
}

/* End of file ext.vz_url.php */
/* Location: /system/expressionengine/third_party/vz_url/ext.vz_url.php */