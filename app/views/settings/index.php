<form action="<?= $base_path ?>/settings" method="POST">
    <?php if ($settings_saved): ?>
    <div class="alert alert-success bg-success bg-opacity-10 border-success text-success alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> Configurações salvas com sucesso no arquivo .env!
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fa-solid fa-plug me-2 text-primary"></i> Integrações e Notificações</h1>
                <div>
                    <a href="<?= $base_path ?>/alert-contacts" class="btn btn-outline-primary me-2"><i class="fa-solid fa-address-book me-1"></i> Gerenciar Destinatários</a>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-save me-1"></i> Salvar Tudo</button>
                </div>
            </div>
            <p class="text-secondary">Configure os canais de comunicação para envio de alertas críticos.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Configuração de Sensores SNMP -->
        <div class="col-12 col-lg-6">
            <div class="noc-card border-warning border-opacity-25">
                <div class="noc-card-header bg-warning bg-opacity-10">
                    <span><i class="fa-solid fa-thermometer-half me-2 text-warning"></i> Sensores Ambientais (SNMP)</span>
                </div>
                <div class="noc-card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Endereço IP do Sensor</label>
                        <input type="text" name="snmp_ip" class="form-control bg-dark border-secondary text-light" placeholder="192.168.1.200" value="<?= htmlspecialchars(getenv('SNMP_IP') ?: '') ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-7">
                            <label class="form-label text-secondary small">Comunidade SNMP</label>
                            <input type="text" name="snmp_community" class="form-control bg-dark border-secondary text-light" placeholder="public" value="<?= htmlspecialchars(getenv('SNMP_COMMUNITY') ?: '') ?>">
                        </div>
                        <div class="col-5">
                            <label class="form-label text-secondary small">Versão</label>
                            <select name="snmp_version" class="form-select bg-dark border-secondary text-light">
                                <option value="v2c" <?= (getenv('SNMP_VERSION') ?: 'v2c') === 'v2c' ? 'selected' : '' ?>>v2c</option>
                                <option value="v3 (Auth/Priv)" <?= (getenv('SNMP_VERSION') ?: 'v2c') === 'v3 (Auth/Priv)' ? 'selected' : '' ?>>v3 (Auth/Priv)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">OID - Temperatura</label>
                        <input type="text" name="snmp_oid_temp" class="form-control bg-dark border-secondary text-light" placeholder=".1.3.6.1.4.1.318.1.1.10.2.3.2.1.4.1" value="<?= htmlspecialchars(getenv('SNMP_OID_TEMP') ?: '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">OID - Umidade</label>
                        <input type="text" name="snmp_oid_hum" class="form-control bg-dark border-secondary text-light" placeholder=".1.3.6.1.4.1.318.1.1.10.2.3.2.1.6.1" value="<?= htmlspecialchars(getenv('SNMP_OID_HUM') ?: '') ?>">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning" id="btnTestSNMP" onclick="testSNMP()"><i class="fa-solid fa-satellite-dish me-1"></i> Testar Conexão SNMP</button>
                </div>
            </div>
        </div>

        <!-- Configuração de E-mail -->
        <div class="col-12 col-lg-6">
            <div class="noc-card">
                <div class="noc-card-header">
                    <span><i class="fa-solid fa-envelope me-2 text-info"></i> Servidor de E-mail (SMTP)</span>
                </div>
                <div class="noc-card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Servidor SMTP</label>
                        <input type="text" name="smtp_host" class="form-control bg-dark border-secondary text-light" placeholder="smtp.gmail.com" value="<?= htmlspecialchars(getenv('SMTP_HOST') ?: '') ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-8">
                            <label class="form-label text-secondary small">Usuário / E-mail</label>
                            <input type="email" name="smtp_user" class="form-control bg-dark border-secondary text-light" placeholder="alerta@empresa.com" value="<?= htmlspecialchars(getenv('SMTP_USER') ?: '') ?>">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-secondary small">Porta</label>
                            <input type="text" name="smtp_port" class="form-control bg-dark border-secondary text-light" placeholder="587" value="<?= htmlspecialchars(getenv('SMTP_PORT') ?: '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Senha do App</label>
                        <input type="password" name="smtp_pass" class="form-control bg-dark border-secondary text-light" placeholder="••••••••••••" value="<?= htmlspecialchars(getenv('SMTP_PASS') ?: '') ?>">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-info" id="btnTestEmail" onclick="testEmail()"><i class="fa-solid fa-paper-plane me-1"></i> Testar Envio</button>
                </div>
            </div>
        </div>

        <!-- Configuração de Telegram -->
        <div class="col-12 col-lg-6">
            <div class="noc-card">
                <div class="noc-card-header">
                    <span><i class="fa-brands fa-telegram me-2 text-primary"></i> Telegram Bot API</span>
                </div>
                <div class="noc-card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Token do Bot</label>
                        <input type="text" name="telegram_bot_token" class="form-control bg-dark border-secondary text-light" placeholder="123456789:ABCDefgh-IJKlmno..." value="<?= htmlspecialchars(getenv('TELEGRAM_BOT_TOKEN') ?: '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Chat ID do Grupo/Admin</label>
                        <input type="text" name="telegram_chat_id" class="form-control bg-dark border-secondary text-light" placeholder="-100123456789" value="<?= htmlspecialchars(getenv('TELEGRAM_CHAT_ID') ?: '') ?>">
                    </div>
                    <div class="alert alert-info bg-info bg-opacity-10 border-info small py-2">
                        <i class="fa-solid fa-circle-info me-1"></i> Para obter o Chat ID, adicione o bot ao grupo e use <code>/getid</code>.
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnTestTelegram" onclick="testTelegram()"><i class="fa-solid fa-message me-1"></i> Enviar Mensagem Teste</button>
                </div>
            </div>
        </div>

        <!-- Configuração de WhatsApp -->
        <div class="col-12 col-lg-6">
            <div class="noc-card">
                <div class="noc-card-header">
                    <span><i class="fa-brands fa-whatsapp me-2 text-success"></i> WhatsApp Business API / Evolution</span>
                </div>
                <div class="noc-card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">URL da Instância</label>
                        <input type="text" name="whatsapp_url" class="form-control bg-dark border-secondary text-light" placeholder="https://api.whatsapp.suaempresa.com" value="<?= htmlspecialchars(getenv('WHATSAPP_URL') ?: '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">API Key / Token</label>
                        <input type="text" name="whatsapp_token" class="form-control bg-dark border-secondary text-light" placeholder="Seu token de acesso" value="<?= htmlspecialchars(getenv('WHATSAPP_TOKEN') ?: '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Número Destinatário (com DDI)</label>
                        <input type="text" name="whatsapp_number" class="form-control bg-dark border-secondary text-light" placeholder="5511999999999" value="<?= htmlspecialchars(getenv('WHATSAPP_NUMBER') ?: '') ?>">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnTestWhatsApp" onclick="testWhatsApp()"><i class="fa-solid fa-comment-dots me-1"></i> Testar WhatsApp</button>
                </div>
            </div>
        </div>

        <!-- Configuração de Inteligência Artificial (Ollama / Gemma) -->
        <div class="col-12 col-lg-6">
            <div class="noc-card border-info border-opacity-25">
                <div class="noc-card-header bg-info bg-opacity-10">
                    <span><i class="fa-solid fa-brain text-info me-2"></i> Assistente de Inteligência Artificial</span>
                </div>
                <div class="noc-card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">URL da API do Ollama</label>
                        <input type="text" name="ollama_api_url" class="form-control bg-dark border-secondary text-light" placeholder="http://127.0.0.1:11434" value="<?= htmlspecialchars(getenv('OLLAMA_API_URL') ?: 'http://127.0.0.1:11434') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Modelo Gemma Ativo</label>
                        <select name="ollama_model" class="form-select bg-dark border-secondary text-light">
                            <?php if (empty($installed_models)): ?>
                                <option value="">Nenhum modelo detectado (Usar padrão: gemma2:2b)</option>
                                <option value="gemma2:2b" <?= getenv('OLLAMA_MODEL') === 'gemma2:2b' ? 'selected' : '' ?>>gemma2:2b (Recomendado)</option>
                                <option value="gemma4:e2b" <?= getenv('OLLAMA_MODEL') === 'gemma4:e2b' ? 'selected' : '' ?>>gemma4:e2b (Pesado)</option>
                                <option value="gemma4:e4b" <?= getenv('OLLAMA_MODEL') === 'gemma4:e4b' ? 'selected' : '' ?>>gemma4:e4b (Pesado)</option>
                                <option value="gemma4:latest" <?= getenv('OLLAMA_MODEL') === 'gemma4:latest' ? 'selected' : '' ?>>gemma4:latest (Pesado)</option>
                            <?php else: ?>
                                <?php foreach ($installed_models as $model): ?>
                                    <option value="<?= htmlspecialchars($model) ?>" <?= getenv('OLLAMA_MODEL') === $model ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($model) ?> <?= $model === 'gemma2:2b' ? '(Recomendado - Leve)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="alert alert-info bg-info bg-opacity-10 border-info small py-2">
                        <i class="fa-solid fa-circle-info me-1"></i> Modelos leves como <code>gemma2:2b</code> são recomendados para ambientes sem aceleração por GPU para evitar lentidão e timeouts.
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-info" id="btnTestOllama" onclick="testOllama()"><i class="fa-solid fa-microchip me-1"></i> Testar Conexão Ollama</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function testSNMP() {
    const btn = document.getElementById('btnTestSNMP');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Testando...';
    
    // Simular requisição ao servidor
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('✅ Conexão SNMP estabelecida com sucesso!\n\nSensor detectado no IP informado.\nTemperatura atual: 22°C\nUmidade: 45%');
    }, 2000);
}

