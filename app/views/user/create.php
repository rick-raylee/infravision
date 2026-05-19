<div class="row justify-content-center">
    <div class="col-12 col-md-6">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= $base_path ?>/users" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i></a>
            <h1><i class="fa-solid fa-user-plus me-2 text-primary"></i> Cadastrar Usuário</h1>
        </div>

        <div class="noc-card">
            <div class="noc-card-body p-4">
                <form action="<?= $base_path ?>/user/store" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-light">Nome Completo</label>
                        <input type="text" name="nome" class="form-control bg-dark border-secondary text-light" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">E-mail (Login)</label>
                        <input type="email" name="email" class="form-control bg-dark border-secondary text-light" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">Senha</label>
                        <input type="password" name="senha" class="form-control bg-dark border-secondary text-light" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-light">Nível de Acesso</label>
                        <select name="nivel" class="form-select bg-dark border-secondary text-light" required>
                            <option value="visitante">Visitante (Apenas Visualização)</option>
                            <option value="operador">Operador (Dashboard + Alertas)</option>
                            <option value="admin">Administrador (Acesso Total)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fa-solid fa-save me-2"></i> Salvar Usuário
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
