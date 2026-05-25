<?php
header('Content-Type: text/plain');

$url = 'http://127.0.0.1:11434/api/tags';
echo "Testing connection to $url\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Redirect verbose output to a temporary stream
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response Length: " . ($response ? strlen($response) : 0) . " bytes\n";
echo "CURL Error: $error\n\n";

rewind($verbose);
$verboseLog = stream_get_contents($verbose);
echo "Verbose Log:\n" . $verboseLog . "\n";
fclose($verbose);
