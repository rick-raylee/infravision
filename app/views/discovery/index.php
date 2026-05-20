<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-radar me-2 text-primary"></i> Descoberta de Rede (Network Discovery)</h1>
            <button class="btn btn-primary" id="startScan"><i class="fa-solid fa-search me-1"></i> Iniciar Varredura</button>
        </div>
        <p class="text-secondary">Identificação automática de dispositivos ativos, pontos cegos e ativos não gerenciados.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Configuração da Varredura -->
    <div class="col-12 col-lg-4">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-sliders me-2"></i> Configurações do Scan</span>
            </div>
            <div class="noc-card-body">
                <div class="mb-3">
                    <label class="form-label text-secondary small">Faixa de IP (CIDR)</label>
                    <input type="text" class="form-control bg-dark border-secondary text-light" id="scanCidr" placeholder="Ex: 192.168.1.0/24" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">Método de Descoberta</label>
                    <select class="form-select bg-dark border-secondary text-light">
                        <option>Ping Sweep (ICMP)</option>
                        <option>Port Scanning (TCP/UDP)</option>
                        <option>ARP Scan (Camada 2)</option>
                        <option>SNMP Discovery</option>
                    </select>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" checked>
                    <label class="form-check-label text-light">Tentar identificar Hostname</label>
                </div>
            </div>
        </div>

        <div class="noc-card mt-4">
            <div class="noc-card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="stat-label">Total Descoberto</span>
                    <span class="h4 mb-0" id="totalFound">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-label text-warning">Pontos Cegos</span>
                    <span class="h4 mb-0 text-warning" id="blindSpots">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados do Scan -->
    <div class="col-12 col-lg-8">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-list me-2"></i> Dispositivos Identificados</span>
                <span id="scanStatus" class="small text-secondary">Aguardando início...</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive" style="max-height: 500px;">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Endereço IP</th>
                                <th>Hostname / MAC</th>
                                <th>Tipo Provável</th>
                                <th>Gestão</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="discoveryResults">
                            <!-- Preenchido via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const scanBtn = document.getElementById('startScan');
    const resultsBody = document.getElementById('discoveryResults');
    const scanStatus = document.getElementById('scanStatus');
    const discoveryDevices = <?= json_encode($dispositivos ?? []) ?>;
    const basePath = <?= json_encode($base_path ?? '') ?>;

    function renderDiscovery(devices) {
        resultsBody.innerHTML = '';
        let blindSpotCount = 0;

        if (!devices.length) {
            resultsBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-secondary py-4">
                        Nenhum dispositivo cadastrado. Execute o agente ou cadastre manualmente em Dispositivos.
                    </td>
                </tr>
            `;
            document.getElementById('totalFound').innerText = '0';
            document.getElementById('blindSpots').innerText = '0';
            return;
        }

        devices.forEach(item => {
            const managed = item.status === 'online' || item.status === 'alerta';
            if (!managed) blindSpotCount++;

            const managedBadge = managed
                ? '<span class="badge bg-success bg-opacity-10 text-success border border-success">Monitorado</span>'
                : '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Sem telemetria</span>';

            const host = item.hostname || item.nome || '—';
            const row = `
                <tr>
                    <td><code class="text-info">${item.ip ?? ''}</code></td>
                    <td class="text-light">${host}</td>
                    <td class="small text-secondary">${item.tipo ?? ''}</td>
                    <td>${managedBadge}</td>
                    <td>
                        ${managed
                            ? '<i class="fa-solid fa-check text-success"></i>'
                            : `<a href="${basePath}/device/create?ip=${encodeURIComponent(item.ip || '')}&nome=${encodeURIComponent(host)}" class="btn btn-sm btn-primary">Gerenciar</a>`}
                    </td>
                </tr>
            `;
            resultsBody.innerHTML += row;
        });

        document.getElementById('totalFound').innerText = devices.length;
        document.getElementById('blindSpots').innerText = blindSpotCount;
    }

    scanBtn.addEventListener('click', () => {
        scanBtn.disabled = true;
        scanBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Carregando...';
        scanStatus.innerText = 'Listando dispositivos cadastrados no banco...';
        renderDiscovery(discoveryDevices);
        scanBtn.disabled = false;
        scanBtn.innerHTML = '<i class="fa-solid fa-search me-1"></i> Atualizar Lista';
        scanStatus.innerText = discoveryDevices.length
            ? 'Lista atualizada com dados reais do banco.'
            : 'Nenhum dispositivo encontrado.';
    });
</script>
