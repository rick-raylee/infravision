<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-gear me-2 text-primary"></i> Configuração de Regras de Alerta</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editRuleModal" onclick="fillModal(null)">
                <i class="fa-solid fa-plus me-1"></i> Nova Regra
            </button>
        </div>
        <p class="text-secondary">Defina os limites e condições que disparam alertas no sistema.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Regras de Performance -->
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
                            <?php if (empty($regras_performance)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">Nenhuma regra de performance cadastrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($regras_performance as $regra): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($regra['nome']) ?></strong></td>
                                        <td><?= htmlspecialchars($regra['condicao']) ?></td>
                                        <td><?= htmlspecialchars($regra['limite_aviso'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($regra['limite_critico'] ?: '-') ?></td>
                                        <td><span class="badge bg-dark"><?= htmlspecialchars($regra['acao']) ?></span></td>
                                        <td>
                                            <span class="badge bg-<?= $regra['ativo'] ? 'success' : 'secondary' ?>">
                                                <?= $regra['ativo'] ? 'Ativa' : 'Inativa' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRuleModal" onclick='fillModal(<?= json_encode($regra, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                    <i class="fa-solid fa-edit"></i>
                                                </button>
                                                <a href="<?= BASE_PATH ?>/rule/delete?id=<?= $regra['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja realmente excluir esta regra?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
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

    <!-- Regras de Disponibilidade -->
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
                                <th>Ação / Severidade</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($regras_disponibilidade)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">Nenhuma regra de disponibilidade cadastrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($regras_disponibilidade as $regra): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($regra['nome']) ?></strong></td>
                                        <td><?= htmlspecialchars($regra['condicao']) ?></td>
                                        <td><?= htmlspecialchars($regra['tempo_resposta'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($regra['tentativas']) ?></td>
                                        <td>
                                            <span class="text-<?= strpos(strtolower($regra['acao']), 'crítico') !== false ? 'danger' : 'warning' ?>">
                                                <?= htmlspecialchars($regra['acao']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $regra['ativo'] ? 'success' : 'secondary' ?>">
                                                <?= $regra['ativo'] ? 'Ativa' : 'Inativa' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRuleModal" onclick='fillModal(<?= json_encode($regra, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                    <i class="fa-solid fa-edit"></i>
                                                </button>
                                                <a href="<?= BASE_PATH ?>/rule/delete?id=<?= $regra['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja realmente excluir esta regra?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
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

<!-- Modal de Edição/Criação de Regra -->
<div class="modal fade" id="editRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalTitle"><span></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editRuleForm" method="POST" action="<?= BASE_PATH ?>/rule/store">
                    <input type="hidden" name="id" id="ruleId">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Nome da Regra / Recurso</label>
                        <input type="text" name="nome" id="ruleName" class="form-control bg-dark border-secondary text-light" required placeholder="Ex: CPU Load, Ping (ICMP)">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Categoria</label>
                        <select name="categoria" id="ruleCategory" class="form-select bg-dark border-secondary text-light" onchange="toggleCategoryFields()" required>
                            <option value="performance">Performance</option>
                            <option value="disponibilidade">Disponibilidade</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Condição</label>
                        <input type="text" name="condicao" id="ruleCondition" class="form-control bg-dark border-secondary text-light" required placeholder="Ex: Maior que (>), Sem Resposta">
                    </div>
                    
                    <!-- Campos para Performance -->
                    <div id="performanceFields">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Limite de Aviso (Warning)</label>
                            <input type="text" name="limite_aviso" id="inputWarning" class="form-control bg-dark border-secondary text-light" placeholder="Ex: 75%">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Limite Crítico (Critical)</label>
                            <input type="text" name="limite_critico" id="inputCritical" class="form-control bg-dark border-secondary text-light" placeholder="Ex: 90%">
                        </div>
                    </div>
                    
                    <!-- Campos para Disponibilidade -->
                    <div id="availabilityFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Tempo de Resposta Máximo</label>
                            <input type="text" name="tempo_resposta" id="inputResponseTime" class="form-control bg-dark border-secondary text-light" placeholder="Ex: > 5000ms, -">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Tentativas antes do Alerta</label>
                            <input type="number" name="tentativas" id="inputRetries" class="form-control bg-dark border-secondary text-light" min="1" value="1">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Ação / Severidade de Notificação</label>
                        <input type="text" name="acao" id="ruleAction" class="form-control bg-dark border-secondary text-light" required placeholder="Ex: Painel + Email, Alerta Crítico, Aviso">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Status da Regra</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ativo" id="ruleActive" value="1" checked>
                            <label class="form-check-label text-secondary small" for="ruleActive">Regra Ativa / Habilitada</label>
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
function toggleCategoryFields() {
    const category = document.getElementById('ruleCategory').value;
    const perfFields = document.getElementById('performanceFields');
    const availFields = document.getElementById('availabilityFields');
    
    if (category === 'performance') {
        perfFields.style.display = 'block';
        availFields.style.display = 'none';
        
        // Opcional: Limpar os campos da outra categoria para não enviar lixo
        document.getElementById('inputResponseTime').value = '';
    } else {
        perfFields.style.display = 'none';
        availFields.style.display = 'block';
        
        // Opcional: Limpar os campos da outra categoria
        document.getElementById('inputWarning').value = '';
        document.getElementById('inputCritical').value = '';
    }
}

function fillModal(rule = null) {
    if (!rule) {
        // Cadastro de Nova Regra
        document.querySelector('#modalTitle span').innerText = 'Nova Regra de Alerta';
        document.getElementById('ruleId').value = '';
        document.getElementById('ruleName').value = '';
        document.getElementById('ruleCategory').value = 'performance';
        document.getElementById('ruleCondition').value = '';
        document.getElementById('inputWarning').value = '';
        document.getElementById('inputCritical').value = '';
        document.getElementById('inputResponseTime').value = '';
        document.getElementById('inputRetries').value = '1';
        document.getElementById('ruleAction').value = '';
        document.getElementById('ruleActive').checked = true;
    } else {
        // Edição de Regra Existente
        document.querySelector('#modalTitle span').innerText = 'Editar Regra: ' + rule.nome;
        document.getElementById('ruleId').value = rule.id;
        document.getElementById('ruleName').value = rule.nome;
        document.getElementById('ruleCategory').value = rule.categoria;
        document.getElementById('ruleCondition').value = rule.condicao;
        document.getElementById('inputWarning').value = rule.limite_aviso || '';
        document.getElementById('inputCritical').value = rule.limite_critico || '';
        document.getElementById('inputResponseTime').value = rule.tempo_resposta || '';
        document.getElementById('inputRetries').value = rule.tentativas || '1';
        document.getElementById('ruleAction').value = rule.acao;
        document.getElementById('ruleActive').checked = parseInt(rule.ativo) === 1;
    }
    toggleCategoryFields();
}

function saveRule() {
    document.getElementById('editRuleForm').submit();
}
</script>
