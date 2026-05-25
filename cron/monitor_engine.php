<?php
/**
 * Infravision - Monitor Engine for Plugins
 * Run this via cron or task scheduler every 1-5 minutes
 */

// Adiciona compatibilidade para rodar tanto via CLI quanto WEB
$base_dir = dirname(__DIR__);
require_once $base_dir . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "[INFO] Atualizando status inativo/offline dos dispositivos...\n";
try {
    // Se passar de 2 minutos sem check-in, muda de Online para Inativo
    $queryInativo = "UPDATE dispositivos 
                     SET status = 'Inativo' 
                     WHERE ultimo_check < DATE_SUB(NOW(), INTERVAL 2 MINUTE) 
                     AND status = 'Online'";
    $stmt1 = $conn->prepare($queryInativo);
    $stmt1->execute();

    // Se passar de 5 minutos sem check-in, muda para Offline (Desligado)
    $queryOffline = "UPDATE dispositivos 
                     SET status = 'Offline' 
                     WHERE ultimo_check < DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                     AND status != 'Offline'";
    $stmt2 = $conn->prepare($queryOffline);
    $stmt2->execute();
} catch (Exception $e) {
    echo "[ERRO] Falha ao atualizar status dos dispositivos: " . $e->getMessage() . "\n";
}

echo "[INFO] Iniciando verificacao de plugins...\n";

// Buscar sensores do tipo 'plugin' que estao ativos
$query = "SELECT s.id as sensor_id, s.nome as sensor_nome, s.plugin_command, 
                 d.id as dispositivo_id, d.ip, d.nome as dispositivo_nome 
          FROM sensores s
          JOIN dispositivos d ON s.dispositivo_id = d.id
          WHERE s.tipo = 'plugin' AND s.ativo = TRUE AND d.status != 'offline'";

$stmt = $conn->prepare($query);
$stmt->execute();
$sensores = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($sensores) == 0) {
    echo "[INFO] Nenhum sensor do tipo plugin ativo encontrado.\n";
    exit;
}

foreach ($sensores as $sensor) {
    echo "[TEST] Verificando Sensor '{$sensor['sensor_nome']}' no dispositivo {$sensor['dispositivo_nome']}...\n";
    
    $command = $sensor['plugin_command'];
    if (empty($command)) {
        echo "       [ERRO] Comando do plugin esta vazio.\n";
        continue;
    }

    // Substituir {ip} pelo IP do dispositivo
    $command = str_replace('{ip}', $sensor['ip'], $command);
    
    // Suporte a caminhos relativos
    if (strpos($command, 'plugins/') === 0) {
        $command = $base_dir . '/' . $command;
    }

    $output = [];
    $return_var = 0;
    
    // Executa o comando
    exec($command, $output, $return_var);
    
    $output_text = isset($output[0]) ? implode("\n", $output) : "Sem output";

    $severidade = 'info';
    $status_alerta = 'resolvido';
    
    switch ($return_var) {
        case 0:
            echo "       [OK] Retorno 0. $output_text\n";
            $severidade = 'info';
            $status_alerta = 'resolvido';
            break;
        case 1:
            echo "       [WARNING] Retorno 1. $output_text\n";
            $severidade = 'aviso';
            $status_alerta = 'ativo';
            break;
        case 2:
            echo "       [CRITICAL] Retorno 2. $output_text\n";
            $severidade = 'critico';
            $status_alerta = 'ativo';
            break;
        case 3:
        default:
            echo "       [UNKNOWN] Retorno $return_var. $output_text\n";
            $severidade = 'erro';
            $status_alerta = 'ativo';
            break;
    }
    
    // Registra uma leitura
    $queryLeitura = "INSERT INTO leituras (sensor_id, valor) VALUES (:sensor_id, :valor)";
    $stmtLeitura = $conn->prepare($queryLeitura);
    $stmtLeitura->execute([':sensor_id' => $sensor['sensor_id'], ':valor' => $return_var]);

    // Gerencia os Alertas
    if ($status_alerta === 'ativo') {
        // Verifica se ja existe alerta ativo para este sensor
        $checkAlert = "SELECT id FROM alertas WHERE sensor_id = :sensor_id AND status != 'resolvido'";
        $stmtCheck = $conn->prepare($checkAlert);
        $stmtCheck->execute([':sensor_id' => $sensor['sensor_id']]);
        
        if ($stmtCheck->rowCount() == 0) {
            // Cria novo alerta
            $insertAlert = "INSERT INTO alertas (dispositivo_id, sensor_id, mensagem, severidade, status) 
                            VALUES (:dispositivo_id, :sensor_id, :mensagem, :severidade, 'ativo')";
            $stmtInsert = $conn->prepare($insertAlert);
            $stmtInsert->execute([
                ':dispositivo_id' => $sensor['dispositivo_id'],
                ':sensor_id' => $sensor['sensor_id'],
                ':mensagem' => "Plugin falhou: " . $output_text,
                ':severidade' => $severidade
            ]);
            echo "       [ALERTA] Novo alerta gerado.\n";
        } else {
            // Atualiza a mensagem do alerta ativo
            $updateAlert = "UPDATE alertas SET mensagem = :mensagem, severidade = :severidade WHERE sensor_id = :sensor_id AND status != 'resolvido'";
            $stmtUpdate = $conn->prepare($updateAlert);
            $stmtUpdate->execute([
                ':mensagem' => "Plugin falhou: " . $output_text,
                ':severidade' => $severidade,
                ':sensor_id' => $sensor['sensor_id']
            ]);
            echo "       [ALERTA] Alerta atualizado.\n";
        }
    } else {
        // Resolve alertas anteriores se estiver OK
        $resolveAlert = "UPDATE alertas SET status = 'resolvido', resolvido_em = CURRENT_TIMESTAMP 
                         WHERE sensor_id = :sensor_id AND status != 'resolvido'";
        $stmtResolve = $conn->prepare($resolveAlert);
        $stmtResolve->execute([':sensor_id' => $sensor['sensor_id']]);
        if ($stmtResolve->rowCount() > 0) {
            echo "       [ALERTA] Alertas resolvidos para este sensor.\n";
        }
    }
}

echo "[INFO] Verificacao concluida.\n";
