<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= $base_path ?>/servers" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i></a>
            <h1><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Editar Ativo / Servidor</h1>
        </div>

        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-file-invoice me-2"></i> Informações do Dispositivo</span>
                <span class="badge bg-primary">Apenas Administradores</span>
            </div>
            <div class="noc-card-body p-4">
                <form action="<?= $base_path ?>/device/update" method="POST">
                    <input type="hidden" name="id" value="<?= $srv['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light">Nome do Ativo (Hostname)</label>
                            <input type="text" name="nome" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($srv['nome']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Endereço IP</label>
                            <input type="text" name="ip" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($srv['ip']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Tipo de Dispositivo</label>
                            <select name="tipo" class="form-select bg-dark border-secondary text-light" required>
                                <option value="servidor_windows" <?= $srv['tipo'] === 'servidor_windows' ? 'selected' : '' ?>>Servidor Windows</option>
                                <option value="servidor_linux" <?= $srv['tipo'] === 'servidor_linux' ? 'selected' : '' ?>>Servidor Linux</option>
                                <option value="switch" <?= $srv['tipo'] === 'switch' ? 'selected' : '' ?>>Switch de Rede</option>
                                <option value="firewall" <?= $srv['tipo'] === 'firewall' ? 'selected' : '' ?>>Firewall / Gateway</option>
                                <option value="storage" <?= $srv['tipo'] === 'storage' ? 'selected' : '' ?>>Storage / NAS</option>
                                <option value="sensor_clima" <?= $srv['tipo'] === 'sensor_clima' ? 'selected' : '' ?>>Sensor de Temperatura</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                <i class="fa-solid fa-save me-2"></i> Salvar Alterações
                            </button>
                            <a href="<?= $base_path ?>/servers" class="btn btn-link text-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
