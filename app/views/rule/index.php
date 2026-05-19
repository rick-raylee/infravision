<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-gear me-2 text-primary"></i> Configuração de Regras de Alerta</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editRuleModal" onclick="fillModal('Nova Regra', '', '')">
                <i class="fa-solid fa-plus me-1"></i> Nova Regra
            </button>
        </div>
        <p class="text-secondary">Defina os limites e condições que disparam alertas no sistema.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-microchip me-2 text-warning"></i> Regras de Performance</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Recurso</th>
                                <th>Condição</th>
                                <th>Limite (Aviso)</th>
                                <th>Limite (Crítico)</th>
                                <th>Notificação</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CPU Load</td>
                                <td>Maior que (>)</td>
                                <td>75%</td>
                                <td>90%</td>
                                <td><span class="badge bg-dark">Painel + Email</span></td>
                                <td><span class="badge bg-success">Ativa</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRuleModal" onclick="fillModal('CPU Load', '75%', '90%')">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Memória RAM</td>
                                <td>Maior que (>)</td>
                                <td>80%</td>
                                <td>95%</td>
                                <td><span class="badge bg-dark">Painel + Telegram</span></td>
                                <td><span class="badge bg-success">Ativa</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRuleModal" onclick="fillModal('Memória RAM', '80%', '95%')">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Latência de Disco</td>
                                <td>Maior que (>)</td>
                                <td>10ms</td>
                                <td>25ms</td>
                                <td><span class="badge bg-dark">Painel</span></td>
                                <td><span class="badge bg-success">Ativa</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRuleModal" onclick="fillModal('Latência de Disco', '10ms', '25ms')">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Regra -->
    <div class="modal fade" id="editRuleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="modalTitle">Editar Regra: <span></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRuleForm">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Limite de Aviso (Warning)</label>
                            <input type="text" id="inputWarning" class="form-control bg-dark border-secondary text-light">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Limite Crítico (Critical)</label>
                            <input type="text" id="inputCritical" class="form-control bg-dark border-secondary text-light">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Ações de Notificação</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked>
                                <label class="form-check-label">Notificar no Painel (Dashboard)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked>
                                <label class="form-check-label">Enviar E-mail ao Administrador</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                                <label class="form-check-label">Alerta via Telegram / WhatsApp</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveRule()">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function fillModal(name, warning, critical) {
        document.querySelector('#modalTitle span').innerText = name;
        document.getElementById('inputWarning').value = warning;
        document.getElementById('inputCritical').value = critical;
    }

    function saveRule() {
        const name = document.querySelector('#modalTitle span').innerText;
        // Aqui seria a chamada AJAX para salvar
        alert('Regra "' + name + '" atualizada com sucesso!');
        const modal = bootstrap.Modal.getInstance(document.getElementById('editRuleModal'));
        modal.hide();
    }
    </script>

    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-network-wired me-2 text-info"></i> Regras de Disponibilidade</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Verificação</th>
                                <th>Condição</th>
                                <th>Tempo de Resposta</th>
                                <th>Tentativas</th>
                                <th>Ação</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Ping (ICMP)</td>
                                <td>Sem Resposta</td>
                                <td>-</td>
                                <td>3</td>
                                <td><span class="text-danger">Alerta Crítico</span></td>
                                <td><span class="badge bg-success">Ativa</span></td>
                                <td><button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-edit"></i></button></td>
                            </tr>
                            <tr>
                                <td>Serviço Web (HTTP)</td>
                                <td>Código != 200</td>
                                <td>> 5000ms</td>
                                <td>2</td>
                                <td><span class="text-warning">Aviso</span></td>
                                <td><span class="badge bg-secondary">Inativa</span></td>
                                <td><button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-edit"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