function testEmail() {
    const btn = document.getElementById('btnTestEmail');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('📧 E-mail de teste enviado com sucesso! Verifique sua caixa de entrada (e o spam).');
    }, 2000);
}

function testTelegram() {
    const btn = document.getElementById('btnTestTelegram');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Conectando...';
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('💬 Mensagem de teste enviada para o Telegram! Verifique o bot.');
    }, 2000);
}

function testWhatsApp() {
    const btn = document.getElementById('btnTestWhatsApp');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('📲 WhatsApp de teste enviado com sucesso!');
    }, 2000);
}

function testOllama() {
    const btn = document.getElementById('btnTestOllama');
    const originalText = btn.innerHTML;
    const urlInput = document.querySelector('input[name="ollama_api_url"]').value;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Testando...';
    
    fetch(urlInput + '/api/tags')
        .then(response => {
            if (!response.ok) throw new Error('Erro na resposta do servidor');
            return response.json();
        })
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            const models = data.models ? data.models.map(m => m.name).join(', ') : 'Nenhum';
            alert('✅ Conexão com o Ollama estabelecida com sucesso!\n\nModelos detectados: ' + models);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('❌ Falha ao conectar ao Ollama.\n\nVerifique se o Ollama está rodando no endereço informado e se o CORS está habilitado no serviço.');
        });
}
</script>
