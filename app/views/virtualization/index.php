<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-cubes me-2 text-info"></i> Monitoramento de Virtualização</h1>
            <span class="badge bg-primary px-3 py-2">Hipervisor: VMware ESXi 7.0</span>
        </div>
        <p class="text-secondary">Análise de performance de máquinas virtuais e detecção de Noisy Neighbor Effect.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Noisy Neighbor Alert -->
    <div class="col-12">
        <div class="alert alert-warning border-warning bg-warning bg-opacity-10 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-triangle-exclamation fa-2x me-3"></i>
            <div>
                <strong>Atenção: Noisy Neighbor detectado!</strong><br>
                A VM <code>SRV-SQL-PROD</code> está causando alta contenção de CPU Ready (7.5%) afetando a performance das outras VMs no mesmo host.
            </div>
        </div>
    </div>

    <!-- VM Performance List -->
    <div class="col-12 col-lg-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-microchip me-2"></i> Performance das VMs (Top Consumption)</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>VM Name</th>
                                <th>CPU Ready</th>
                                <th>RAM Active</th>
                                <th>Disk Latency</th>
                                <th>Net Throughput</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>SRV-SQL-PROD</strong></td>
                                <td class="text-danger">7.5% (High)</td>
                                <td>28 GB</td>
                                <td>1.2 ms</td>
                                <td>45 Mbps</td>
                                <td><span class="badge bg-success">Running</span></td>
                            </tr>
                            <tr>
                                <td><strong>SRV-APP-01</strong></td>
                                <td class="text-success">0.8%</td>
                                <td>4 GB</td>
                                <td>0.5 ms</td>
                                <td>12 Mbps</td>
                                <td><span class="badge bg-success">Running</span></td>
                            </tr>
                            <tr>
                                <td><strong>SRV-WEB-DEV</strong></td>
                                <td class="text-success">1.2%</td>
                                <td>2 GB</td>
                                <td>2.1 ms</td>
                                <td>2 Mbps</td>
                                <td><span class="badge bg-warning text-dark">Warning</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Resource Contention Chart -->
    <div class="col-12 col-lg-4">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-chart-pie me-2"></i> Contenção de Recursos</span>
            </div>
            <div class="noc-card-body">
                <div id="contentionChart" style="height: 250px;"></div>
                <div class="mt-3 small text-secondary">
                    <p><i class="fa-solid fa-info-circle me-1 text-info"></i> CPU Ready acima de 5% indica que as VMs estão esperando tempo de processamento do hipervisor.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    var options = {
        series: [45, 25, 15, 15],
        chart: { type: 'pie', height: 250, foreColor: '#a0aec0' },
        labels: ['SQL-PROD', 'APP-01', 'WEB-DEV', 'Outros'],
        colors: ['#ef4444', '#3b82f6', '#f59e0b', '#6b7280'],
        stroke: { show: false },
        legend: { position: 'bottom' }
    };
    var chart = new ApexCharts(document.querySelector("#contentionChart"), options);
    chart.render();
</script>
<?php $extra_js = ob_get_clean(); ?>
