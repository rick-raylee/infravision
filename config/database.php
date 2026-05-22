<?php

// Fuso horário do Brasil (UTC-3)
date_default_timezone_set('America/Sao_Paulo');

class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    // Remove surrounding quotes
                    if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                        $value = $matches[1];
                    }
                    if (!isset($_SERVER[$name]) && !isset($_ENV[$name])) {
                        putenv("{$name}={$value}");
                        $_ENV[$name] = $value;
                        $_SERVER[$name] = $value;
                    }
                }
            }
        }
    }

    private function getEnvVar($key, $default) {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        return $default;
    }

    public function __construct() {
        $this->loadEnv();
        $this->host = $this->getEnvVar('DB_HOST', 'mysql-3c70fa95-infravision.a.aivencloud.com');
        $this->port = $this->getEnvVar('DB_PORT', '26976');
        $this->db_name = $this->getEnvVar('DB_NAME', 'defaultdb');
        $this->username = $this->getEnvVar('DB_USER', 'avnadmin');
        $this->password = $this->getEnvVar('DB_PASS', '');
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET NAMES utf8mb4");
            // Forçar fuso horário da sessão MySQL para horário de Brasília (UTC-3)
            $this->conn->exec("SET time_zone = '-03:00'");
        } catch(PDOException $exception) {
            $this->handleConnectionError($exception);
        }

        return $this->conn;
    }

    private function handleConnectionError(PDOException $exception) {
        // Detectar se é uma requisição de API
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_api = (strpos($request_uri, '/api/') !== false) || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($is_api) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
                http_response_code(500);
            }
            echo json_encode([
                "status" => "erro",
                "erro" => "Erro de conexão com o banco de dados",
                "detalhes" => $exception->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Requisição Web, renderizar página de erro NOC elegante
        $host = htmlspecialchars($this->host);
        $port = htmlspecialchars($this->port);
        $db_name = htmlspecialchars($this->db_name);
        $user = htmlspecialchars($this->username);
        $error_msg = htmlspecialchars($exception->getMessage());

        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR" data-bs-theme="dark">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>InfraVision - Erro de Conexão</title>
            <link rel="icon" href="<?= $base_path ?>/assets/img/logo.png" type="image/png">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --noc-bg: #0b0f19;
                    --noc-card: #151a27;
                    --noc-primary: #3b82f6;
                    --noc-danger: #ef4444;
                    --noc-secondary: #a0aec0;
                    --noc-border: #1e2638;
                }
                body {
                    background-color: var(--noc-bg);
                    color: #fff;
                    font-family: 'Inter', sans-serif;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                    padding: 1.5rem;
                }
                .error-card {
                    background-color: var(--noc-card);
                    border: 1px solid var(--noc-border);
                    border-radius: 1rem;
                    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
                    width: 100%;
                    max-width: 650px;
                    padding: 3rem;
                    position: relative;
                    overflow: hidden;
                    backdrop-filter: blur(10px);
                }
                .error-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(90deg, var(--noc-danger) 0%, #f97316 100%);
                }
                .icon-container {
                    width: 80px;
                    height: 80px;
                    background-color: rgba(239, 68, 68, 0.1);
                    border: 1px solid rgba(239, 68, 68, 0.25);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1.5rem auto;
                    color: var(--noc-danger);
                    font-size: 2.5rem;
                    position: relative;
                    box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
                }
                .icon-container .warning-badge {
                    position: absolute;
                    bottom: 0px;
                    right: 0px;
                    background-color: var(--noc-danger);
                    color: #fff;
                    font-size: 0.8rem;
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 2px solid var(--noc-card);
                }
                .tech-details {
                    text-align: left;
                    background-color: rgba(0, 0, 0, 0.3);
                    border: 1px solid var(--noc-border);
                    border-radius: 0.5rem;
                    padding: 1.2rem;
                    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
                    font-size: 0.85rem;
                    color: #f87171;
                    max-height: 180px;
                    overflow-y: auto;
                    word-break: break-all;
                }
                .btn-retry {
                    background-color: var(--noc-primary);
                    border: none;
                    font-weight: 600;
                    padding: 0.75rem 2rem;
                    transition: all 0.2s;
                    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
                    color: #fff;
                    text-decoration: none;
                    display: inline-block;
                }
                .btn-retry:hover {
                    background-color: #2563eb;
                    transform: translateY(-1px);
                    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
                    color: #fff;
                }
                .btn-details {
                    background: none;
                    border: none;
                    color: var(--noc-secondary);
                    font-size: 0.875rem;
                    text-decoration: underline;
                    padding: 0;
                    transition: color 0.2s;
                }
                .btn-details:hover {
                    color: #fff;
                }
                .info-badge {
                    background-color: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 0.375rem;
                    padding: 0.4rem 0.75rem;
                    font-size: 0.85rem;
                    color: var(--noc-secondary);
                }
                .info-badge strong {
                    color: #fff;
                }
            </style>
        </head>
        <body>

        <div class="error-card text-center">
            <div class="icon-container">
                <i class="fa-solid fa-database"></i>
                <div class="warning-badge">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>

            <h3 class="fw-bold mb-2">Falha na Conexão com o Banco de Dados</h3>
            <p class="text-secondary mb-4">O InfraVision NOC não conseguiu se conectar ao servidor de banco de dados. Isso pode ocorrer por configurações incorretas, o serviço estar offline ou problemas de DNS/rede.</p>

            <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
                <div class="info-badge">Host: <strong><?= $host ?></strong></div>
                <div class="info-badge">Porta: <strong><?= $port ?></strong></div>
                <div class="info-badge">Banco: <strong><?= $db_name ?></strong></div>
                <div class="info-badge">Usuário: <strong><?= $user ?></strong></div>
            </div>

            <div class="mb-4">
                <button class="btn btn-details mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseError" aria-expanded="false" aria-controls="collapseError">
                    Visualizar detalhes técnicos do erro
                </button>
                <div class="collapse" id="collapseError">
                    <div class="tech-details text-start">
                        <strong>SQLSTATE / Mensagem:</strong><br>
                        <?= $error_msg ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <a href="" class="btn btn-primary btn-retry rounded-pill">
                    <i class="fa-solid fa-arrows-rotate me-2"></i> Tentar Novamente
                </a>
            </div>

            <div class="text-secondary small mt-4">
                Dica: Verifique as variáveis de ambiente <code>DB_HOST</code>, <code>DB_PORT</code>, <code>DB_NAME</code>, <code>DB_USER</code> e <code>DB_PASS</code> em seu contêiner ou ambiente.
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit;
    }
}
