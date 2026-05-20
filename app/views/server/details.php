<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center mb-2">
            <a href="<?= $base_path ?>/servers" class="btn btn-outline-secondary btn-sm me-3"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            <h1 class="mb-0">Detalhes do Servidor: <span class="text-primary"><?= htmlspecialchars($server_name ?: '—') ?></span></h1>
        </div>
        <p class="text-secondary ps-5">Dados coletados pelo agente InfraVision.</p>
    </div>
</div>

<?php if (!$servidor): ?>
    <div class="alert alert-secondary">
        Servidor não encontrado no banco. Cadastre o dispositivo ou aguarde o agente enviar telemetria.
    </div>
<?php else: ?>
<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="noc-card h-100">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-circle-info me-2 text-info"></i> Especificações</span>
            </div>
            <div class="noc-card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <tr><td class="text-secondary">Hostname:</td><td class="text-light fw-bold"><?= htmlspecialchars($servidor['nome']) ?></td></tr>
                        <tr><td class="text-secondary">IP:</td><td class="text-primary"><?= htmlspecialchars($servidor['ip']) ?></td></tr>
                        <tr><td class="text-secondary">Tipo:</td><td class="text-light"><?= htmlspecialchars($servidor['tipo']) ?></td></tr>
                        <tr><td class="text-secondary">Status:</td><td class="text-light"><?= htmlspecialchars($servidor['status']) ?></td></tr>
                        <tr><td class="text-secondary">S.O.:</td><td class="text-light"><?= htmlspecialchars($servidor['sistema_operacional'] ?? '—') ?></td></tr>
                        <tr><td class="text-secondary">Processador:</td><td class="text-light"><?= htmlspecialchars($servidor['processador'] ?? '—') ?></td></tr>
                        <tr><td class="text-secondary">Última coleta:</td><td class="text-light"><?= $servidor['ultimo_check'] ? date('d/m/Y H:i:s', strtotime($servidor['ultimo_check'])) : 'Nunca' ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-chart-area me-2 text-primary"></i> Histórico de utilização</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary active" id="btnCPU">CPU</button>
                    <button class="btn btn-outline-primary" id="btnRAM">RAM</button>
                </div>
            </div>
            <div class="noc-card-body">
                <?php if (empty($historico_cpu) && empty($historico_ram)): ?>
                    <p class="text-secondary mb-0">Sem leituras de CPU/RAM. Execute o agente neste servidor.</p>
                <?php else: ?>
                    <div id="performanceChart" style="min-height: 300px;"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-hard-drive me-2 text-success"></i> Discos</span>
            </div>
            <div class="noc-card-body">
                <?php if (empty($discos)): ?>
                    <p class="text-secondary mb-0">Sem dados de disco para este servidor.</p>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($discos as $disco): ?>
                            <?php $uso = min(100, max(0, (float)$disco['valor'])); ?>
                            <div class="col-12 col-md-4">
                                <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><?= htmlspecialchars($disco['nome']) ?></span>
                                        <span class="<?= $uso >= 90 ? 'text-danger' : ($uso >= 75 ? 'text-warning' : 'text-success') ?>"><?= round($uso) ?>%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 10px;">
                                        <div class="progress-bar bg-primary" style="width: <?= $uso ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($historico_cpu) || !empty($historico_ram)): ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const dataCPU = <?= json_encode($historico_cpu ?: [0]) ?>;
    const dataRAM = <?= json_encode($historico_ram ?: [0]) ?>;
    const labels = dataCPU.map((_, i) => '#' + (i + 1));

    var chart = new ApexCharts(document.querySelector("#performanceChart"), {
        series: [{ name: 'CPU (%)', data: dataCPU }],
        chart: { type: 'area', height: 300, foreColor: '#a0aec0', toolbar: { show: false } },
        colors: ['#3b82f6'],
        stroke: { curve: 'smooth', width: 2 },
        xaxis: { categories: labels },
        grid: { borderColor: '#1e2638' }
    });
    chart.render();

    document.getElementById('btnCPU').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('btnRAM').classList.remove('active');
        chart.updateSeries([{ name: 'CPU (%)', data: dataCPU }]);
    });
    document.getElementById('btnRAM').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('btnCPU').classList.remove('active');
        chart.updateSeries([{ name: 'RAM (%)', data: dataRAM.length ? dataRAM : [0] }]);
    });
</script>
<?php endif; ?>
<?php endif; ?>
