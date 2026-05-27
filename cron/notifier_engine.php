<?php
/**
 * Infravision - Notifier Engine (Nagios-style Notification Dispatcher)
 * Lê alertas recém-gerados e dispara notificações via Telegram.
 */

$base_dir = dirname(__DIR__);
require_once $base_dir . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$telegram_token = getenv('TELEGRAM_BOT_TOKEN');
$telegram_chat_id = getenv('TELEGRAM_CHAT_ID');

$whatsapp_url = getenv('WHATSAPP_URL');
$whatsapp_token = getenv('WHATSAPP_TOKEN');
$whatsapp_number = getenv('WHATSAPP_NUMBER');

function sendTelegramMessage($token, $chat_id, $message) {
    if (empty($token) || empty($chat_id)) return false;
    
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

function sendWhatsAppMessage($url, $token, $number, $message) {
    if (empty($url) || empty($number)) return false;
    
    // Suporte especial para CallMeBot (que é gratuito e não requer instalação local)
    if (strpos($url, 'callmebot.com') !== false) {
        $final_url = $url . "?phone=" . urlencode($number) . "&text=" . urlencode($message) . "&apikey=" . urlencode($token);
        $ch = curl_init($final_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    
    // Formato padrão para APIs REST como Evolution API, Z-API, Mega API
    $data = json_encode([
        'number' => $number,
        'text' => $message,
        'message' => $message // Fallback para outras APIs
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
        'apikey: ' . $token
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

echo "[NOTIFIER] Buscando contatos de alerta no banco...\n";
$stmtContacts = $conn->query("SELECT tipo, destino FROM contatos_alerta WHERE ativo = 1");
$contatosExtras = $stmtContacts->fetchAll(PDO::FETCH_ASSOC);

$whatsappNumbers = [];
if (!empty($whatsapp_number)) {
    $whatsappNumbers[] = $whatsapp_number; // Da variavel de ambiente
}
$telegramChats = [];
if (!empty($telegram_chat_id)) {
    $telegramChats[] = $telegram_chat_id; // Da variavel de ambiente
}

foreach ($contatosExtras as $c) {
    if ($c['tipo'] === 'whatsapp' && !empty($c['destino'])) {
        $whatsappNumbers[] = preg_replace('/[^0-9]/', '', $c['destino']);
    }
    if ($c['tipo'] === 'telegram' && !empty($c['destino'])) {
        $telegramChats[] = trim($c['destino']);
    }
}
$whatsappNumbers = array_unique($whatsappNumbers);
$telegramChats = array_unique($telegramChats);


echo "[NOTIFIER] Buscando alertas PROBLEM nao notificados...\n";

// Buscar alertas ativos não notificados
$queryProblems = "SELECT a.id, a.mensagem, a.severidade, a.criado_em, d.nome as dispositivo_nome, d.ip
                  FROM alertas a
                  LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
                  WHERE a.status = 'ativo' AND a.notificado_em IS NULL";
$stmt = $conn->query($queryProblems);
$problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($problems as $alert) {
    $icon = "🚨";
    if ($alert['severidade'] === 'aviso') $icon = "⚠️";
    if ($alert['severidade'] === 'info') $icon = "ℹ️";

    $msg = "{$icon} *[PROBLEM] InfraVision Alert*\n";
    $msg .= "*Dispositivo:* {$alert['dispositivo_nome']} ({$alert['ip']})\n";
    $msg .= "*Severidade:* " . strtoupper($alert['severidade']) . "\n";
    $msg .= "*Data/Hora:* {$alert['criado_em']}\n";
    $msg .= "*Detalhes:* {$alert['mensagem']}";

    echo "   Enviando PROBLEM para Alerta #{$alert['id']}... \n";
    
    foreach ($telegramChats as $chat_id) {
        sendTelegramMessage($telegram_token, $chat_id, $msg);
    }
    
    if (!empty($whatsapp_url)) {
        foreach ($whatsappNumbers as $num) {
            sendWhatsAppMessage($whatsapp_url, $whatsapp_token, $num, $msg);
        }
    }
    
    $update = $conn->prepare("UPDATE alertas SET notificado_em = NOW() WHERE id = ?");
    $update->execute([$alert['id']]);
    echo "   -> OK\n";
}

echo "[NOTIFIER] Buscando alertas RECOVERY nao notificados...\n";

// Buscar alertas resolvidos não notificados
$queryRecoveries = "SELECT a.id, a.mensagem, a.severidade, a.resolvido_em, d.nome as dispositivo_nome, d.ip
                    FROM alertas a
                    LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
                    WHERE a.status = 'resolvido' AND a.notificado_em IS NOT NULL AND a.resolvido_notificado_em IS NULL";
$stmt = $conn->query($queryRecoveries);
$recoveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($recoveries as $alert) {
    $msg = "✅ *[RECOVERY] InfraVision Alert*\n";
    $msg .= "*Dispositivo:* {$alert['dispositivo_nome']} ({$alert['ip']})\n";
    $msg .= "*Status Anterior:* " . strtoupper($alert['severidade']) . "\n";
    $msg .= "*Recuperado em:* {$alert['resolvido_em']}\n";
    $msg .= "*Detalhes:* O servico/sensor voltou ao normal.\n";
    $msg .= "*Mensagem original:* {$alert['mensagem']}";

    echo "   Enviando RECOVERY para Alerta #{$alert['id']}... \n";
    
    foreach ($telegramChats as $chat_id) {
        sendTelegramMessage($telegram_token, $chat_id, $msg);
    }
    
    if (!empty($whatsapp_url)) {
        foreach ($whatsappNumbers as $num) {
            sendWhatsAppMessage($whatsapp_url, $whatsapp_token, $num, $msg);
        }
    }
    
    $update = $conn->prepare("UPDATE alertas SET resolvido_notificado_em = NOW() WHERE id = ?");
    $update->execute([$alert['id']]);
    echo "   -> OK\n";
}

echo "[NOTIFIER] Rotina de notificacoes finalizada.\n";
