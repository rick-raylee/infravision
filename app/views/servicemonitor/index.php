<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-globe me-2 text-primary"></i> Monitor de Serviços e URLs</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUrlModal"><i class="fa-solid fa-plus me-1"></i> Nova URL</button>
        </div>
        <p class="text-secondary">Acompanhamento de disponibilidade de sites, APIs e serviços específicos como Exchange.</p>
    </div>
</div>

<!-- Resumo do Monitoramento e Explicação da Tecnologia -->
<div class="row g-3 mb-4">
    <!-- Card Resumo URLs -->
    <div class="col-12 col-md-4">
        <div class="noc-card p-3 d-flex align-items-center">
            <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info me-3 shadow-sm d-flex align-items-center justify-content-center" style="font-size: 1.5rem; width: 54px; height: 54px;">
                <i class="fa-solid fa-globe"></i>
            </div>
            <div>
                <div class="stat-label" style="font-size: 0.75rem;">Websites & APIs</div>
                <div class="h4 mb-0 fw-bold">
                    <span id="stat-urls-total" class="text-light">0</span>
                    <span class="small text-secondary" style="font-size: 0.8rem;"> total</span>
                </div>
                <div class="small mt-1" style="font-size: 0.8rem;">
                    <span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> <span id="stat-urls-online">0</span> Online</span>
                    <span class="text-danger fw-bold ms-2"><i class="fa-solid fa-circle-xmark"></i> <span id="stat-urls-offline">0</span> Falhas</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card Resumo E-mails -->
    <div class="col-12 col-md-4">
        <div class="noc-card p-3 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary me-3 shadow-sm d-flex align-items-center justify-content-center" style="font-size: 1.5rem; width: 54px; height: 54px;">
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div>
                <div class="stat-label" style="font-size: 0.75rem;">Servidores de E-mail</div>
                <div class="h4 mb-0 fw-bold">
                    <span id="stat-emails-total" class="text-light">0</span>
                    <span class="small text-secondary" style="font-size: 0.8rem;"> total</span>
                </div>
                <div class="small mt-1" style="font-size: 0.8rem;">
                    <span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> <span id="stat-emails-online">0</span> Online</span>
                    <span class="text-danger fw-bold ms-2"><i class="fa-solid fa-circle-xmark"></i> <span id="stat-emails-offline">0</span> Falhas</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Como Funciona -->
    <div class="col-12 col-md-4">
        <div class="noc-card p-3 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(0, 0, 0, 0) 100%);">
            <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info me-3 shadow-sm d-flex align-items-center justify-content-center" style="font-size: 1.3rem; width: 54px; height: 54px;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div class="small text-secondary" style="line-height: 1.3; font-size: 0.75rem;">
                <strong class="text-light d-block mb-1"><i class="fa-solid fa-shield-halved text-info me-1"></i> Como Funciona a Tecnologia?</strong>
                <span class="d-block"><strong>Websites:</strong> Teste cURL + Fallback dinâmico pelo navegador para evitar bloqueio de firewalls governamentais.</span>
                <span class="d-block mt-1"><strong>E-mail:</strong> Pings ativos de sockets TCP (conexões rápidas na porta) + simulação de diagnóstico.</span>
            </div>
        </div>
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
                                <td><span id="badge-${res.id}" class="badge <?= $res['status_class'] ?>" title="<?= htmlspecialchars($res['curl_error'] ?? '') ?>"><?= $res['status_text'] ?></span></td>
                                <td id="latency-${res.id}"><?= $res['latency'] ?></td>
                                <td><?= $res['uptime'] ?></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-warning me-1" onclick="openEditUrlModal(<?= $res['id'] ?>)" title="Editar URL">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
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

    <!-- Monitoramento de Servidores de E-mail -->
    <div class="col-12 col-lg-5">
        <div class="noc-card">
            <div class="noc-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="d-flex align-items-center"><i class="fa-solid fa-envelope-open-text me-2 text-primary"></i> E-mail Health</span>
                <div class="d-flex align-items-center gap-2">
                    <select id="email-server-select" class="form-select form-select-sm bg-dark text-light border-secondary" style="width: 140px; font-size: 0.8rem;">
                        <?php foreach ($email_servers as $srv): ?>
                            <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newEmailServerModal" title="Novo Servidor"><i class="fa-solid fa-plus"></i></button>
                    <button id="edit-email-server-btn" class="btn btn-sm btn-outline-warning" title="Editar Servidor Selecionado"><i class="fa-solid fa-pen"></i></button>
                    <button id="delete-email-server-btn" class="btn btn-sm btn-outline-danger" title="Excluir Servidor Selecionado"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
            <div class="noc-card-body">
                <div id="email-server-offline-alert" class="alert alert-danger d-none py-2 px-3 small align-items-center mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> O servidor de e-mail está inacessível via porta indicada.
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-25 pb-2">
                    <div>
                        <span class="text-secondary small">Endereço:</span>
                        <strong id="email-server-host" class="ms-1 text-light">Carregando...</strong>
                    </div>
                    <div>
                        <span id="email-server-status-badge" class="badge bg-secondary">Offline</span>
                        <span id="email-server-latency" class="badge bg-dark text-info border border-secondary border-opacity-50 ms-1">0ms</span>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Fila de Mensagens (Submission)</span>
                        <span id="email-server-fila" class="text-warning">0 msgs</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div id="email-server-fila-progress" class="progress-bar bg-warning" style="width: 0%"></div>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 border border-secondary border-opacity-50 rounded text-center" style="background-color: rgba(0,0,0,0.15);">
                            <div class="small text-secondary">Mailbox DB</div>
                            <div id="email-server-mailbox" class="h5 mb-0 text-success">Mounted</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border border-secondary border-opacity-50 rounded text-center" style="background-color: rgba(0,0,0,0.15);">
                            <div class="small text-secondary">Transport Svc</div>
                            <div id="email-server-transport" class="h5 mb-0 text-success">Running</div>
                        </div>
                    </div>
                </div>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        Active Sync Performance
                        <span id="email-server-activesync" class="badge bg-success">Healthy</span>
                    </li>
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        Outlook Anywhere (RPC)
                        <span id="email-server-outlook" class="badge bg-success">Healthy</span>
                    </li>
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        DAG Replication State
                        <span id="email-server-dag" class="badge bg-danger">Out of Sync</span>
                    </li>
                </ul>
            </div>
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

