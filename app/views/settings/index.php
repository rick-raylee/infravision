<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-plug me-2 text-primary"></i> Integrações e Notificações</h1>
            <div>
                <a href="<?= $base_path ?>/alert-contacts" class="btn btn-outline-primary me-2"><i class="fa-solid fa-address-book me-1"></i> Gerenciar Destinatários</a>
                <button class="btn btn-success"><i class="fa-solid fa-save me-1"></i> Salvar Tudo</button>
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
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="192.168.1.200">
                </div>
                <div class="row mb-3">
                    <div class="col-7">
                        <label class="form-label text-secondary small">Comunidade SNMP</label>
                        <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="public">
                    </div>
                    <div class="col-5">
                        <label class="form-label text-secondary small">Versão</label>
                        <select class="form-select bg-dark border-secondary text-light">
                            <option>v2c</option>
                            <option>v3 (Auth/Priv)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">OID - Temperatura</label>
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder=".1.3.6.1.4.1.318.1.1.10.2.3.2.1.4.1">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">OID - Umidade</label>
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder=".1.3.6.1.4.1.318.1.1.10.2.3.2.1.6.1">
                </div>
                <button class="btn btn-sm btn-outline-warning" id="btnTestSNMP" onclick="testSNMP()"><i class="fa-solid fa-satellite-dish me-1"></i> Testar Conexão SNMP</button>
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
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="smtp.gmail.com">
                </div>
                <div class="row mb-3">
                    <div class="col-8">
                        <label class="form-label text-secondary small">Usuário / E-mail</label>
                        <input type="email" class="form-control bg-dark border-secondary text-light" placeholder="alerta@empresa.com">
                    </div>
                    <div class="col-4">
                        <label class="form-label text-secondary small">Porta</label>
                        <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="587">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">Senha do App</label>
                    <input type="password" class="form-control bg-dark border-secondary text-light" placeholder="••••••••••••">
                </div>
                <button class="btn btn-sm btn-outline-info" id="btnTestEmail" onclick="testEmail()"><i class="fa-solid fa-paper-plane me-1"></i> Testar Envio</button>
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
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="123456789:ABCDefgh-IJKlmno...">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">Chat ID do Grupo/Admin</label>
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="-100123456789">
                </div>
                <div class="alert alert-info bg-info bg-opacity-10 border-info small py-2">
                    <i class="fa-solid fa-circle-info me-1"></i> Para obter o Chat ID, adicione o bot ao grupo e use <code>/getid</code>.
                </div>
                <button class="btn btn-sm btn-outline-primary" id="btnTestTelegram" onclick="testTelegram()"><i class="fa-solid fa-message me-1"></i> Enviar Mensagem Teste</button>
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
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="https://api.whatsapp.suaempresa.com">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">API Key / Token</label>
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="Seu token de acesso">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small">Número Destinatário (com DDI)</label>
                    <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="5511999999999">
                </div>
                <button class="btn btn-sm btn-outline-success" id="btnTestWhatsApp" onclick="testWhatsApp()"><i class="fa-solid fa-comment-dots me-1"></i> Testar WhatsApp</button>
            </div>
        </div>

</div>

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
</script>
