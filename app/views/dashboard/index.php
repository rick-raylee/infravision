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
                        <div class="stat-label">Conexões de Rede</div>
                        <div class="stat-value"><?= (int)($estatisticas['conexoes_ativas'] ?? 0) ?></div>
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
                        <div class="stat-value text-danger" id="val-alertas"><?= (int)($estatisticas['alertas_ativos'] ?? 0) ?></div>
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
                        <div class="stat-value"><span id="val-temp">—</span> / <span id="val-umidade" class="text-info">—</span></div>
                        <div class="text-secondary small"><i class="fa-solid fa-microchip me-1"></i> Sensor ambiental (quando disponível)</div>
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
                        <div class="stat-value"><?= (int)$estatisticas['vms_total'] ?> dispositivos</div>
                        <div class="text-secondary small">Cadastrados no monitoramento</div>
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
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<div class="row g-4 mb-4">
    <!-- Gráficos Principais -->
    <div class="col-12 col-lg-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-chart-area me-2"></i> Tráfego de Rede (<span id="net-interface-name">Core Switch</span>)</span>
                <span class="badge bg-success">Online</span>
            </div>
            <div class="noc-card-body d-flex align-items-center justify-content-center" style="min-height: 300px;">
                <div class="row w-100 g-4 align-items-center">
                    <div class="col-6 text-center border-end border-secondary border-opacity-10">
                        <div id="networkChartIn" style="min-height: 180px;"></div>
                        <div class="mt-1">
                            <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                                <i class="fa-solid fa-download me-1 animate-pulse"></i> Entrada
                            </span>
                            <div class="text-secondary small mt-2" id="net-in-peak" style="font-size: 0.8rem;">Pico: 0.00 Mbps (Escala: 10 Mbps)</div>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div id="networkChartOut" style="min-height: 180px;"></div>
                        <div class="mt-1">
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                <i class="fa-solid fa-upload me-1 animate-pulse"></i> Saída
                            </span>
                            <div class="text-secondary small mt-2" id="net-out-peak" style="font-size: 0.8rem;">Pico: 0.00 Mbps (Escala: 10 Mbps)</div>
                        </div>
                    </div>
                </div>
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
                            <?php if (empty($dispositivos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        <i class="fa-solid fa-circle-info me-2"></i> Nenhum dispositivo cadastrado. Execute o agente coletor para enviar dados reais.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dispositivos as $d): ?>
                                    <tr>
                                        <td>
                                            <span class="status-indicator status-<?= $d['status'] === 'online' ? 'online' : ($d['status'] === 'alerta' ? 'warning' : ($d['status'] === 'critico' ? 'danger' : 'offline')) ?>"></span>
                                            <?= ucfirst($d['status']) ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($d['hostname']) ?></strong></td>
                                        <td><?= htmlspecialchars($d['tipo']) ?></td>
                                        <td><?= htmlspecialchars($d['ip']) ?></td>
                                        <td>
                                            <?php 
                                            if ($d['ultimo_check']) {
                                                echo 'Ativo';
                                            } else {
                                                echo 'Pendente';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?= $d['ultimo_check'] ? date('d/m/Y H:i:s', strtotime($d['ultimo_check'])) : 'Nunca' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
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

    // Valores Globais de Tráfego de Rede
    var currentNetIn = 0;
    var currentNetInMax = 10;
    var currentNetOut = 0;
    var currentNetOutMax = 10;

    function getGaugeMax(val) {
        if (val <= 10) return 10;
        if (val <= 100) return 100;
        if (val <= 1000) return 1000;
        return Math.ceil(val / 1000) * 1000;
    }

    // Gráficos de Rede (Velocímetros)
    var inOptions = {
        series: [0],
        chart: {
            type: 'radialBar',
            height: 200,
            sparkline: { enabled: true }
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                track: {
                    background: '#1e2638',
                    strokeWidth: '97%',
                    margin: 5,
                },
                dataLabels: {
                    name: { show: false },
                    value: {
                        offsetY: -5,
                        fontSize: '20px',
                        fontWeight: '700',
                        color: '#fff',
                        formatter: function(val) {
                            return currentNetIn.toFixed(2) + ' Mbps';
                        }
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: ['#60a5fa'],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100]
            }
        },
        colors: ['#3b82f6'],
        labels: ['Entrada']
    };

    var outOptions = {
        series: [0],
        chart: {
            type: 'radialBar',
            height: 200,
            sparkline: { enabled: true }
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                track: {
                    background: '#1e2638',
                    strokeWidth: '97%',
                    margin: 5,
                },
                dataLabels: {
                    name: { show: false },
                    value: {
                        offsetY: -5,
                        fontSize: '20px',
                        fontWeight: '700',
                        color: '#fff',
                        formatter: function(val) {
                            return currentNetOut.toFixed(2) + ' Mbps';
                        }
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: ['#34d399'],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100]
            }
        },
        colors: ['#10b981'],
        labels: ['Saída']
    };

    var chartIn = new ApexCharts(document.querySelector("#networkChartIn"), inOptions);
    var chartOut = new ApexCharts(document.querySelector("#networkChartOut"), outOptions);
    chartIn.render();
    chartOut.render();

    // Gráfico de CPU
    var cpuData = <?= json_encode(array_map('floatval', array_column($topCpu, 'valor'))) ?>;
    var cpuLabels = <?= json_encode(array_column($topCpu, 'nome')) ?>;
    
    if (cpuData.length === 0) {
        cpuData = [0];
        cpuLabels = ['Sem dados'];
    }

    var cpuOptions = {
        series: [{ data: cpuData }],
        chart: { type: 'bar', height: 300 },
        colors: [function({ value }) {
            if (value >= 90) return '#ef4444';
            if (value >= 75) return '#f59e0b';
            return '#3b82f6';
        }],
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        dataLabels: { enabled: true },
        xaxis: { categories: cpuLabels }
    };
    var cpuChart = new ApexCharts(document.querySelector("#cpuChart"), cpuOptions);
    cpuChart.render();

    function atualizarDashboard() {
        fetch('<?= $base_path ?>/api/dados_dashboard')
            .then(response => response.json())
            .then(data => {
                if (data.erro) return;
                if (data.temperatura != null) {
                    document.getElementById('val-temp').innerText = data.temperatura + '°C';
                }
                if (data.umidade != null) {
                    document.getElementById('val-umidade').innerText = data.umidade + '%';
                }
                if (data.rede && data.rede.in && data.rede.out) {
                    // 1. Obter valores mais recentes
                    currentNetIn = data.rede.in.length > 0 ? data.rede.in[data.rede.in.length - 1] : 0;
                    currentNetOut = data.rede.out.length > 0 ? data.rede.out[data.rede.out.length - 1] : 0;
                    
                    // 2. Determinar limites máximos da escala
                    currentNetInMax = getGaugeMax(currentNetIn);
                    currentNetOutMax = getGaugeMax(currentNetOut);
                    
                    // 3. Converter para porcentagem do velocímetro
                    var pctIn = (currentNetIn / currentNetInMax) * 100;
                    var pctOut = (currentNetOut / currentNetOutMax) * 100;
                    
                    // 4. Atualizar velocímetros
                    chartIn.updateSeries([pctIn]);
                    chartOut.updateSeries([pctOut]);

                    // Definir escala de cores: Verde (<60%), Amarelo (60-85%), Vermelho (>85%)
                    function getGaugeColor(pct) {
                        if (pct < 60) return { color: '#10b981', gradient: '#34d399' }; // Verde
                        if (pct < 85) return { color: '#f59e0b', gradient: '#fbbf24' }; // Amarelo
                        return { color: '#ef4444', gradient: '#f87171' }; // Vermelho
                    }

                    var colorIn = getGaugeColor(pctIn);
                    chartIn.updateOptions({
                        colors: [colorIn.color],
                        fill: { type: 'gradient', gradient: { gradientToColors: [colorIn.gradient] } }
                    });

                    var colorOut = getGaugeColor(pctOut);
                    chartOut.updateOptions({
                        colors: [colorOut.color],
                        fill: { type: 'gradient', gradient: { gradientToColors: [colorOut.gradient] } }
                    });
                    
                    // 5. Exibir pico e escalas nas legendas
                    var peakIn = Math.max(...data.rede.in);
                    var peakOut = Math.max(...data.rede.out);
                    document.getElementById('net-in-peak').innerText = 'Pico: ' + peakIn.toFixed(2) + ' Mbps (Escala: ' + currentNetInMax + ' Mbps)';
                    document.getElementById('net-out-peak').innerText = 'Pico: ' + peakOut.toFixed(2) + ' Mbps (Escala: ' + currentNetOutMax + ' Mbps)';
                    
                    // 6. Atualizar nome da interface
                    if (data.rede.interface) {
                        document.getElementById('net-interface-name').innerText = data.rede.interface;
                    }
                }
            })
            .catch(error => console.error('Erro ao buscar dados:', error));
    }
    atualizarDashboard();
    setInterval(atualizarDashboard, 5000);
</script>
<?php $extra_js = ob_get_clean(); ?>
