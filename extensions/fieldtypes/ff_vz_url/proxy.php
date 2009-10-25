<?php
/*
 * Proxy for checking if a remote page exists
 * Used by the VZ URL extension
 */ 

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

echo json_encode($return);

?>