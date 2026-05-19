<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center mb-2">
            <a href="<?= $base_path ?>/users" class="btn btn-outline-secondary btn-sm me-3"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            <h1 class="mb-0">Editar Usuário</h1>
        </div>
        <p class="text-secondary ps-5">Atualize as informações de acesso e permissões de <?= htmlspecialchars($usuario['nome']) ?>.</p>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-6">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-user-pen me-2 text-primary"></i> Formulário de Edição</span>
            </div>
            <div class="noc-card-body">
                <form action="<?= $base_path ?>/user/update" method="POST">
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Nome Completo</label>
                        <input type="text" name="nome" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small">E-mail de Acesso</label>
                        <input type="email" name="email" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" name="senha" class="form-control bg-dark border-secondary text-light" placeholder="••••••••">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small">Nível de Acesso</label>
                        <select name="nivel" class="form-select bg-dark border-secondary text-light">
                            <option value="admin" <?= $usuario['nivel'] === 'admin' ? 'selected' : '' ?>>Administrador (NOC Manager)</option>
                            <option value="operador" <?= $usuario['nivel'] === 'operador' ? 'selected' : '' ?>>Operador (L1/L2)</option>
                            <option value="visitante" <?= $usuario['nivel'] === 'visitante' ? 'selected' : '' ?>>Visitante (Apenas Visualização)</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-2"></i> Salvar Alterações
                        </button>
                        <a href="<?= $base_path ?>/users" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Dicas de Segurança -->
    <div class="col-12 col-lg-6">
        <div class="noc-card border-info border-opacity-25">
            <div class="noc-card-header bg-info bg-opacity-10">
                <span><i class="fa-solid fa-shield-halved me-2 text-info"></i> Notas de Segurança</span>
            </div>
            <div class="noc-card-body">
                <ul class="text-secondary small">
                    <li class="mb-2"><strong>Administrador:</strong> Acesso total ao sistema, inclusive configurações de SNMP e usuários.</li>
                    <li class="mb-2"><strong>Operador:</strong> Pode gerenciar ativos e alertas, mas não altera configurações globais do sistema.</li>
                    <li class="mb-2"><strong>Visitante:</strong> Acesso somente leitura aos dashboards e logs.</li>
                    <hr class="border-secondary border-opacity-10">
                    <li>Se alterar a senha, certifique-se de que o usuário seja notificado por um canal seguro.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
