<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-bell me-2 text-danger"></i> Central de Alertas</h1>
            <div>
                <a href="<?= $base_path ?>/alert/test" class="btn btn-outline-warning me-2"><i class="fa-solid fa-vial me-1"></i> Gerar Alerta de Teste</a>
                <button class="btn btn-outline-secondary me-2"><i class="fa-solid fa-check-double me-1"></i> Reconhecer Todos</button>
                <a href="<?= $base_path ?>/rules" class="btn btn-primary"><i class="fa-solid fa-gear me-1"></i> Configurar Regras</a>
            </div>
        </div>
        <p class="text-secondary">Histórico e gerenciamento de eventos críticos da infraestrutura.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-triangle-exclamation me-2"></i> Alertas Ativos</span>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option>Todas as Severidades</option>
                        <option class="text-danger">Crítico</option>
                        <option class="text-warning">Aviso</option>
                        <option class="text-info">Informativo</option>
                    </select>
                </div>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Severidade</th>
                                <th>Dispositivo</th>
                                <th>Mensagem</th>
                                <th>Duração</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($alertas)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">Nenhum alerta ativo no momento.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($alertas as $a): ?>
                                <tr id="alert-row-<?= $a['id'] ?>">
                                    <td>
                                        <?php 
                                            $badge = 'bg-info';
                                            if ($a['severidade'] === 'critico') $badge = 'bg-danger';
                                            elseif ($a['severidade'] === 'erro') $badge = 'bg-danger';
                                            elseif ($a['severidade'] === 'aviso') $badge = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?= $badge ?>"><?= strtoupper($a['severidade']) ?></span>
                                    </td>
                                    <td><?= $a['dispositivo_nome'] ?></td>
                                    <td><?= $a['mensagem'] ?></td>
                                    <td><?= date('d/m H:i', strtotime($a['criado_em'])) ?></td>
                                    <td>
                                        <?php if ($a['status'] === 'ativo'): ?>
                                            <span class="text-danger status-text"><i class="fa-solid fa-circle-exclamation me-1"></i> Ativo</span>
                                        <?php elseif ($a['status'] === 'reconhecido'): ?>
                                            <span class="text-info status-text"><i class="fa-solid fa-eye me-1"></i> Reconhecido</span>
                                        <?php else: ?>
                                            <span class="text-success status-text"><i class="fa-solid fa-check me-1"></i> Resolvido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($a['status'] === 'ativo'): ?>
                                            <button class="btn btn-sm btn-outline-primary btn-ack" onclick="acknowledgeAlert(<?= $a['id'] ?>)">Reconhecer</button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>Reconhecido</button>
                                        <?php endif; ?>
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
function acknowledgeAlert(id) {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    const formData = new FormData();
    formData.append('id', id);

    fetch('<?= $base_path ?>/alert/acknowledge', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const row = document.getElementById('alert-row-' + id);
            row.querySelector('.status-text').innerHTML = '<i class="fa-solid fa-eye me-1"></i> Reconhecido';
            row.querySelector('.status-text').className = 'text-info status-text';
            btn.className = 'btn btn-sm btn-secondary';
            btn.innerHTML = 'Reconhecido';
            btn.disabled = true;
        } else {
            alert('Erro: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = 'Reconhecer';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar solicitação');
        btn.disabled = false;
        btn.innerHTML = 'Reconhecer';
    });
}
</script>
<?php $extra_js = ob_get_clean(); ?>
