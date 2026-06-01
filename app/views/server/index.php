<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-server me-2 text-primary"></i> Servidores</h1>
            <?php if (($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?>
                <a href="<?= $base_path ?>/device/create" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Adicionar Servidor</a>
            <?php endif; ?>
        </div>
        <p class="text-secondary">Gestão de performance, recursos e serviços de servidores Windows e Linux.</p>
    </div>
</div>

<div class="row g-4">
    <?php
    if (empty($servidores)):
    ?>
    <div class="col-12 text-center py-5">
        <i class="fa-solid fa-server fa-3x text-secondary mb-3"></i>
        <h4 class="text-secondary">Nenhum servidor cadastrado.</h4>
        <a href="<?= $base_path ?>/device/create" class="btn btn-primary mt-2">Cadastrar Primeiro Servidor</a>
    </div>
    <?php
    endif;

    foreach ($servidores as $srv):
        $statusClass = 'status-' . $srv['status'];
        $progressBarClass = $srv['cpu'] > 85 ? 'bg-danger' : ($srv['cpu'] > 60 ? 'bg-warning' : 'bg-primary');
    ?>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><span class="status-indicator <?= $statusClass ?>"></span> <?= $srv['nome'] ?></span>
                <div class="dropdown">
                    <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow border-secondary">
                        <li><a class="dropdown-item" href="<?= $base_path ?>/server/details?nome=<?= urlencode($srv['nome']) ?>"><i class="fa-solid fa-gauge-high me-2 small"></i> Ver Detalhes</a></li>
                        <li><a class="dropdown-item" href="<?= $base_path ?>/services"><i class="fa-solid fa-globe me-2 small"></i> Monitorar Serviços</a></li>
                        <?php if (($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?>
                            <li><a class="dropdown-item" href="<?= $base_path ?>/device/edit?id=<?= $srv['id'] ?>"><i class="fa-solid fa-pen-to-square me-2 small"></i> Editar</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="confirmRemoval('<?= $srv['id'] ?>', '<?= $srv['nome'] ?>')"><i class="fa-solid fa-trash-can me-2 small"></i> Remover</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="noc-card-body">
                <div class="mb-2 small">
                    <span class="text-secondary">IP:</span> <?= $srv['ip'] ?><br>
                    <span class="text-secondary">S.O:</span> <?= $srv['so'] ?>
                </div>
                
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span><i class="fa-solid fa-microchip me-1"></i> CPU Load</span>
                        <span><?= $srv['cpu'] ?>%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: #1e2638;">
                        <div class="progress-bar <?= $progressBarClass ?>" style="width: <?= $srv['cpu'] ?>%"></div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span><i class="fa-solid fa-memory me-1"></i> Memória RAM</span>
                        <span><?= round($srv['ram']) ?>% (<?= $srv['ram_usada'] ?>GB / <?= $srv['ram_total'] ?>GB)</span>

                    </div>
                    <div class="progress" style="height: 6px; background-color: #1e2638;">
                        <div class="progress-bar <?= $srv['ram'] > 80 ? 'bg-danger' : 'bg-info' ?>" style="width: <?= $srv['ram'] ?>%"></div>
                    </div>
                </div>

                <div class="mt-3">
                    <?php if ($srv['disco_percent'] !== null): ?>
                    <?php $dp = round($srv['disco_percent']); $dpColor = $dp >= 90 ? 'bg-danger' : ($dp >= 75 ? 'bg-warning' : 'bg-success'); $dpText = $dp >= 90 ? 'text-danger' : ($dp >= 75 ? 'text-warning' : 'text-success'); ?>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span><i class="fa-solid fa-hard-drive me-1"></i> Disco (C:)</span>
                        <span class="<?= $dpText ?>"><?= $dp ?>% (<?= $srv['disco_total'] - $srv['disco_livre'] ?> / <?= $srv['disco_total'] ?> GB)</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: #1e2638;">
                        <div class="progress-bar <?= $dpColor ?>" style="width: <?= $dp ?>%"></div>
                    </div>
                    <div class="text-secondary mt-1" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-<?= $dp >= 90 ? 'triangle-exclamation text-danger' : ($dp >= 75 ? 'clock text-warning' : 'circle-check text-success') ?> me-1"></i>
                        <?= $srv['disco_livre'] ?> GB livres de <?= $srv['disco_total'] ?> GB
                    </div>
                    <?php else: ?>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span><i class="fa-solid fa-hard-drive me-1"></i> Disco</span>
                        <span class="text-secondary">Sem dados</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: #1e2638;">
                        <div class="progress-bar bg-secondary" style="width: 0%"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function confirmRemoval(serverId, serverName) {
    if (confirm('Tem certeza que deseja remover o servidor ' + serverName + ' do monitoramento?')) {
        window.location.href = '<?= $base_path ?>/device/delete?id=' + serverId;
    }
}
</script>
