<?php

class ServiceMonitorController {
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        $urls_to_check = [
            ['nome' => 'Rodomax Atua', 'url' => 'https://rodomax.atua.com.br/']
        ];
        
        $resultados_urls = [];
        foreach ($urls_to_check as $site) {
            $ch = curl_init($site['url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $start_time = microtime(true);
            curl_exec($ch);
            $end_time = microtime(true);
            
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $latency = round(($end_time - $start_time) * 1000);
            curl_close($ch);
            
            $status_class = ($http_code >= 200 && $http_code < 400) ? 'bg-success' : 'bg-danger';
            $status_text = $http_code ? $http_code : 'Offline';
            if ($http_code == 200) $status_text = '200 OK';
            
            $resultados_urls[] = [
                'nome' => $site['nome'],
                'url' => $site['url'],
                'status_class' => $status_class,
                'status_text' => $status_text,
                'latency' => $latency . 'ms',
                'uptime' => '100%'
            ];
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/servicemonitor/index.php';
        require 'app/views/layout/footer.php';
    }
}
