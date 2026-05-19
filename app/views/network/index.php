<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-shield-halved me-2 text-primary"></i> Rede & Firewall</h1>
            <button class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Novo Dispositivo</button>
        </div>
        <p class="text-secondary">Monitoramento em tempo real de switches, roteadores e regras de tráfego.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Status das Interfaces -->
    <div class="col-12 col-lg-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-ethernet me-2"></i> Interfaces Core-Switch-01</span>
                <span class="badge bg-success">Operacional</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Porta</th>
                                <th>Status</th>
                                <th>VLAN</th>
                                <th>Tráfego (In)</th>
                                <th>Tráfego (Out)</th>
                                <th>Erros</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>GigabitEthernet1/1</td>
                                <td><span class="status-indicator status-online"></span> Up</td>
                                <td>10 (Data)</td>
                                <td>45.2 Mbps</td>
                                <td>12.8 Mbps</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td>GigabitEthernet1/2</td>
                                <td><span class="status-indicator status-online"></span> Up</td>
                                <td>20 (VoIP)</td>
                                <td>2.1 Mbps</td>
                                <td>1.5 Mbps</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td>GigabitEthernet1/3</td>
                                <td><span class="status-indicator status-offline"></span> Down</td>
                                <td>10 (Data)</td>
                                <td>0 bps</td>
                                <td>0 bps</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td>TenGigabitEthernet1/1</td>
                                <td><span class="status-indicator status-online"></span> Up</td>
                                <td>Trunk</td>
                                <td>850 Mbps</td>
                                <td>420 Mbps</td>
                                <td>0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Firewall Deep Dive -->
    <div class="col-12 col-lg-4">
        <div class="noc-card mb-4">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-lock me-2 text-danger"></i> Firewall State</span>
                <span class="badge bg-danger pulse-danger">Ameaça Detectada</span>
            </div>
            <div class="noc-card-body">
                <div class="row text-center mb-4">
                    <div class="col-6">
                        <div class="text-secondary small">Throughput</div>
                        <div class="h4 mb-0">850 Mbps</div>
                    </div>
                    <div class="col-6 border-start border-secondary">
                        <div class="text-secondary small">Sessões Ativas</div>
                        <div class="h4 mb-0">12.4k</div>
                    </div>
                </div>
                
                <div id="protocolChart" style="height: 200px;"></div>

                <hr class="border-secondary my-4">
                
                <h6 class="mb-3">Ameaças Recentes (IPS)</h6>
                <div class="list-group list-group-flush bg-transparent">
                    <div class="list-group-item bg-transparent border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-bold">SQL Injection Attempt</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">IP: 185.220.101.45 (RU)</div>
                        </div>
                        <span class="badge bg-danger">Bloqueado</span>
                    </div>
                    <div class="list-group-item bg-transparent border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-bold">SSH Brute Force</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">IP: 45.142.120.12 (CN)</div>
                        </div>
                        <span class="badge bg-danger">Bloqueado</span>
                    </div>
                    <div class="list-group-item bg-transparent border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-bold">Port Scan Detectado</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">IP: 192.168.1.50 (Local)</div>
                        </div>
                        <span class="badge bg-warning text-dark">Alerta</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pulse-danger {
    animation: pulse-red 2s infinite;
}
@keyframes pulse-red {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
</style>

<?php ob_start(); ?>
<script>
    // Gráfico de Protocolos do Firewall
    var protocolOptions = {
        series: [44, 35, 13, 8],
        chart: {
            type: 'donut',
            height: 200,
            foreColor: '#a0aec0'
        },
        labels: ['HTTPS', 'HTTP', 'UDP', 'DNS'],
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#6366f1'],
        stroke: { show: false },
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: { show: true },
                        value: { show: true, color: '#fff' },
                        total: { show: true, label: 'Tráfego', color: '#a0aec0' }
                    }
                }
            }
        }
    };
    var protocolChart = new ApexCharts(document.querySelector("#protocolChart"), protocolOptions);
    protocolChart.render();
</script>
<?php $extra_js = ob_get_clean(); ?>
