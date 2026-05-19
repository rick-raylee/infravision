<div class="row justify-content-center">
    <div class="col-12 col-md-6">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= $base_path ?>/alert-contacts" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i></a>
            <h1><i class="fa-solid fa-user-plus me-2 text-primary"></i> Novo Destinatário</h1>
        </div>

        <div class="noc-card">
            <div class="noc-card-body p-4">
                <form action="<?= $base_path ?>/alert-contact/store" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-light">Nome do Contato ou Grupo</label>
                        <input type="text" name="nome" class="form-control bg-dark border-secondary text-light" placeholder="Ex: Equipe de TI / João Silva" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">Canal de Notificação</label>
                        <select name="tipo" class="form-select bg-dark border-secondary text-light" id="tipoSelect" required>
                            <option value="email">E-mail</option>
                            <option value="telegram">Telegram</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-light" id="destinoLabel">Endereço de E-mail</label>
                        <input type="text" name="destino" class="form-control bg-dark border-secondary text-light" id="destinoInput" placeholder="exemplo@empresa.com" required>
                        <div class="form-text text-secondary" id="destinoHelp">
                            Insira o e-mail que receberá os alertas.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fa-solid fa-save me-2"></i> Cadastrar Destinatário
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tipoSelect').addEventListener('change', function() {
        const label = document.getElementById('destinoLabel');
        const input = document.getElementById('destinoInput');
        const help = document.getElementById('destinoHelp');
        
        if (this.value === 'email') {
            label.innerText = 'Endereço de E-mail';
            input.placeholder = 'exemplo@empresa.com';
            help.innerText = 'Insira o e-mail que receberá os alertas.';
        } else if (this.value === 'telegram') {
            label.innerText = 'Chat ID do Telegram';
            input.placeholder = '-100123456789';
            help.innerText = 'Insira o Chat ID do grupo ou do usuário.';
        } else {
            label.innerText = 'Número do WhatsApp';
            input.placeholder = '5511999999999';
            help.innerText = 'Insira o número com DDI e DDD (apenas números).';
        }
    });
</script>
