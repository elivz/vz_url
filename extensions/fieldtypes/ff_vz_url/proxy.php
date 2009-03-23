<?php
// Proxy for checking if a remote page exists
// Used by the VZ URL extension

$session = curl_init($_GET['path']);

curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($session, CURLOPT_VERBOSE,false);
curl_setopt($session, CURLOPT_TIMEOUT, 5);

// Request the file
$response = curl_exec($session);
$httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);

curl_close($session);

// If the response code is in the 200s the page was valid
echo ($httpcode >= 200 && $httpcode < 300);

?>