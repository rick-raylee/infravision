<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= $base_path ?>/servers" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i></a>
            <h1><i class="fa-solid fa-plus-circle me-2 text-primary"></i> Cadastrar Novo Ativo</h1>
        </div>

        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-file-invoice me-2"></i> Informações do Dispositivo</span>
                <span class="badge bg-primary">Apenas Administradores</span>
            </div>
            <div class="noc-card-body p-4">
                <form action="<?= $base_path ?>/device/store" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light">Nome do Ativo (Hostname)</label>
                            <input type="text" name="nome" class="form-control bg-dark border-secondary text-light" placeholder="Ex: SRV-APP-FINANCEIRO" value="<?= htmlspecialchars($_GET['nome'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Endereço IP</label>
                            <input type="text" name="ip" class="form-control bg-dark border-secondary text-light" placeholder="Ex: 192.168.1.10" value="<?= htmlspecialchars($_GET['ip'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Tipo de Dispositivo</label>
                            <select name="tipo" class="form-select bg-dark border-secondary text-light" required>
                                <option value="servidor_windows">Servidor Windows</option>
                                <option value="servidor_linux">Servidor Linux</option>
                                <option value="switch">Switch de Rede</option>
                                <option value="firewall">Firewall / Gateway</option>
                                <option value="storage">Storage / NAS</option>
                                <option value="sensor_clima">Sensor de Temperatura</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Comunidade SNMP (Opcional)</label>
                            <input type="text" name="snmp" class="form-control bg-dark border-secondary text-light" placeholder="Padrão: public">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <hr class="border-secondary">
                            <h5 class="mb-3"><i class="fa-solid fa-bell me-2"></i> Configurações de Alerta</h5>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Limite CPU (Crítico)</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-dark border-secondary text-light" value="90">
                                <span class="input-group-text bg-dark border-secondary text-secondary">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Limite RAM (Crítico)</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-dark border-secondary text-light" value="85">
                                <span class="input-group-text bg-dark border-secondary text-secondary">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Limite Disco (Crítico)</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-dark border-secondary text-light" value="95">
                                <span class="input-group-text bg-dark border-secondary text-secondary">%</span>
                            </div>
                        </div>

                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                <i class="fa-solid fa-save me-2"></i> Salvar e Iniciar Monitoramento
                            </button>
                            <a href="<?= $base_path ?>/servers" class="btn btn-link text-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
