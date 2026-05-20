<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Infraestrutura - InfraVision</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff !important; color: #000 !important; }
            .card { border: 1px solid #ddd !important; break-inside: avoid; }
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .report-header {
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .report-title {
            color: #1e2638;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-box {
            border-left: 4px solid #3b82f6;
            background: #fff;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .table thead {
            background-color: #f1f5f9;
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .watermark {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 0.8rem;
            color: #cbd5e0;
        }
    </style>
</head>
<body class="p-5">

<div class="container bg-white p-5 shadow-sm rounded">
    <!-- Header do Relatório -->
    <div class="report-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="report-title mb-0">Infra<span class="text-primary">Vision</span></h1>
            <p class="text-secondary mb-0">Infrastructure Intelligence Report</p>
        </div>
        <div class="text-end">
            <h5 class="mb-1">Relatório de Inventário & Status</h5>
            <p class="small text-secondary">Gerado em: <?= date('d/m/Y H:i:s') ?><br>ID: #IV-<?= date('YmdHis') ?></p>
        </div>
    </div>

    <!-- Resumo Executivo -->
    <div class="row mb-5 g-3">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="small text-secondary">Ativos Monitorados</div>
                <div class="h4 mb-0">14 Dispositivos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box" style="border-left-color: #10b981;">
                <div class="small text-secondary">Disponibilidade Média</div>
                <div class="h4 mb-0">99.85%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box" style="border-left-color: #ef4444;">
                <div class="small text-secondary">Alertas Críticos</div>
                <div class="h4 mb-0 text-danger">3 Ativos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box" style="border-left-color: #f59e0b;">
                <div class="small text-secondary">Saúde do Ambiente</div>
                <div class="h4 mb-0">Estável (22°C)</div>
            </div>
        </div>
    </div>

    <!-- Lista de Servidores -->
    <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-server"></i> Inventário de Servidores</h5>
    <table class="table table-bordered align-middle mb-5">
        <thead>
            <tr>
                <th>Dispositivo</th>
                <th>Endereço IP</th>
                <th>Sistema Operacional</th>
                <th>Status</th>
                <th>Uso CPU/RAM</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($dispositivos)): ?>
                <tr><td colspan="5" class="text-center text-muted">Nenhum dispositivo cadastrado no banco.</td></tr>
            <?php else: ?>
                <?php foreach ($dispositivos as $d): ?>
                    <?php
                        $badge = match ($d['status'] ?? '') {
                            'online' => 'bg-success',
                            'alerta' => 'bg-warning text-dark',
                            'critico' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                        $cpu = $d['cpu_atual'] !== null ? round($d['cpu_atual']) . '%' : '—';
                        $ram = $d['ram_atual'] !== null ? round($d['ram_atual']) . '%' : '—';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($d['hostname']) ?></strong></td>
                        <td><?= htmlspecialchars($d['ip']) ?></td>
                        <td><?= htmlspecialchars($d['tipo']) ?></td>
                        <td><span class="badge <?= $badge ?>"><?= htmlspecialchars(ucfirst($d['status'] ?? 'offline')) ?></span></td>
                        <td><?= $cpu ?> / <?= $ram ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Serviços Críticos -->
    <h5 class="mb-3 border-bottom pb-2">Serviços & URLs Monitoradas</h5>
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card p-3">
                <div class="fw-bold mb-2">Microsoft Exchange Cluster</div>
                <div class="small d-flex justify-content-between border-bottom py-1">
                    <span>Transport Service</span> <span class="text-success">OK</span>
                </div>
                <div class="small d-flex justify-content-between border-bottom py-1">
                    <span>Database Availability Group</span> <span class="text-success">OK</span>
                </div>
                <div class="small d-flex justify-content-between py-1">
                    <span>Mailbox Database</span> <span class="text-danger">Warning</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <div class="fw-bold mb-2">URLs Externas</div>
                <div class="small d-flex justify-content-between border-bottom py-1">
                    <span>Portal do Cliente</span> <span class="text-success">200 OK (45ms)</span>
                </div>
                <div class="small d-flex justify-content-between border-bottom py-1">
                    <span>API de Pagamentos</span> <span class="text-success">200 OK (12ms)</span>
                </div>
                <div class="small d-flex justify-content-between py-1">
                    <span>Webmail</span> <span class="text-success">200 OK (30ms)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rodapé do Relatório -->
    <div class="mt-5 pt-4 border-top text-center text-secondary small">
        <p>Este documento é confidencial e destinado exclusivamente para uso administrativo do NOC InfraVision.<br>
        InfraVision Monitoring System &copy; <?= date('Y') ?></p>
    </div>
</div>

<div class="watermark no-print">InfraVision NOC System - Relatório Oficial</div>

<!-- Botões Flutuantes (Apenas Tela) -->
<div class="no-print position-fixed bottom-0 start-50 translate-middle-x mb-4 bg-dark p-3 rounded-pill shadow-lg">
    <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 me-2">
        <i class="bi bi-printer"></i> Imprimir / Salvar PDF
    </button>
    <a href="<?= $base_path ?>/dashboard" class="btn btn-outline-light rounded-pill px-4">Voltar ao Painel</a>
</div>

</body>
</html>