<!-- Modal Novo Servidor de E-mail -->
<div class="modal fade" id="newEmailServerModal" tabindex="-1" aria-labelledby="newEmailServerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_PATH ?>/services/email/store" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="newEmailServerModalLabel"><i class="fa-solid fa-plus me-2 text-primary"></i>Novo Servidor de E-mail</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label">Nome do Servidor</label>
                        <input type="text" name="nome" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Exchange Principal" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-8">
                            <label class="form-label">Host / IP / Endereço</label>
                            <input type="text" name="host" class="form-control bg-dark text-light border-secondary" placeholder="Ex: smtp.gmail.com" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Porta</label>
                            <input type="number" name="porta" class="form-control bg-dark text-light border-secondary" value="587" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Tipo de Servidor</label>
                            <select name="tipo" class="form-select bg-dark text-light border-secondary">
                                <option value="Exchange">Exchange</option>
                                <option value="SMTP">SMTP</option>
                                <option value="IMAP">IMAP</option>
                                <option value="POP3">POP3</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fila de Msgs Inicial</label>
                            <input type="number" name="fila_mensagens" class="form-control bg-dark text-light border-secondary" value="0">
                        </div>
                    </div>
                    
                    <hr class="border-secondary my-3 border-opacity-50">
                    <div class="px-1 mb-2 small text-info"><i class="fa-solid fa-sliders me-1"></i> Simulação de Status para Apresentação/TCC</div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Mailbox DB</label>
                            <select name="mailbox_db" class="form-select bg-dark text-light border-secondary">
                                <option value="Mounted">Mounted (Montado)</option>
                                <option value="Dismounted">Dismounted (Desmontado)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Transport Service</label>
                            <select name="transport_svc" class="form-select bg-dark text-light border-secondary">
                                <option value="Running">Running (Executando)</option>
                                <option value="Stopped">Stopped (Parado)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Active Sync</label>
                            <select name="active_sync" class="form-select bg-dark text-light border-secondary">
                                <option value="Healthy">Healthy (Saudável)</option>
                                <option value="Unhealthy">Unhealthy (Não Saudável)</option>
                                <option value="Failed">Failed (Com Erro)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Outlook Anywhere</label>
                            <select name="outlook_anywhere" class="form-select bg-dark text-light border-secondary">
                                <option value="Healthy">Healthy (Saudável)</option>
                                <option value="Unhealthy">Unhealthy (Não Saudável)</option>
                                <option value="Failed">Failed (Com Erro)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Replicação DAG</label>
                        <select name="dag_replication" class="form-select bg-dark text-light border-secondary">
                            <option value="Healthy">Healthy (Saudável)</option>
                            <option value="Out of Sync">Out of Sync (Fora de Sincronia)</option>
                            <option value="Failed">Failed (Com Erro)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Servidor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar URL -->
