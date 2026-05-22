<?php
// Health check endpoint - responde imediatamente para manter o Render "acordado"
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
echo json_encode([
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'app' => 'InfraVision NOC'
]);
