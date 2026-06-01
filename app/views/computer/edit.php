<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= $base_path ?>/computers" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i></a>
            <h1><i class="fa-solid fa-laptop text-primary me-2"></i> Editar Computador / Notebook</h1>
        </div>

        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-file-invoice me-2"></i> Ficha Técnica e Cadastro</span>
                <span class="badge bg-primary">Apenas Administradores</span>
            </div>
            <div class="noc-card-body p-4">
                <form action="<?= $base_path ?>/computer/update" method="POST">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light">Hostname (Nome do Computador)</label>
                            <input type="text" name="nome" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['nome']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Endereço IP</label>
                            <input type="text" name="ip" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['ip']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Sistema Operacional</label>
                            <input type="text" name="sistema_operacional" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['sistema_operacional'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Processador</label>
                            <input type="text" name="processador" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['processador'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light">Fabricante</label>
                            <input type="text" name="fabricante" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['fabricante'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light">Modelo</label>
                            <input type="text" name="modelo" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['modelo'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light">Nº de Série / Service Tag</label>
                            <input type="text" name="numero_serie" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['numero_serie'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Colaborador Atribuído</label>
                            <input type="text" name="funcionario" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['funcionario'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Setor</label>
                            <input type="text" name="setor" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['setor'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Nº Patrimônio</label>
                            <input type="text" name="patrimonio" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($c['patrimonio'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Data de Entrega</label>
                            <input type="date" name="data_entrega" class="form-control bg-dark border-secondary text-light" value="<?= $c['data_entrega'] ?>">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                <i class="fa-solid fa-save me-2"></i> Salvar Alterações
                            </button>
                            <a href="<?= $base_path ?>/computers" class="btn btn-link text-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
