<div class="row g-4 mb-4">
    <!-- Cards de Resumo -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= $base_path ?>/servers" class="text-decoration-none">
            <div class="noc-card dashboard-stat-card">
                <div class="noc-card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-server fa-2x text-primary"></i>
                    </div>
                    <div>
                        <div class="stat-label">Servidores Ativos</div>
                        <div class="stat-value" id="val-servidores"><?= $estatisticas['servidores_online'] ?>/<?= $estatisticas['servidores_total'] ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= $base_path ?>/network" class="text-decoration-none">
            <div class="noc-card dashboard-stat-card">
                <div class="noc-card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-network-wired fa-2x text-success"></i>
                    </div>
                    <div>
                        <div class="stat-label">Rede Core</div>
                        <div class="stat-value">Normal</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= $base_path ?>/alerts" class="text-decoration-none">
            <div class="noc-card dashboard-stat-card">
                <div class="noc-card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-triangle-exclamation fa-2x text-danger"></i>
                    </div>
                    <div>
                        <div class="stat-label">Alertas Críticos</div>
                        <div class="stat-value text-danger" id="val-alertas"><?= $estatisticas['alertas_ativos'] ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= $base_path ?>/server/details?nome=SENSOR-AMBIENTAL" class="text-decoration-none">
            <div class="noc-card dashboard-stat-card">
                <div class="noc-card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-thermometer-half fa-2x text-warning"></i>
                    </div>
                    <div>
                        <div class="stat-label">Ambiente Datacenter</div>
                        <div class="stat-value"><span id="val-temp">22°C</span> / <span id="val-umidade" class="text-info">45%</span></div>
                        <div class="text-secondary small"><i class="fa-solid fa-microchip me-1"></i> SNMP Sensor Active</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= $base_path ?>/virtualization" class="text-decoration-none">
            <div class="noc-card dashboard-stat-card">
                <div class="noc-card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-cubes fa-2x text-info"></i>
                    </div>
                    <div>
                        <div class="stat-label">Virtualização</div>
                        <div class="stat-value"><?= $estatisticas['vms_total'] ?> VMs</div>
                        <div class="text-success small">Noisy Neighbor: Low</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
.dashboard-stat-card {
    transition: all 0.3s ease;
    cursor: pointer;
}
.dashboard-stat-card:hover {
    transform: translateY(-5px);
    background-color: rgba(255, 255, 255, 0.05);
    border-color: var(--noc-primary);
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}
</style>

<div class="row g-4 mb-4">
    <!-- Gráficos Principais -->
    <div class="col-12 col-lg-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-chart-area me-2"></i> Tráfego de Rede (Core Switch)</span>
                <span class="badge bg-success">Online</span>
            </div>
            <div class="noc-card-body">
                <div id="networkChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-server me-2"></i> Top Consumo CPU</span>
            </div>
            <div class="noc-card-body">
                <div id="cpuChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Tabela de Status -->
    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-list-check me-2"></i> Status dos Serviços Críticos</span>
                <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-rotate-right"></i> Atualizar</button>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Dispositivo</th>
                                <th>Tipo</th>
                                <th>IP</th>
                                <th>Uptime</th>
                                <th>Última Checagem</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-dispositivos">
                            <tr>
                                <td><span class="status-indicator status-online"></span> Online</td>
                                <td>SRV-DC-01</td>
                                <td>Windows Server</td>
                                <td>10.0.0.5</td>
                                <td>45 dias</td>
                                <td>Agora mesmo</td>
                            </tr>
                            <tr>
                                <td><span class="status-indicator status-online"></span> Online</td>
                                <td>SRV-DB-01</td>
                                <td>Linux / MySQL</td>
                                <td>10.0.0.10</td>
                                <td>120 dias</td>
                                <td>Agora mesmo</td>
                            </tr>
                            <tr>
                                <td><span class="status-indicator status-danger"></span> Crítico</td>
                                <td>SW-CORE-01</td>
                                <td>Switch L3</td>
                                <td>10.0.0.1</td>
                                <td>2 dias</td>
                                <td class="text-danger">Alta latência (150ms)</td>
                            </tr>
                            <tr>
                                <td><span class="status-indicator status-warning"></span> Alerta</td>
                                <td>STORAGE-SAN</td>
                                <td>Storage</td>
                                <td>10.0.0.50</td>
                                <td>90 dias</td>
                                <td class="text-warning">Disco 3 - Warning SMART</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    // Configurações Globais ApexCharts para Tema Dark
    window.Apex = {
        chart: {
            foreColor: '#a0aec0',
            toolbar: { show: false },
            animations: { enabled: true, dynamicAnimation: { speed: 1000 } }
        },
        grid: { borderColor: '#1e2638' },
        tooltip: { theme: 'dark' }
    };

    // Gráfico de Rede
    var netOptions = {
        series: [{ name: 'Inbound (Mbps)', data: [30, 40, 35, 50, 49, 60, 70, 91, 125, 100, 110] },
                 { name: 'Outbound (Mbps)', data: [20, 30, 25, 40, 39, 50, 60, 81, 95, 80, 90] }],
        chart: { type: 'area', height: 300, stacked: false },
        colors: ['#3b82f6', '#10b981'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
        xaxis: { categories: ['10:00', '10:05', '10:10', '10:15', '10:20', '10:25', '10:30', '10:35', '10:40', '10:45', '10:50'] }
    };
    var netChart = new ApexCharts(document.querySelector("#networkChart"), netOptions);
    netChart.render();

    // Gráfico de CPU
    var cpuOptions = {
        series: [{ data: [95, 80, 75, 60, 45] }],
        chart: { type: 'bar', height: 300 },
        colors: [function({ value }) {
            if (value >= 90) return '#ef4444';
            if (value >= 75) return '#f59e0b';
            return '#3b82f6';
        }],
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        dataLabels: { enabled: true },
        xaxis: { categories: ['SRV-DB-01', 'SRV-APP-02', 'SRV-DC-01', 'SRV-FILE-01', 'SRV-WEB-01'] }
    };
    var cpuChart = new ApexCharts(document.querySelector("#cpuChart"), cpuOptions);
    cpuChart.render();

    // Simular Atualização em Tempo Real via AJAX
    setInterval(() => {
        fetch('/infravision/api/dados_dashboard')
            .then(response => response.json())
            .then(data => {
                // Atualizar Cards
                document.getElementById('val-temp').innerText = data.temperatura + '°C';
                if(data.umidade) {
                    document.getElementById('val-umidade').innerText = data.umidade + '%';
                }
                
                // Atualizar Gráfico de Rede
                netChart.updateSeries([
                    { name: 'Inbound (Mbps)', data: data.rede.in },
                    { name: 'Outbound (Mbps)', data: data.rede.out }
                ]);
            })
            .catch(error => console.error('Erro ao buscar dados:', error));
    }, 5000);
</script>
<?php $extra_js = ob_get_clean(); ?>
