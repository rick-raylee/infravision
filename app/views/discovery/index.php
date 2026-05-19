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
                    <input type="text" class="form-control bg-dark border-secondary text-light" value="192.168.1.0/24">
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
    
    const mockDiscovery = [
        { ip: '192.168.1.1', host: 'GATEWAY-FIREWALL', type: 'Network Device', managed: true },
        { ip: '192.168.1.10', host: 'SRV-EXCHANGE-01', type: 'Server', managed: true },
        { ip: '192.168.1.15', host: 'PC-FINANCEIRO-02', type: 'Workstation', managed: false },
        { ip: '192.168.1.50', host: 'DESCONHECIDO (MAC: 00:0C:29...)', type: 'IoT / Camera', managed: false },
        { ip: '192.168.1.105', host: 'MACBOOK-CEO', type: 'Mobile/Laptop', managed: false },
        { ip: '192.168.1.200', host: 'SRV-ARQUIVOS', type: 'Server', managed: true },
    ];

    scanBtn.addEventListener('click', () => {
        scanBtn.disabled = true;
        scanBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Varrendo Rede...';
        scanStatus.innerText = 'Varrendo 192.168.1.0/24...';
        resultsBody.innerHTML = '';
        
        let foundCount = 0;
        let blindSpotCount = 0;

        mockDiscovery.forEach((item, index) => {
            setTimeout(() => {
                foundCount++;
                if (!item.managed) blindSpotCount++;
                
                const managedBadge = item.managed 
                    ? '<span class="badge bg-success bg-opacity-10 text-success border border-success">Gerenciado</span>'
                    : '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning">PONTO CEGO</span>';
                
                const row = `
                    <tr class="animate__animated animate__fadeIn">
                        <td><code class="text-info">${item.ip}</code></td>
                        <td class="text-light">${item.host}</td>
                        <td class="small text-secondary">${item.type}</td>
                        <td>${managedBadge}</td>
                        <td>
                            ${!item.managed ? `<a href="<?= $base_path ?>/device/create?ip=${item.ip}&nome=${item.host}" class="btn btn-sm btn-primary">Gerenciar</a>` : '<i class="fa-solid fa-check text-success"></i>'}
                        </td>
                    </tr>
                `;
                resultsBody.innerHTML += row;
                
                document.getElementById('totalFound').innerText = foundCount;
                document.getElementById('blindSpots').innerText = blindSpotCount;

                if (index === mockDiscovery.length - 1) {
                    scanBtn.disabled = false;
                    scanBtn.innerHTML = '<i class="fa-solid fa-search me-1"></i> Iniciar Varredura';
                    scanStatus.innerText = 'Varredura concluída.';
                }
            }, index * 800);
        });
    });
</script>
