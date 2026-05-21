<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-cubes me-2 text-info"></i> Monitoramento de Virtualização</h1>
        </div>
        <p class="text-secondary">Dispositivos monitorados com métricas de CPU e RAM do agente (sem dados fictícios de hipervisor).</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-microchip me-2"></i> Carga por dispositivo</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Hostname</th>
                                <th>IP</th>
                                <th>CPU</th>
                                <th>RAM</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($hosts)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">
                                        Nenhum host/VM com telemetria. Cadastre servidores e execute o agente.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($hosts as $h): ?>
                                    <?php
                                        $cpu = $h['cpu_atual'] !== null ? round($h['cpu_atual']) : null;
                                        $ram = $h['ram_atual'] !== null ? round($h['ram_atual']) : null;
                                        $cpuClass = $cpu !== null && $cpu >= 90 ? 'text-danger' : ($cpu !== null && $cpu >= 75 ? 'text-warning' : 'text-success');
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($h['hostname']) ?></strong></td>
                                        <td><code class="text-info"><?= htmlspecialchars($h['ip']) ?></code></td>
                                        <td class="<?= $cpuClass ?>"><?= $cpu !== null ? $cpu . '%' : '—' ?></td>
                                        <td><?= $ram !== null ? $ram . '%' : '—' ?></td>
                                        <td><span class="badge bg-<?= $h['status'] === 'online' ? 'success' : ($h['status'] === 'alerta' ? 'warning' : 'secondary') ?>"><?= htmlspecialchars($h['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-chart-pie me-2"></i> Distribuição de CPU</span>
            </div>
            <div class="noc-card-body">
                <?php
                    $chartHosts = array_values(array_filter($hosts ?? [], fn($h) => $h['cpu_atual'] !== null));

                ?>
                <?php if (empty($chartHosts)): ?>
                    <p class="text-secondary mb-0">Sem leituras de CPU para exibir gráfico.</p>
                <?php else: ?>
                    <div id="contentionChart" style="height: 250px;"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($chartHosts)): ?>
<?php ob_start(); ?>
<script>
    var options = {
        series: <?= json_encode(array_map(fn($h) => round((float)$h['cpu_atual']), $chartHosts)) ?>,
        chart: { type: 'pie', height: 250, foreColor: '#a0aec0' },
        labels: <?= json_encode(array_map(fn($h) => $h['hostname'], $chartHosts)) ?>,
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1'],
        stroke: { show: false },
        legend: { position: 'bottom' }
    };
    new ApexCharts(document.querySelector("#contentionChart"), options).render();
</script>
<?php $extra_js = ob_get_clean(); ?>
<?php endif; ?>