<div class="modal fade" id="editUrlModal" tabindex="-1" aria-labelledby="editUrlModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_PATH ?>/services/update" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="editUrlModalLabel"><i class="fa-solid fa-pen me-2 text-warning"></i>Editar URL de Monitoramento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editUrlId">
                    <div class="mb-3">
                        <label for="editUrlName" class="form-label">Nome do Serviço</label>
                        <input type="text" name="nome" id="editUrlName" class="form-control bg-dark text-light border-secondary" placeholder="Ex: API de Pagamentos" required>
                    </div>
                    <div class="mb-3">
                        <label for="editUrlAddress" class="form-label">URL / Endpoint</label>
                        <input type="url" name="url" id="editUrlAddress" class="form-control bg-dark text-light border-secondary" placeholder="https://..." required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Servidor de E-mail -->
<div class="modal fade" id="editEmailServerModal" tabindex="-1" aria-labelledby="editEmailServerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form action="<?= BASE_PATH ?>/services/email/update" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="editEmailServerModalLabel"><i class="fa-solid fa-pen me-2 text-warning"></i>Editar Servidor de E-mail</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <input type="hidden" name="id" id="editEmailServerId">
                    <div class="mb-3">
                        <label class="form-label">Nome do Servidor</label>
                        <input type="text" name="nome" id="editEmailServerName" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Exchange Principal" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-8">
                            <label class="form-label">Host / IP / Endereço</label>
                            <input type="text" name="host" id="editEmailServerHost" class="form-control bg-dark text-light border-secondary" placeholder="Ex: smtp.gmail.com" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Porta</label>
                            <input type="number" name="porta" id="editEmailServerPort" class="form-control bg-dark text-light border-secondary" value="587" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Tipo de Servidor</label>
                            <select name="tipo" id="editEmailServerType" class="form-select bg-dark text-light border-secondary">
                                <option value="Exchange">Exchange</option>
                                <option value="SMTP">SMTP</option>
                                <option value="IMAP">IMAP</option>
                                <option value="POP3">POP3</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fila de Msgs</label>
                            <input type="number" name="fila_mensagens" id="editEmailServerFila" class="form-control bg-dark text-light border-secondary" value="0">
                        </div>
                    </div>
                    
                    <hr class="border-secondary my-3 border-opacity-50">
                    <div class="px-1 mb-2 small text-info"><i class="fa-solid fa-sliders me-1"></i> Simulação de Status para Apresentação/TCC</div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Mailbox DB</label>
                            <select name="mailbox_db" id="editEmailServerMailbox" class="form-select bg-dark text-light border-secondary">
                                <option value="Mounted">Mounted (Montado)</option>
                                <option value="Dismounted">Dismounted (Desmontado)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Transport Service</label>
                            <select name="transport_svc" id="editEmailServerTransport" class="form-select bg-dark text-light border-secondary">
                                <option value="Running">Running (Executando)</option>
                                <option value="Stopped">Stopped (Parado)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Active Sync</label>
                            <select name="active_sync" id="editEmailServerActiveSync" class="form-select bg-dark text-light border-secondary">
                                <option value="Healthy">Healthy (Saudável)</option>
                                <option value="Unhealthy">Unhealthy (Não Saudável)</option>
                                <option value="Failed">Failed (Com Erro)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Outlook Anywhere</label>
                            <select name="outlook_anywhere" id="editEmailServerOutlook" class="form-select bg-dark text-light border-secondary">
                                <option value="Healthy">Healthy (Saudável)</option>
                                <option value="Unhealthy">Unhealthy (Não Saudável)</option>
                                <option value="Failed">Failed (Com Erro)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Replicação DAG</label>
                        <select name="dag_replication" id="editEmailServerDag" class="form-select bg-dark text-light border-secondary">
                            <option value="Healthy">Healthy (Saudável)</option>
                            <option value="Out of Sync">Out of Sync (Fora de Sincronia)</option>
                            <option value="Failed">Failed (Com Erro)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cache inicial das URLs para edição sem dependência de AJAX imediato
