<?php
$current_request = $_SERVER['REQUEST_URI'];
$base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
$current_path = str_replace($base_path, '', $current_request);
$current_path = explode('?', $current_path)[0];

// Buscar quantidade de alertas ativos
$alert_count = 0;
try {
    require_once __DIR__ . '/../../../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        $stmtAlertsCount = $db->query("SELECT COUNT(*) FROM alertas WHERE status = 'ativo'");
        $alert_count = (int)$stmtAlertsCount->fetchColumn();
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfraVision - NOC Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --noc-bg: #0b0f19;
            --noc-card: #151a27;
            --noc-primary: #3b82f6;
            --noc-secondary: #a0aec0;
            --sidebar-width: 260px;
        }
        body {
            background-color: var(--noc-bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
            margin: 0;
        }
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--noc-card);
            border-right: 1px solid #1e2638;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #1e2638;
            background-color: rgba(0,0,0,0.2);
        }
        .sidebar-menu {
            padding: 1rem 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: var(--noc-secondary);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        .sidebar-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.05);
        }
        .sidebar-link.active {
            color: #fff !important;
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0) 100%) !important;
            border-left: 4px solid var(--noc-primary) !important;
            font-weight: 700 !important;
            box-shadow: inset 6px 0 12px -4px rgba(59, 130, 246, 0.6) !important;
        }
        .sidebar-link.active i {
            color: var(--noc-primary) !important;
            filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.8)) !important;
        }
        .main-content {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem 3rem;
            width: calc(100% - var(--sidebar-width));
        }
        .noc-card {
            background-color: var(--noc-card);
            border: 1px solid #1e2638;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
        }
        .stat-label {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--noc-secondary);
        }
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--noc-bg);
        }
        ::-webkit-scrollbar-thumb {
            background: #1e2638;
            border-radius: 3px;
        }
        .profile-section {
            padding: 1.5rem;
            border-bottom: 1px solid #1e2638;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <!-- 1. Logo e Nome -->
    <div class="sidebar-header">
        <a class="d-flex align-items-center text-decoration-none" href="<?= $base_path ?>/dashboard">
            <img src="<?= $base_path ?>/assets/img/logo.png" alt="InfraVision" height="30" class="me-2 rounded-circle">
            <span class="fw-bold fs-5 text-light">Infra<span class="text-primary">Vision</span></span>
        </a>
    </div>

    <!-- 2. Perfil e Logout (Agora no Topo) -->
    <div class="profile-section">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-primary rounded-circle p-2 me-3 shadow-sm">
                <i class="fa-solid fa-user text-white"></i>
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-light text-truncate" style="font-size: 0.9rem;"><?= $_SESSION['usuario_nome'] ?? 'Administrador' ?></div>
                <div class="text-secondary small">Online</div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base_path ?>/logout" class="btn btn-outline-danger btn-sm w-100 py-1">
                <i class="fa-solid fa-sign-out-alt me-1"></i> Sair
            </a>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <!-- 3. Alertas (Agora em primeiro no menu) -->
        <a href="<?= $base_path ?>/alerts" class="sidebar-link <?= $current_path === '/alerts' ? 'active' : '' ?> mb-2">
            <i class="fa-solid fa-bell text-danger"></i> <strong>Alertas Críticos</strong> 
            <?php if ($alert_count > 0): ?>
                <span class="badge bg-danger rounded-pill ms-auto"><?= $alert_count ?></span>
            <?php endif; ?>
        </a>

        <hr class="border-secondary border-opacity-10 mx-3 mb-3">

        <!-- Administração -->
        <?php if (($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?>
        <div class="px-3 mb-2 small text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">Configurações</div>
        <a href="<?= $base_path ?>/users" class="sidebar-link <?= $current_path === '/users' ? 'active' : '' ?>">
            <i class="fa-solid fa-users-gear"></i> Usuários
        </a>
        <a href="<?= $base_path ?>/settings" class="sidebar-link <?= $current_path === '/settings' ? 'active' : '' ?>">
            <i class="fa-solid fa-plug"></i> Integrações
        </a>
        <a href="<?= $base_path ?>/rules" class="sidebar-link <?= $current_path === '/rules' ? 'active' : '' ?>">
            <i class="fa-solid fa-bell-concierge"></i> Regras de Alerta
        </a>
        <hr class="border-secondary border-opacity-10 mx-3 my-3">
        <?php endif; ?>

        <!-- Monitoramento -->
        <div class="px-3 mb-2 small text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">Operacional</div>
        <a href="<?= $base_path ?>/dashboard" class="sidebar-link <?= ($current_path === '/dashboard' || $current_path === '/' || $current_path === '') ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-line"></i> Dashboard
        </a>
        <a href="<?= $base_path ?>/report/inventory" class="sidebar-link <?= $current_path === '/report/inventory' ? 'active' : '' ?>">
            <i class="fa-solid fa-file-pdf"></i> Relatórios
        </a>
        <a href="<?= $base_path ?>/servers" class="sidebar-link <?= $current_path === '/servers' ? 'active' : '' ?>">
            <i class="fa-solid fa-server"></i> Servidores
        </a>
        <a href="<?= $base_path ?>/ups" class="sidebar-link <?= $current_path === '/ups' ? 'active' : '' ?>">
            <i class="fa-solid fa-battery-three-quarters"></i> Nobreaks & UPS
        </a>
        <a href="<?= $base_path ?>/virtualization" class="sidebar-link <?= $current_path === '/virtualization' ? 'active' : '' ?>">
            <i class="fa-solid fa-cubes"></i> Virtualização
        </a>
        <a href="<?= $base_path ?>/services" class="sidebar-link <?= $current_path === '/services' ? 'active' : '' ?>">
            <i class="fa-solid fa-globe"></i> Serviços & URLs
        </a>
        <a href="<?= $base_path ?>/logcenter" class="sidebar-link <?= $current_path === '/logcenter' ? 'active' : '' ?>">
            <i class="fa-solid fa-terminal"></i> Central de Logs
        </a>
        <a href="<?= $base_path ?>/network" class="sidebar-link <?= $current_path === '/network' ? 'active' : '' ?>">
            <i class="fa-solid fa-shield-halved"></i> Rede & Firewall
        </a>
        <a href="<?= $base_path ?>/traffic" class="sidebar-link <?= $current_path === '/traffic' ? 'active' : '' ?>">
            <i class="fa-solid fa-arrows-left-right"></i> Monitor de Tráfego
        </a>
        <a href="<?= $base_path ?>/discovery" class="sidebar-link <?= $current_path === '/discovery' ? 'active' : '' ?>">
            <i class="fa-solid fa-radar"></i> Descoberta
        </a>
    </div>
</div>

<div class="main-content">
