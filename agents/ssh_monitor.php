<?php
/**
 * Exemplo de Coleta SSH (Linux)
 * Requer a extensão nativa ssh2 (pecl install ssh2) ou a biblioteca phpseclib.
 * Aqui simulamos usando a extensão nativa (ssh2).
 */

$servidores = [
    ['ip' => '192.168.1.10', 'user' => 'root', 'pass' => 'Senha123', 'port' => 22, 'name' => 'SRV-DB-LINUX'],
];

echo "Iniciando Coleta SSH...\n";

foreach ($servidores as $server) {
    echo "Consultando {$server['name']} ({$server['ip']})...\n";
    
    if (!function_exists('ssh2_connect')) {
        echo "[ERRO] Extensão SSH2 não instalada no PHP. Simulação iniciada.\n";
        // Simulação caso a extensão falte:
        echo "[MOCK] CPU Load: 15%\n";
        echo "[MOCK] Memória Livre: 2.5GB\n";
        echo "[MOCK] Disco / (Root) Livre: 45GB\n";
        echo "-----------------------------------\n";
        continue;
    }

    $conexao = @ssh2_connect($server['ip'], $server['port']);
    
    if (!$conexao) {
        echo "[ERRO] Não foi possível conectar ao servidor {$server['name']}\n";
        continue;
    }
    
    if (@ssh2_auth_password($conexao, $server['user'], $server['pass'])) {
        
        // Coletar Uptime
        $stream = ssh2_exec($conexao, 'uptime -p');
        stream_set_blocking($stream, true);
        $uptime = stream_get_contents($stream);
        echo "[OK] " . trim($uptime) . "\n";
        
        // Coletar Espaço em Disco do Root
        $stream = ssh2_exec($conexao, 'df -h / | awk \'NR==2 {print $4}\'');
        stream_set_blocking($stream, true);
        $disk_free = stream_get_contents($stream);
        echo "[OK] Espaço Livre no Disco (/): " . trim($disk_free) . "\n";
        
        // Coletar Memória Total/Livre (usando free -m)
        $stream = ssh2_exec($conexao, 'free -m | awk \'NR==2 {print $3/$2 * 100.0}\'');
        stream_set_blocking($stream, true);
        $mem_used_percent = stream_get_contents($stream);
        echo "[OK] Uso de Memória RAM: " . round(trim($mem_used_percent), 2) . "%\n";

    } else {
        echo "[ERRO] Falha de autenticação (Usuário/Senha) em {$server['name']}\n";
    }
    
    echo "-----------------------------------\n";
}