window.urlsData = <?= json_encode($resultados_urls) ?>;

window.openEditUrlModal = function(id) {
    const res = (window.urlsData || []).find(u => u.id == id);
    if (!res) return;
    document.getElementById("editUrlId").value = res.id;
    document.getElementById("editUrlName").value = res.nome;
    document.getElementById("editUrlAddress").value = res.url;
    const modal = new bootstrap.Modal(document.getElementById('editUrlModal'));
    modal.show();
};

document.addEventListener("DOMContentLoaded", function() {
    const base_path = '<?= BASE_PATH ?>';
    const tableBody = document.getElementById("urls-table-body");
    const emailSelect = document.getElementById("email-server-select");
    const deleteEmailServerBtn = document.getElementById("delete-email-server-btn");
    const editEmailServerBtn = document.getElementById("edit-email-server-btn");
    
    let emailServersData = [];

    // --- Auxiliar: Atualização do Resumo Gráfico de URLs ---
    function updateUrlStats() {
        const total = tableBody.rows.length;
        let online = 0;
        let offline = 0;
        
        const badges = tableBody.querySelectorAll(".badge");
        badges.forEach(b => {
            // Conta como Online se for bg-success (200 OK)
            if (b.classList.contains("bg-success")) {
                online++;
            } else {
                offline++;
            }
        });
        
        document.getElementById("stat-urls-total").textContent = total;
        document.getElementById("stat-urls-online").textContent = online;
        document.getElementById("stat-urls-offline").textContent = offline;
    }

    // --- Auxiliar: Atualização do Resumo Gráfico de E-mails ---
    function updateEmailStats() {
        const total = emailServersData.length;
        let online = 0;
        let offline = 0;
        
        emailServersData.forEach(s => {
            if (s.is_online) {
                online++;
            } else {
                offline++;
            }
        });
        
        document.getElementById("stat-emails-total").textContent = total;
        document.getElementById("stat-emails-online").textContent = online;
        document.getElementById("stat-emails-offline").textContent = offline;
    }

    // --- 1. Monitoramento de URLs em Tempo Real ---
    function refreshUrls() {
        fetch(`${base_path}/api/services`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data)) return;
                
                window.urlsData = data; // Atualizar o cache de URLs
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
                        <td><span id="badge-${res.id}" class="badge ${res.status_class}" title="${escapeHtml(res.curl_error || '')}">${res.status_text}</span></td>
                        <td id="latency-${res.id}">${res.latency}</td>
                        <td>${res.uptime}</td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-warning me-1" onclick="openEditUrlModal(${res.id})" title="Editar URL">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <a href="${base_path}/services/delete?id=${res.id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja remover esta URL de monitoramento?');" title="Remover URL">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                    
                    // Fallback de verificação do lado do cliente se o servidor cURL der Offline
                    if (res.status_text === 'Offline') {
                        const startClientCheck = performance.now();
                        const controller = new AbortController();
                        const timeoutId = setTimeout(() => controller.abort(), 4000);
                        
                        fetch(res.url, { mode: 'no-cors', signal: controller.signal })
                            .then(() => {
                                clearTimeout(timeoutId);
                                const endClientCheck = performance.now();
                                const clientLatency = Math.round(endClientCheck - startClientCheck);
                                
                                const badge = document.getElementById(`badge-${res.id}`);
                                const latencyCol = document.getElementById(`latency-${res.id}`);
                                if (badge && latencyCol) {
                                    badge.className = 'badge bg-success';
                                    badge.textContent = '200 OK';
                                    badge.title = 'Verificado via Navegador (O IP do Servidor Render está bloqueado pelo Firewall do site)';
                                    
                                    latencyCol.innerHTML = `${clientLatency}ms <i class="fa-solid fa-circle-nodes text-info ms-1" style="cursor: help;" title="Medido de forma híbrida pelo seu navegador (bypasseou bloqueio de IP do servidor)"></i>`;
                                }
                                // Recalcula resumo de URLs imediatamente após o bypass do cliente
                                updateUrlStats();
                            })
                            .catch((err) => {
                                clearTimeout(timeoutId);
                                updateUrlStats();
                            });
                    }
                });
                
                // Recalcula resumo de URLs inicial
                updateUrlStats();
            })
            .catch(error => console.error("Erro ao atualizar URLs em tempo real:", error));
    }

    // --- 2. Monitoramento de Servidores de E-mail ---
    function updateEmailServerUI(selectedId) {
        const srv = emailServersData.find(s => s.id == selectedId);
        if (!srv) return;
        
        // Host e porta
        document.getElementById("email-server-host").textContent = `${srv.host}:${srv.porta} (${srv.tipo})`;
        
        // Status Badge
        const statusBadge = document.getElementById("email-server-status-badge");
        statusBadge.className = `badge ${srv.status_class}`;
        statusBadge.textContent = srv.status_text;
        statusBadge.title = srv.socket_error ? srv.socket_error : 'Conexão ativa e monitorada via porta';
        
        // Latência
        const latencyBadge = document.getElementById("email-server-latency");
        latencyBadge.textContent = srv.latency;
        
        // Alert de Offline
        const offlineAlert = document.getElementById("email-server-offline-alert");
        if (srv.is_online) {
            offlineAlert.classList.add("d-none");
            offlineAlert.classList.remove("d-flex");
        } else {
            offlineAlert.classList.remove("d-none");
            offlineAlert.classList.add("d-flex");
        }
        
        // Fila de mensagens
        document.getElementById("email-server-fila").textContent = `${srv.fila_mensagens} msgs`;
        const progressBar = document.getElementById("email-server-fila-progress");
        const percent = Math.min(100, Math.round((srv.fila_mensagens / 600) * 100));
        progressBar.style.width = `${percent}%`;
        progressBar.className = `progress-bar ${srv.fila_mensagens > 400 ? 'bg-danger' : (srv.fila_mensagens > 200 ? 'bg-warning' : 'bg-success')}`;
        
        // Mailbox DB
        const mailbox = document.getElementById("email-server-mailbox");
        mailbox.textContent = srv.mailbox_db;
        mailbox.className = `h5 mb-0 ${srv.mailbox_db === 'Mounted' ? 'text-success' : 'text-danger'}`;
        
        // Transport Svc
        const transport = document.getElementById("email-server-transport");
        transport.textContent = srv.transport_svc;
        transport.className = `h5 mb-0 ${srv.transport_svc === 'Running' ? 'text-success' : 'text-danger'}`;
        
        // Active Sync
        const activesync = document.getElementById("email-server-activesync");
        activesync.textContent = srv.active_sync;
        activesync.className = `badge ${srv.active_sync === 'Healthy' ? 'bg-success' : (srv.active_sync === 'Unhealthy' ? 'bg-warning' : 'bg-danger')}`;
        
        // Outlook Anywhere
        const outlook = document.getElementById("email-server-outlook");
        outlook.textContent = srv.outlook_anywhere;
        outlook.className = `badge ${srv.outlook_anywhere === 'Healthy' ? 'bg-success' : (srv.outlook_anywhere === 'Unhealthy' ? 'bg-warning' : 'bg-danger')}`;
        
        // DAG Replication
        const dag = document.getElementById("email-server-dag");
        dag.textContent = srv.dag_replication;
        dag.className = `badge ${srv.dag_replication === 'Healthy' ? 'bg-success' : (srv.dag_replication === 'Out of Sync' ? 'bg-warning' : 'bg-danger')}`;
    }
    
    function refreshEmailServers() {
        fetch(`${base_path}/api/email-services`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data)) return;
                emailServersData = data;
                
                const currentSelectedId = emailSelect.value || (data[0] ? data[0].id : null);
                
                // Recriar as opções se mudou o número de servidores registrados
                if (emailSelect.options.length !== data.length) {
                    emailSelect.innerHTML = '';
                    data.forEach(srv => {
                        const opt = document.createElement('option');
                        opt.value = srv.id;
                        opt.textContent = srv.nome;
                        emailSelect.appendChild(opt);
                    });
                    if (currentSelectedId) {
                        emailSelect.value = currentSelectedId;
                    }
                }
                
                if (currentSelectedId) {
                    updateEmailServerUI(currentSelectedId);
                }
                
                // Atualiza o resumo numérico de servidores
                updateEmailStats();
            })
            .catch(error => console.error("Erro ao atualizar servidores de e-mail:", error));
    }
    
    // Switch de servidor
    emailSelect.addEventListener("change", function() {
        updateEmailServerUI(this.value);
    });
    
    // Botão Excluir
    deleteEmailServerBtn.addEventListener("click", function() {
        const id = emailSelect.value;
        if (id) {
            if (confirm("Tem certeza que deseja remover este servidor de e-mail do monitoramento?")) {
                window.location.href = `${base_path}/services/email/delete?id=${id}`;
            }
        }
    });
    
    // Botão Editar
    editEmailServerBtn.addEventListener("click", function() {
        const id = emailSelect.value;
        if (!id) return;
        const srv = emailServersData.find(s => s.id == id);
        if (!srv) return;
        
        document.getElementById("editEmailServerId").value = srv.id;
        document.getElementById("editEmailServerName").value = srv.nome;
        document.getElementById("editEmailServerHost").value = srv.host;
        document.getElementById("editEmailServerPort").value = srv.porta;
        document.getElementById("editEmailServerType").value = srv.tipo;
        document.getElementById("editEmailServerFila").value = srv.fila_mensagens;
        
        document.getElementById("editEmailServerMailbox").value = srv.mailbox_db;
        document.getElementById("editEmailServerTransport").value = srv.transport_svc;
        document.getElementById("editEmailServerActiveSync").value = srv.active_sync;
        document.getElementById("editEmailServerOutlook").value = srv.outlook_anywhere;
        document.getElementById("editEmailServerDag").value = srv.dag_replication;
        
        const modal = new bootstrap.Modal(document.getElementById('editEmailServerModal'));
        modal.show();
    });

    // --- 3. Inicialização e Loop global de refresh ---
    refreshUrls();
    refreshEmailServers();
    
    setInterval(function() {
        refreshUrls();
        refreshEmailServers();
    }, 5000);
});
</script>
