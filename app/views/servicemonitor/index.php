<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-globe me-2 text-primary"></i> Monitor de Serviços e URLs</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUrlModal"><i class="fa-solid fa-plus me-1"></i> Nova URL</button>
        </div>
        <p class="text-secondary">Acompanhamento de disponibilidade de sites, APIs e serviços específicos como Exchange.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Monitoramento de URLs -->
    <div class="col-12 col-lg-7">
        <div class="noc-card">
            <div class="noc-card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-link me-2 text-info"></i> URLs e Endpoints Externos</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.75rem; font-weight: 500; background-color: rgba(25, 135, 84, 0.1);">
                    <span class="spinner-grow spinner-grow-sm text-success me-2" role="status" style="width: 8px; height: 8px;"></span>
                    Tempo Real Ativo
                </span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th>URL / Endpoint</th>
                                <th>Status</th>
                                <th>Tempo Resp.</th>
                                <th>Uptime (7d)</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="urls-table-body">
                            <?php foreach ($resultados_urls as $res): ?>
                            <tr>
                                <td><?= htmlspecialchars($res['nome']) ?></td>
                                <td><a href="<?= htmlspecialchars($res['url']) ?>" target="_blank" class="text-primary"><?= htmlspecialchars($res['url']) ?></a></td>
                                <td><span class="badge <?= $res['status_class'] ?>"><?= $res['status_text'] ?></span></td>
                                <td><?= $res['latency'] ?></td>
                                <td><?= $res['uptime'] ?></td>
                                <td class="text-end pe-4">
                                    <a href="<?= BASE_PATH ?>/services/delete?id=<?= $res['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja remover esta URL de monitoramento?');" title="Remover URL">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitoramento Microsoft Exchange -->
    <div class="col-12 col-lg-5">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-envelope-open-text me-2 text-primary"></i> Microsoft Exchange Health</span>
            </div>
            <div class="noc-card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Fila de Mensagens (Submission)</span>
                        <span class="text-warning">452 msgs</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: 75%"></div>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 border border-secondary rounded text-center">
                            <div class="small text-secondary">Mailbox DB</div>
                            <div class="h5 mb-0 text-success">Mounted</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border border-secondary rounded text-center">
                            <div class="small text-secondary">Transport Svc</div>
                            <div class="h5 mb-0 text-success">Running</div>
                        </div>
                    </div>
                </div>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        Active Sync Performance
                        <span class="badge bg-success">Healthy</span>
                    </li>
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        Outlook Anywhere (RPC)
                        <span class="badge bg-success">Healthy</span>
                    </li>
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        DAG Replication State
                        <span class="badge bg-danger">Out of Sync</span>
                    </li>
                </ul>
            </div>
        </div>
</div>

<!-- Modal Nova URL -->
<div class="modal fade" id="newUrlModal" tabindex="-1" aria-labelledby="newUrlModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_PATH ?>/services/store" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="newUrlModalLabel"><i class="fa-solid fa-plus me-2 text-primary"></i>Nova URL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="urlName" class="form-label">Nome do Serviço</label>
                        <input type="text" name="nome" class="form-control bg-dark text-light border-secondary" id="urlName" placeholder="Ex: API de Pagamentos" required>
                    </div>
                    <div class="mb-3">
                        <label for="urlAddress" class="form-label">URL / Endpoint</label>
                        <input type="url" name="url" class="form-control bg-dark text-light border-secondary" id="urlAddress" placeholder="https://..." required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const base_path = '<?= BASE_PATH ?>';
    const tableBody = document.getElementById("urls-table-body");
    
    function refreshUrls() {
        fetch(`${base_path}/api/services`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data)) return;
                
                tableBody.innerHTML = '';
                
                data.forEach(res => {
                    const tr = document.createElement('tr');
                    
                    const escapeHtml = (str) => {
                        return str.replace(/&/g, "&amp;")
                                  .replace(/</g, "&lt;")
                                  .replace(/>/g, "&gt;")
                                  .replace(/"/g, "&quot;")
                                  .replace(/'/g, "&#039;");
                    };
                    
                    tr.innerHTML = `
                        <td>${escapeHtml(res.nome)}</td>
                        <td><a href="${escapeHtml(res.url)}" target="_blank" class="text-primary">${escapeHtml(res.url)}</a></td>
                        <td><span class="badge ${res.status_class}">${res.status_text}</span></td>
                        <td>${res.latency}</td>
                        <td>${res.uptime}</td>
                        <td class="text-end pe-4">
                            <a href="${base_path}/services/delete?id=${res.id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja remover esta URL de monitoramento?');" title="Remover URL">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });
            })
            .catch(error => console.error("Erro ao atualizar URLs em tempo real:", error));
    }
    
    // Atualizar a cada 5 segundos para que seja realmente em tempo real dinâmico
    setInterval(refreshUrls, 5000);
});
</script>
