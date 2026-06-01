<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= $base_path ?>/computers" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i></a>
            <h1><i class="fa-solid fa-laptop text-primary me-2"></i> Novo Computador / Notebook</h1>
        </div>

        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-file-invoice me-2"></i> Cadastro Manual de Estação de Trabalho</span>
                <span class="badge bg-primary">Apenas Administradores</span>
            </div>
            <div class="noc-card-body p-4">
                <form action="<?= $base_path ?>/computer/store" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light">Hostname (Nome do Computador)</label>
                            <input type="text" name="nome" class="form-control bg-dark border-secondary text-light" placeholder="Ex: PC-TI-JOAO" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Endereço IP</label>
                            <input type="text" name="ip" class="form-control bg-dark border-secondary text-light" placeholder="Ex: 192.168.1.50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Sistema Operacional</label>
                            <input type="text" name="sistema_operacional" class="form-control bg-dark border-secondary text-light" placeholder="Ex: Windows 11 Pro 23H2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Processador</label>
                            <input type="text" name="processador" class="form-control bg-dark border-secondary text-light" placeholder="Ex: Intel Core i5-11400F">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light">Fabricante</label>
                            <input type="text" name="fabricante" class="form-control bg-dark border-secondary text-light" placeholder="Ex: Dell Inc.">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light">Modelo</label>
                            <input type="text" name="modelo" class="form-control bg-dark border-secondary text-light" placeholder="Ex: Vostro 3400">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light">Nº de Série / Service Tag</label>
                            <input type="text" name="numero_serie" class="form-control bg-dark border-secondary text-light" placeholder="Ex: 9B7XTY2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Colaborador Atribuído</label>
                            <input type="text" name="funcionario" class="form-control bg-dark border-secondary text-light" placeholder="Ex: João da Silva">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Setor</label>
                            <input type="text" name="setor" class="form-control bg-dark border-secondary text-light" placeholder="Ex: Financeiro">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Nº Patrimônio</label>
                            <input type="text" name="patrimonio" class="form-control bg-dark border-secondary text-light" placeholder="Ex: PAT-2024-052">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Data de Entrega</label>
                            <input type="date" name="data_entrega" class="form-control bg-dark border-secondary text-light">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                <i class="fa-solid fa-save me-2"></i> Cadastrar Computador
                            </button>
                            <a href="<?= $base_path ?>/computers" class="btn btn-link text-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
