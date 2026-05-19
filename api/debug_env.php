<?php
header('Content-Type: application/json');

echo json_encode([
    'getenv_DB_HOST' => getenv('DB_HOST'),
    'getenv_BASE_PATH' => getenv('BASE_PATH'),
    'env_DB_HOST' => $_ENV['DB_HOST'] ?? 'not_set',
    'server_DB_HOST' => $_SERVER['DB_HOST'] ?? 'not_set',
    'getenv_all' => getenv(),
    'server_all' => array_intersect_key($_SERVER, array_flip(['REQUEST_URI', 'HTTP_HOST', 'DOCUMENT_ROOT', 'DB_HOST', 'BASE_PATH']))
]);
