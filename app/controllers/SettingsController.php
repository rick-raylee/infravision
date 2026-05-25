<?php

class SettingsController {
    
    public function index() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: " . BASE_PATH . "/dashboard");
            exit;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        
        // Carregar modelos instalados do Ollama para o dropdown
        $ollama_url = getenv('OLLAMA_API_URL') ?: 'http://127.0.0.1:11434';
        $ch_models = curl_init($ollama_url . '/api/tags');
        curl_setopt($ch_models, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_models, CURLOPT_TIMEOUT, 3);
        $models_response = curl_exec($ch_models);
        curl_close($ch_models);

        $installed_models = [];
        if ($models_response) {
            $models_data = json_decode($models_response, true);
            if (isset($models_data['models'])) {
                $installed_models = array_column($models_data['models'], 'name');
            }
        }

        // Verificar status de sucesso do salvamento
        $settings_saved = false;
        if (isset($_SESSION['settings_saved'])) {
            $settings_saved = true;
            unset($_SESSION['settings_saved']);
        }

        require 'app/views/layout/header.php';
        require 'app/views/settings/index.php';
        require 'app/views/layout/footer.php';
    }

    public function save() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: " . BASE_PATH . "/dashboard");
            exit;
        }

        // Chaves permitidas para salvamento
        $keys = [
            'SNMP_IP', 'SNMP_COMMUNITY', 'SNMP_VERSION', 'SNMP_OID_TEMP', 'SNMP_OID_HUM',
            'SMTP_HOST', 'SMTP_USER', 'SMTP_PORT', 'SMTP_PASS',
            'TELEGRAM_BOT_TOKEN', 'TELEGRAM_CHAT_ID',
            'WHATSAPP_URL', 'WHATSAPP_TOKEN', 'WHATSAPP_NUMBER',
            'OLLAMA_API_URL', 'OLLAMA_MODEL', 'AGENT_API_TOKEN'
        ];

        $newData = [];
        foreach ($keys as $key) {
            if (isset($_POST[strtolower($key)])) {
                // CORREÇÃO (Segurança): Sanitizar contra injeção de nova linha no .env
                $newData[$key] = str_replace(["\r", "\n"], '', trim($_POST[strtolower($key)]));
            }
        }

        $envFile = __DIR__ . '/../../.env';
        $existing = [];

        // Ler arquivo .env atual
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($k, $v) = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v);
                    if (preg_match('/^["\'](.*)["\']$/', $v, $matches)) {
                        $v = $matches[1];
                    }
                    $existing[$k] = $v;
                }
            }
        }

        // Mesclar novos dados
        foreach ($newData as $k => $v) {
            $existing[$k] = $v;
        }

        // Escrever conteúdo atualizado de volta para o arquivo .env
        $content = "";
        foreach ($existing as $k => $v) {
            $content .= "{$k}={$v}\n";
        }

        file_put_contents($envFile, $content);

        // Atualizar variáveis na sessão atual do script
        foreach ($existing as $k => $v) {
            putenv("{$k}={$v}");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }

        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        $_SESSION['settings_saved'] = true;
        header("Location: " . $base_path . "/settings");
        exit;
    }
}
