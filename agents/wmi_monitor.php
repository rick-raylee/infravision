<?php
/**
 * Exemplo de Coleta WMI (Windows Management Instrumentation)
 * Nota: Funciona nativamente em PHP rodando no Windows usando a classe COM.
 * Para acessar remotamente, as permissões WMI, DCOM e Firewall devem estar configuradas no alvo.
 */

$servidores = [
    ['ip' => '127.0.0.1', 'user' => 'Administrator', 'pass' => 'Senha123', 'name' => 'SRV-LOCAL'],
];

echo "Iniciando Coleta WMI...\n";

foreach ($servidores as $server) {
    echo "Consultando {$server['name']} ({$server['ip']})...\n";
    
    try {
        if (class_exists('COM')) {
            // Conexão Local (sem user/pass) ou Remota
            if ($server['ip'] == '127.0.0.1' || $server['ip'] == 'localhost') {
                $wmi = new COM('winmgmts:{impersonationLevel=impersonate}//./root/cimv2');
            } else {
                $locator = new COM("WbemScripting.SWbemLocator");
                $wmi = $locator->ConnectServer($server['ip'], 'root\cimv2', $server['user'], $server['pass']);
            }
            
            // Exemplo 1: Uso de CPU
            $cpuInfo = $wmi->ExecQuery("SELECT LoadPercentage FROM Win32_Processor");
            $cpuLoad = 0;
            foreach ($cpuInfo as $cpu) {
                $cpuLoad = $cpu->LoadPercentage;
                break;
            }
            echo "[OK] CPU Load: {$cpuLoad}%\n";
            
            // Exemplo 2: Espaço em Disco (Drive C:)
            $disks = $wmi->ExecQuery("SELECT Size, FreeSpace, DeviceID FROM Win32_LogicalDisk WHERE DeviceID='C:'");
            foreach ($disks as $disk) {
                $sizeGB = round($disk->Size / 1073741824, 2);
                $freeGB = round($disk->FreeSpace / 1073741824, 2);
                $percentFree = round(($freeGB / $sizeGB) * 100, 2);
                echo "[OK] Disco C: - Total: {$sizeGB}GB | Livre: {$freeGB}GB ({$percentFree}% livre)\n";
            }
            
            // Exemplo 3: Serviços Críticos (Spooler como exemplo)
            $services = $wmi->ExecQuery("SELECT State FROM Win32_Service WHERE Name='Spooler'");
            foreach ($services as $service) {
                echo "[OK] Serviço Spooler: {$service->State}\n";
            }
            
        } else {
            echo "[ERRO] Extensão COM não habilitada no PHP (necessária para WMI).\n";
            break;
        }
    } catch (Exception $e) {
        echo "[ERRO] Falha WMI no servidor {$server['name']}: " . $e->getMessage() . "\n";
    }
    
    echo "-----------------------------------\n";
}
