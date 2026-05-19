<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-users me-2 text-primary"></i> Gerenciamento de Usuários</h1>
            <a href="<?= $base_path ?>/user/create" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Novo Usuário</a>
        </div>
        <p class="text-secondary">Administre quem tem acesso ao sistema de monitoramento.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-list me-2"></i> Usuários Cadastrados</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Nível</th>
                                <th>Data de Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><strong><?= $u['nome'] ?></strong></td>
                                <td><?= $u['email'] ?></td>
                                <td>
                                    <?php 
                                        $badge = $u['nivel'] === 'admin' ? 'bg-danger' : ($u['nivel'] === 'operador' ? 'bg-primary' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= ucfirst($u['nivel']) ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($u['criado_em'])) ?></td>
                                <td>
                                    <a href="<?= $base_path ?>/user/edit?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-edit"></i></a>
                                    <button onclick="confirmDelete(<?= $u['id'] ?>, '<?= $u['nome'] ?>')" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, nome) {
    if (confirm('Tem certeza que deseja excluir o usuário "' + nome + '"? Esta ação não pode ser desfeita.')) {
        window.location.href = '<?= $base_path ?>/user/delete?id=' + id;
    }
}
</script>
