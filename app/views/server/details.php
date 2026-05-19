<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center mb-2">
            <a href="<?= $base_path ?>/servers" class="btn btn-outline-secondary btn-sm me-3"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            <h1 class="mb-0">Detalhes do Servidor: <span class="text-primary"><?= htmlspecialchars($server_name) ?></span></h1>
        </div>
        <p class="text-secondary ps-5">Informações detalhadas de hardware, sistema operacional e histórico de performance.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Informações Gerais -->
    <div class="col-12 col-xl-4">
        <div class="noc-card h-100">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-circle-info me-2 text-info"></i> Especificações Técnicas</span>
            </div>
            <div class="noc-card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <tr><td class="text-secondary">Hostname:</td><td class="text-light fw-bold"><?= $server_name ?></td></tr>
                        <tr><td class="text-secondary">Endereço IP:</td><td class="text-primary">10.0.0.5</td></tr>
                        <tr><td class="text-secondary">S.O:</td><td class="text-light">Windows Server 2022 Datacenter</td></tr>
                        <tr><td class="text-secondary">Processador:</td><td class="text-light">Intel Xeon Gold 6230 (16 vCPUs)</td></tr>
                        <tr><td class="text-secondary">Memória:</td><td class="text-light">32GB RAM DDR4</td></tr>
                        <tr><td class="text-secondary">Uptime:</td><td class="text-success">15 dias, 4 horas, 12 min</td></tr>
                        <tr><td class="text-secondary">Fabricante:</td><td class="text-light">Dell Inc. (PowerEdge R740)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gráfico de Performance (Histórico) -->
    <div class="col-12 col-xl-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-chart-area me-2 text-primary"></i> Histórico de Utilização (24h)</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary active" id="btnCPU">CPU</button>
                    <button class="btn btn-outline-primary" id="btnRAM">RAM</button>
                </div>
            </div>
            <div class="noc-card-body">
                <div id="performanceChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Discos e Partições -->
    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-hard-drive me-2 text-success"></i> Discos e Partições</span>
            </div>
            <div class="noc-card-body">
                <div class="row g-4">
                    <div class="col-12 col-md-4">
                        <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold">Unidade C: (Sistema)</span>
                                <span class="text-success">OK</span>
                            </div>
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: 45%"></div>
                            </div>
                            <div class="small text-secondary">450GB usados de 1TB</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold">Unidade D: (Dados)</span>
                                <span class="text-warning">78%</span>
                            </div>
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: 78%"></div>
                            </div>
                            <div class="small text-secondary">1.5TB usados de 2TB</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold">Unidade E: (Log)</span>
                                <span class="text-success">OK</span>
                            </div>
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-primary" style="width: 12%"></div>
                            </div>
                            <div class="small text-secondary">60GB usados de 500GB</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Monitoria via Agente (Sem SNMP) -->
    <div class="col-12 mt-4">
        <div class="noc-card border-primary border-opacity-25">
            <div class="noc-card-header bg-primary bg-opacity-10">
                <span><i class="fa-solid fa-microchip me-2 text-primary"></i> Saúde dos Componentes (Via InfraVision Agent)</span>
                <span class="badge bg-primary px-2 small">Agente v1.2.0 Ativo</span>
            </div>
            <div class="noc-card-body">
                <div class="row g-4 text-center">
                    <div class="col-6 col-md-2">
                        <div class="stat-label small mb-1">Fan 1 (Front)</div>
                        <div class="h5 mb-0 text-success">4200 RPM</div>
                        <div class="small text-secondary">Status: Estável</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="stat-label small mb-1">Fan 2 (Rear)</div>
                        <div class="h5 mb-0 text-success">4150 RPM</div>
                        <div class="small text-secondary">Status: Estável</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="stat-label small mb-1">Temp. Cores</div>
                        <div class="h5 mb-0 text-warning">48°C</div>
                        <div class="small text-secondary">Média 16 Cores</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="stat-label small mb-1">Fonte (PSU 1)</div>
                        <div class="h5 mb-0 text-success">Operacional</div>
                        <div class="small text-secondary">110W Consumo</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="stat-label small mb-1">Fonte (PSU 2)</div>
                        <div class="h5 mb-0 text-success">Operacional</div>
                        <div class="small text-secondary">105W Consumo</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="stat-label small mb-1">Saúde Memória</div>
                        <div class="h5 mb-0 text-success">0 Erros ECC</div>
                        <div class="small text-secondary">Status: OK</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var options = {
        series: [{
            name: 'Uso de CPU (%)',
            data: [15, 18, 25, 42, 38, 30, 28, 45, 60, 55, 40, 35, 20, 15, 12, 10, 15, 20, 35, 48, 50, 42, 30, 25]
        }],
        chart: {
            type: 'area',
            height: 300,
            foreColor: '#a0aec0',
            toolbar: { show: false }
        },
        colors: ['#3b82f6'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: ['00h', '01h', '02h', '03h', '04h', '05h', '06h', '07h', '08h', '09h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h', '18h', '19h', '20h', '21h', '22h', '23h'],
        },
        grid: {
            borderColor: '#1e2638',
            strokeDashArray: 4,
        }
    };

    var chart = new ApexCharts(document.querySelector("#performanceChart"), options);
    chart.render();

    const dataCPU = [15, 18, 25, 42, 38, 30, 28, 45, 60, 55, 40, 35, 20, 15, 12, 10, 15, 20, 35, 48, 50, 42, 30, 25];
    const dataRAM = [42, 42, 43, 45, 45, 44, 44, 46, 50, 52, 55, 58, 60, 60, 61, 62, 62, 60, 58, 55, 50, 48, 45, 42];

    document.getElementById('btnCPU').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('btnRAM').classList.remove('active');
        chart.updateSeries([{
            name: 'Uso de CPU (%)',
            data: dataCPU
        }]);
    });

    document.getElementById('btnRAM').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('btnCPU').classList.remove('active');
        chart.updateSeries([{
            name: 'Uso de RAM (%)',
            data: dataRAM
        }]);
    });
</script>
