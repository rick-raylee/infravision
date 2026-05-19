<?php
/**
 * Exemplo de Coleta SNMP (v1/v2c)
 * Funciona nativamente se a extensão php_snmp estiver ativada.
 */

// IPs e Comunidades de exemplo (Na prática buscaria do Banco de Dados)
$switches = [
    ['ip' => '192.168.1.254', 'community' => 'public', 'name' => 'SW-CORE-01'],
];

echo "Iniciando Coleta SNMP...\n";

foreach ($switches as $device) {
    echo "Consultando {$device['name']} ({$device['ip']})...\n";
    
    // Suprimindo avisos se o host estiver offline
    $sysName = @snmpget($device['ip'], $device['community'], "sysName.0");
    $sysUpTime = @snmpget($device['ip'], $device['community'], "sysUpTime.0");
    
    if ($sysName === false) {
        echo "[ERRO] Não foi possível conectar via SNMP ao dispositivo {$device['name']}.\n";
        // Inserir log de falha/alerta no banco de dados
        continue;
    }
    
    // Limpando saída
    $sysName = str_replace('STRING: ', '', $sysName);
    $sysUpTime = str_replace('Timeticks: ', '', $sysUpTime);
    
    echo "[OK] Nome do Sistema: {$sysName}\n";
    echo "[OK] Uptime: {$sysUpTime}\n";
    
    // Exemplo: Coletando status das interfaces (ifOperStatus)
    // 1 = up, 2 = down
    $interfaces = @snmpwalk($device['ip'], $device['community'], "ifOperStatus");
    if ($interfaces !== false) {
        $up = 0;
        $down = 0;
        foreach($interfaces as $status) {
            if (strpos($status, 'up(1)') !== false) $up++;
            if (strpos($status, 'down(2)') !== false) $down++;
        }
        echo "[INFO] Interfaces - Up: $up | Down: $down\n";
    }
    
    echo "-----------------------------------\n";
}
