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
        return $a;
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


// Start the actual function
$url = urldecode($_GET['path']);

// Create the CURL session and set options
$session = curl_init(urldecode(trim($url)));
curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($session, CURLOPT_VERBOSE, false);
curl_setopt($session, CURLOPT_TIMEOUT, 15);
curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($session, CURLOPT_MAXREDIRS, 5);

// Request the file
$content = curl_exec($session);
$info = curl_getinfo($session);
curl_close($session);

$return = array(
  'original' => $url,
  'final' => $info['url'],
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

?>