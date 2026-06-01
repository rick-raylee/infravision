<div class="row justify-content-center">
    <div class="col-12 col-md-6">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= $base_path ?>/alert-contacts" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i></a>
            <h1><i class="fa-solid fa-user-pen me-2 text-primary"></i> Editar Destinatário</h1>
        </div>

        <div class="noc-card">
            <div class="noc-card-body p-4">
                <form action="<?= $base_path ?>/alert-contact/update" method="POST">
                    <input type="hidden" name="id" value="<?= $contato['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label text-light">Nome do Contato ou Grupo</label>
                        <input type="text" name="nome" class="form-control bg-dark border-secondary text-light" value="<?= htmlspecialchars($contato['nome']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">Canal de Notificação</label>
                        <select name="tipo" class="form-select bg-dark border-secondary text-light" id="tipoSelect" required>
                            <option value="email" <?= $contato['tipo'] === 'email' ? 'selected' : '' ?>>E-mail</option>
                            <option value="telegram" <?= $contato['tipo'] === 'telegram' ? 'selected' : '' ?>>Telegram</option>
                            <option value="whatsapp" <?= $contato['tipo'] === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light" id="destinoLabel">Destinatário</label>
                        <input type="text" name="destino" class="form-control bg-dark border-secondary text-light" id="destinoInput" value="<?= htmlspecialchars($contato['destino']) ?>" required>
                        <div class="form-text text-secondary" id="destinoHelp">
                            Insira o endereço de destino correspondente.
                        </div>
                    </div>
                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ativo" id="flexSwitchCheckChecked" <?= $contato['ativo'] ? 'checked' : '' ?>>
                        <label class="form-check-label text-light" for="flexSwitchCheckChecked">Contato Ativo</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fa-solid fa-save me-2"></i> Salvar Alterações
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateLabels(tipo) {
        const label = document.getElementById('destinoLabel');
        const input = document.getElementById('destinoInput');
        const help = document.getElementById('destinoHelp');
        
        if (tipo === 'email') {
            label.innerText = 'Endereço de E-mail';
            help.innerText = 'Insira o e-mail que receberá os alertas.';
        } else if (tipo === 'telegram') {
            label.innerText = 'Chat ID do Telegram';
            help.innerText = 'Insira o Chat ID do grupo ou do usuário.';
        } else {
            label.innerText = 'Número do WhatsApp';
            help.innerText = 'Insira o número com DDI e DDD (apenas números).';
        }
    }

    document.getElementById('tipoSelect').addEventListener('change', function() {
        updateLabels(this.value);
    });

    // Run once on load
    updateLabels(document.getElementById('tipoSelect').value);
</script>
