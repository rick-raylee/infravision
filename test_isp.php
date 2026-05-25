<?php
$public_ip = '127.0.0.1';
$dispositivo_id = 999;
if ($public_ip) {
    $public_ip = trim(explode(',', $public_ip)[0]);
    
    if (filter_var($public_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        $public_ip = '';
    }

    $isp_cache_file = sys_get_temp_dir() . '/infravision_isp_' . $dispositivo_id . '.txt';
    echo "Cache file will be: $isp_cache_file\n";
    $apiUrl = 'http://ip-api.com/json/' . $public_ip;
    echo "API URL: $apiUrl\n";
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $res = curl_exec($ch);
    curl_close($ch);
    
    if ($res) {
        echo "Response: $res\n";
        $json = json_decode($res, true);
        if (isset($json['isp']) && !empty($json['isp'])) {
            file_put_contents($isp_cache_file, $json['isp']);
            echo "Saved ISP: " . $json['isp'] . "\n";
        }
    }
}
