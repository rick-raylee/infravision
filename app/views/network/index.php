<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-shield-halved me-2 text-primary"></i> Rede & Firewall</h1>
            <a href="<?= $base_path ?>/device/create" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Novo Dispositivo</a>
        </div>
        <p class="text-secondary">Visão resumida por estação (uma linha por máquina). O detalhe de cada conexão está em <a href="<?= $base_path ?>/traffic" class="text-primary">Monitor de Conexões</a>.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-network-wired me-2"></i> Estações com tráfego ativo</span>
                <span class="badge bg-<?= !empty($hosts_rede) ? 'success' : 'secondary' ?>"><?= count($hosts_rede) ?> máquina(s)</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Máquina</th>
                                <th>IP</th>
                                <th class="text-center">Conexões</th>
                                <th>Destinos (amostra)</th>
                                <th>Serviços</th>
                                <th>Lat. máx.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($hosts_rede)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        Nenhuma conexão registrada. O agente envia conexões em cada coleta.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($hosts_rede as $h): ?>
                                    <?php
                                        $destinos = array_slice($h['destinos'], 0, 3);
                                        $destinoTxt = implode(', ', $destinos);
                                        if (count($h['destinos']) > 3) {
                                            $destinoTxt .= ' +' . (count($h['destinos']) - 3);
                                        }
                                        $servicos = array_slice($h['servicos'], 0, 2);
                                        $servicoTxt = implode(', ', $servicos);
                                        if (count($h['servicos']) > 2) {
                                            $servicoTxt .= ' +' . (count($h['servicos']) - 2);
                                        }
                                    ?>
                                    <tr>
                                        <td><i class="fa-solid fa-desktop me-2 text-secondary"></i><?= htmlspecialchars($h['origem']) ?></td>
                                        <td><code class="text-info"><?= htmlspecialchars($h['ip_origem']) ?></code></td>
                                        <td class="text-center"><span class="badge bg-primary"><?= (int)$h['total'] ?></span></td>
                                        <td class="small text-secondary"><?= htmlspecialchars($destinoTxt ?: '—') ?></td>
                                        <td class="small"><?= htmlspecialchars($servicoTxt ?: '—') ?></td>
                                        <td><?= (int)$h['latencia_max'] ?> ms</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($dispositivos_rede)): ?>
        <div class="noc-card mt-4">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-ethernet me-2"></i> Equipamentos de rede cadastrados</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>IP</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Última checagem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dispositivos_rede as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['nome']) ?></td>
                                    <td><?= htmlspecialchars($d['ip']) ?></td>
                                    <td><?= htmlspecialchars($d['tipo']) ?></td>
                                    <td><span class="status-indicator status-<?= $d['status'] === 'online' ? 'online' : 'offline' ?>"></span> <?= htmlspecialchars($d['status']) ?></td>
                                    <td><?= $d['ultimo_check'] ? date('d/m/Y H:i', strtotime($d['ultimo_check'])) : 'Nunca' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-lg-4">
        <div class="noc-card mb-4">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-chart-pie me-2"></i> Serviços nas conexões</span>
            </div>
            <div class="noc-card-body">
                <?php if (empty($servico_stats)): ?>
                    <p class="text-secondary mb-0">Sem dados para gráfico. Aguardando agente.</p>
                <?php else: ?>
                    <div id="protocolChart" style="height: 200px;"></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i> Alertas ativos</span>
            </div>
            <div class="noc-card-body">
                <?php if (empty($alertas_ips)): ?>
                    <p class="text-secondary mb-0">Nenhum alerta ativo no momento.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush bg-transparent">
                        <?php foreach ($alertas_ips as $a): ?>
                            <div class="list-group-item bg-transparent border-0 px-0 py-2">
                                <div class="small fw-bold"><?= htmlspecialchars($a['mensagem']) ?></div>
                                <div class="text-secondary" style="font-size: 0.75rem;">
                                    <?= htmlspecialchars($a['nome']) ?> — IP: <?= htmlspecialchars($a['ip']) ?>
                                </div>
                                <span class="badge bg-<?= $a['severidade'] === 'critico' ? 'danger' : 'warning' ?> mt-1"><?= htmlspecialchars($a['severidade']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($servico_stats)): ?>
<?php ob_start(); ?>
<script>
    const protocolLabels = <?= json_encode(array_column($servico_stats, 'servico')) ?>;
    const protocolSeries = <?= json_encode(array_map('intval', array_column($servico_stats, 'total'))) ?>;
    var protocolOptions = {
        series: protocolSeries,
        chart: { type: 'donut', height: 200, foreColor: '#a0aec0' },
        labels: protocolLabels,
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#6366f1', '#ec4899', '#14b8a6'],
        stroke: { show: false },
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: { show: true, total: { show: true, label: 'Conexões', color: '#a0aec0' } }
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#protocolChart"), protocolOptions).render();
</script>
<?php $extra_js = ob_get_clean(); ?>
<?php endif; ?>
