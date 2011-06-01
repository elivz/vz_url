<?php
/*
 * Proxy for checking if a remote page exists
 * Used by the VZ URL extension
 */ 


// JSON_encode replacement for PHP < 5.2
// From PHP docs
if (!function_exists('json_encode'))
{
    function json_encode($a=false)
    {
        if (is_null($a)) return 'null';
        if ($a === false) return 'false';
        if ($a === true) return 'true';
        if (is_scalar($a))
        {
            if (is_float($a))
            {
                // Always use "." for floats.
                return floatval(str_replace(",", ".", strval($a)));
            }
            
            if (is_string($a))
            {
                static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
            }
            else
            {
                return $a;
            }
        }
        $isList = true;
        for ($i = 0, reset($a); $i < count($a); $i++, next($a))
        {
            if (key($a) !== $i)
            {
                $isList = false;
                break;
            }
        }
        $result = array();
        if ($isList)
        {
            foreach ($a as $v) $result[] = json_encode($v);
            return '[' . join(',', $result) . ']';
        }
        else
        {
            foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
            return '{' . join(',', $result) . '}';
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


/*----------------------------------------------------------------*
 * Start the actual url check
 *----------------------------------------------------------------*/

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
    'final'     => str_replace($prefix, '', $info['url']),
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