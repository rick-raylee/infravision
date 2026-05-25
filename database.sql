CREATE DATABASE IF NOT EXISTS infravision CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE infravision;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin', 'operador', 'visitante') DEFAULT 'visitante',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL
);

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, nivel) VALUES 
('Administrador', 'admin@infravision.local', '$2y$10$e.w3j3./J.xW0/kE0sB3/e3y9Zg2hA/vI6z9Xo/5fT4o9Xn7M.uWG', 'admin');

CREATE TABLE IF NOT EXISTS dispositivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    tipo ENUM('servidor_windows', 'servidor_linux', 'switch', 'roteador', 'firewall', 'nobreak', 'sensor_clima', 'storage', 'outro') NOT NULL,
    snmp_community VARCHAR(50) DEFAULT 'public',
    wmi_user VARCHAR(100) NULL,
    wmi_pass VARCHAR(255) NULL,
    ssh_user VARCHAR(100) NULL,
    ssh_pass VARCHAR(255) NULL,
    ssh_port INT DEFAULT 22,
    status ENUM('online', 'alerta', 'critico', 'offline') DEFAULT 'offline',
    ultimo_check TIMESTAMP NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sensores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dispositivo_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('cpu', 'ram', 'disco', 'ping', 'temperatura', 'umidade', 'rede_in', 'rede_out', 'uptime', 'servico', 'plugin') NOT NULL,
    oid VARCHAR(255) NULL COMMENT 'Para SNMP',
    plugin_command VARCHAR(255) NULL COMMENT 'Comando para execução do plugin',
    limite_alerta FLOAT NULL,
    limite_critico FLOAT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS leituras (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    valor FLOAT NOT NULL,
    data_leitura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES sensores(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dispositivo_id INT NOT NULL,
    sensor_id INT NULL,
    mensagem TEXT NOT NULL,
    severidade ENUM('info', 'aviso', 'erro', 'critico') DEFAULT 'info',
    status ENUM('ativo', 'reconhecido', 'resolvido') DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolvido_em TIMESTAMP NULL,
    FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE CASCADE,
    FOREIGN KEY (sensor_id) REFERENCES sensores(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    acao VARCHAR(255) NOT NULL,
    detalhes TEXT NULL,
    ip_origem VARCHAR(45) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS contatos_alerta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    destino VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
