<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-address-book me-2 text-primary"></i> Destinatários de Alerta</h1>
            <a href="<?= $base_path ?>/alert-contact/create" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Novo Destinatário</a>
        </div>
        <p class="text-secondary">Cadastre quem deve receber as notificações de erro e alertas críticos.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-list me-2"></i> Lista de Contatos Configurados</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nome / Grupo</th>
                                <th>Canal</th>
                                <th>Destino (Email/ID/Fone)</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contatos)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">Nenhum contato cadastrado.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($contatos as $c): ?>
                                <tr>
                                    <td><strong><?= $c['nome'] ?></strong></td>
                                    <td>
                                        <?php if ($c['tipo'] === 'email'): ?>
                                            <span class="text-info"><i class="fa-solid fa-envelope me-1"></i> E-mail</span>
                                        <?php elseif ($c['tipo'] === 'telegram'): ?>
                                            <span class="text-primary"><i class="fa-brands fa-telegram me-1"></i> Telegram</span>
                                        <?php else: ?>
                                            <span class="text-success"><i class="fa-brands fa-whatsapp me-1"></i> WhatsApp</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= $c['destino'] ?></code></td>
                                    <td>
                                        <span class="badge <?= $c['ativo'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $c['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= $base_path ?>/alert-contact/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-edit"></i></a>
                                        <a href="<?= $base_path ?>/alert-contact/delete?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja remover este destinatário?');"><i class="fa-solid fa-trash"></i></a>
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
