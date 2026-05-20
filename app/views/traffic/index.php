<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-arrows-left-right me-2 text-primary"></i> Monitor de Conexões Ativas</h1>
            <div class="badge bg-success p-2"><i class="fa-solid fa-circle pulse-green me-1"></i> Real-time Analysis</div>
        </div>
        <p class="text-secondary">Visualização do tráfego entre estações de trabalho e o servidor da empresa.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Resumo de Conexões -->
    <div class="col-12 col-md-4">
        <div class="noc-card">
            <div class="noc-card-body">
                <div class="stat-label">Conexões Atuais</div>
                <div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
                <div class="text-secondary small">Dados enviados pelo agente</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="noc-card">
            <div class="noc-card-body">
                <div class="stat-label">Serviço mais Acessado</div>
                <div class="stat-value" style="font-size: 1.5rem;"><?= $stats['top_service'] ? htmlspecialchars($stats['top_service']) : '—' ?></div>
                <div class="text-secondary small"><?= $stats['top_service'] ? 'Com base nas conexões ativas' : 'Sem conexões registradas' ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="noc-card">
            <div class="noc-card-body">
                <div class="stat-label">Top Consumidor</div>
                <div class="stat-value" style="font-size: 1.5rem;"><?= $stats['top_consumer'] ? htmlspecialchars($stats['top_consumer']) : '—' ?></div>
                <div class="text-primary small"><?= $stats['top_consumer_ip'] ? 'IP: ' . htmlspecialchars($stats['top_consumer_ip']) : 'Aguardando agente' ?></div>
            </div>
        </div>
    </div>

    <!-- Tabela de Conexões em Tempo Real -->
    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-list me-2"></i> Matriz de Comunicação (Origem -> Destino)</span>
                <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary w-25" placeholder="Filtrar por IP ou Hostname...">
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Máquina Origem</th>
                                <th>IP Origem</th>
                                <th class="text-center">Sentido</th>
                                <th>Servidor Destino</th>
                                <th>Serviço / Porta</th>
                                <th>Latência</th>
                                <th>Carga</th>
                            </tr>
                        </thead>
                        <tbody id="traffic-body">
                            <!-- Inserido via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pulse-green {
    animation: pulse-green 1.5s infinite;
}
@keyframes pulse-green {
    0% { opacity: 1; }
    50% { opacity: 0.3; }
    100% { opacity: 1; }
}
.flow-arrow {
    color: var(--noc-primary);
    animation: flow-move 1.5s infinite linear;
}
@keyframes flow-move {
    0% { transform: translateX(-5px); opacity: 0.2; }
    50% { transform: translateX(0); opacity: 1; }
    100% { transform: translateX(5px); opacity: 0.2; }
}
</style>

<?php ob_start(); ?>
<script>
    const trafficData = <?= json_encode($conexoes) ?>;

    function renderTraffic() {
        const body = document.getElementById('traffic-body');
        body.innerHTML = '';
        
        if (!trafficData || trafficData.length === 0) {
            body.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-secondary py-4">
                        <i class="fa-solid fa-circle-info me-2"></i> Nenhuma conexão ativa registrada no banco de dados. Inicie o agente PowerShell para enviar dados de rede reais.
                    </td>
                </tr>
            `;
            return;
        }
        
        trafficData.forEach(item => {
            const load = Number(item.load) || 0;
            const loadClass = load > 80 ? 'bg-danger' : (load > 50 ? 'bg-warning' : 'bg-success');
            const row = `
                <tr>
                    <td><i class="fa-solid fa-desktop me-2 text-secondary"></i>${item.origin ?? item.origem ?? ''}</td>
                    <td><code class="text-info">${item.ip ?? ''}</code></td>
                    <td class="text-center text-primary"><i class="fa-solid fa-angles-right flow-arrow"></i></td>
                    <td><i class="fa-solid fa-server me-2 text-primary"></i>${item.destino ?? ''}</td>
                    <td><span class="badge border border-secondary text-light">${item.service ?? item.servico ?? ''}</span></td>
                    <td>${item.latency ?? ''}</td>
                    <td style="width: 150px;">
                        <div class="progress" style="height: 6px; background-color: #1e2638;">
                            <div class="progress-bar ${loadClass}" role="progressbar" style="width: ${load}%"></div>
                        </div>
                    </td>
                </tr>
            `;
            body.innerHTML += row;
        });
    }

    renderTraffic();

    // Recarregar os dados do banco a cada 30 segundos
    setInterval(() => {
        location.reload();
    }, 30000);
</script>
<?php $extra_js = ob_get_clean(); ?>
