<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ URL Module
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2010-2014 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

class Vz_url {

    /**
     * Proxy function for checking URLs
     */
    public function validate_url()
    {
        // Check to see if it's a request from VZ URL
        if (
            AJAX_REQUEST &&
            ee()->input->get('url') &&
            ee()->input->get('callback')
        )
        {
            ee()->load->library('javascript');

            $url = trim($_GET['url']);
            $host = '';

            // If the url is relative to the root,
            // convert to an absolute url
            if (substr($url, 0, 1) == '/')
            {
                $host = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://".$_SERVER['SERVER_NAME'] : "http://".$_SERVER['SERVER_NAME'];
                $url = $host . $url;
            }
            else
            {
                $url = 'http' . $url;
            }

            // Create the CURL session and set options
            $session = curl_init($url);

            curl_setopt($session, CURLOPT_HEADER, true);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_VERBOSE, false);
            curl_setopt($session, CURLOPT_TIMEOUT, 15);
            curl_setopt($session, CURLOPT_MAXREDIRS, 8);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

            // Spoof a real browser, or Facebook redirects to an error page
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

            $return = json_encode(array(
                'original'  => str_replace($host, '', $url),
                'final_url' => str_replace($host, '', $info['url']),
                'http_code' => $info['http_code']
            ));

            // Kill processing before EE can output anything
            header('Content-Type: application/javascript');
            exit($_GET['callback'] . '(' . $return . ');');
        }
    }
}

// Recursively follow redirects
// Adapted from the PHP docs
function curl_redirect_exec($session)
{
    $data = curl_exec($session);
    $info = curl_getinfo($session);

    if ($info['http_code'] == 301 || $info['http_code'] == 302)
    {
        list($header) = explode("\r\n\r\n", $data, 2);
        $matches = array();
        preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        $url_parsed = parse_url($url);
        if (isset($url_parsed))
        {
            curl_setopt($session, CURLOPT_URL, $url);
            return curl_redirect_exec($session);
        }
    }

    return $info;
}

/* End of file mod.vz_url.php */