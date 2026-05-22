<?php
// A view será incluída entre o header.php e o footer.php.
// O cabeçalho já define o contêiner principal com a classe .main-content.
$base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');
?>

<style>
    /* Estilos Premium para o Gemma 4 AI Analyst */
    .ai-analyst-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        min-height: 500px;
        background: rgba(21, 26, 39, 0.45);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
        position: relative;
    }

    /* Brilhos sutis no fundo (efeito futurista) */
    .ai-analyst-container::before {
        content: '';
        position: absolute;
        top: -20%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.12) 0%, rgba(59, 130, 246, 0) 70%);
        pointer-events: none;
        z-index: 0;
    }
    
    .ai-analyst-container::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.08) 0%, rgba(168, 85, 247, 0) 70%);
        pointer-events: none;
        z-index: 0;
    }

    .ai-header {
        padding: 1.25rem 2rem;
        background: rgba(15, 23, 42, 0.6);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 1;
    }

    .ai-title-section {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .ai-avatar-wrapper {
        position: relative;
    }

    .ai-avatar {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
    }

    .ai-avatar i {
        font-size: 1.5rem;
        color: #fff;
    }

    .ai-status-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 12px;
        height: 12px;
        background-color: #22c55e;
        border: 2px solid var(--noc-card);
        border-radius: 50%;
        box-shadow: 0 0 8px #22c55e;
    }

    .ai-meta h5 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
        background: linear-gradient(90deg, #fff 0%, #cbd5e1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .ai-meta span {
        font-size: 0.75rem;
        color: var(--noc-secondary);
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .ai-shortcuts {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-ai-shortcut {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #fff;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }

    .btn-ai-shortcut:hover {
        background: rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .btn-ai-shortcut i {
        color: #3b82f6;
    }

    .ai-chat-history {
        flex-grow: 1;
        padding: 2rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        z-index: 1;
    }

    .chat-message {
        display: flex;
        gap: 1rem;
        max-width: 80%;
        animation: messageFadeIn 0.3s ease-out forwards;
    }

    @keyframes messageFadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chat-message.operator {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .chat-message.system {
        align-self: center;
        max-width: 90%;
        text-align: center;
        font-size: 0.8rem;
        color: var(--noc-secondary);
        background: rgba(255, 255, 255, 0.02);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .message-bubble {
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        font-size: 0.95rem;
        line-height: 1.5;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .chat-message.operator .message-bubble {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #fff;
        border-top-right-radius: 0.25rem;
    }

    .chat-message.assistant .message-bubble {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.05);
        color: #f1f5f9;
        border-top-left-radius: 0.25rem;
    }

    .message-time {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.4);
        margin-top: 0.35rem;
        text-align: right;
    }

    .chat-message.operator .message-time {
        text-align: left;
    }

    .ai-chat-input-panel {
        padding: 1.5rem 2rem;
        background: rgba(15, 23, 42, 0.8);
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        z-index: 1;
    }

    .chat-input-container {
        display: flex;
        gap: 1rem;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 0.75rem;
        padding: 0.5rem 0.75rem;
        align-items: center;
        transition: all 0.3s ease;
    }

    .chat-input-container:focus-within {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(59, 130, 246, 0.5);
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.25);
    }

    .chat-input-container textarea {
        flex-grow: 1;
        background: transparent;
        border: none;
        outline: none;
        color: #fff;
        font-size: 0.95rem;
        padding: 0.5rem;
        resize: none;
        max-height: 100px;
        font-family: inherit;
    }

    .chat-input-container textarea::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .btn-send-message {
        width: 42px;
        height: 42px;
        background: #3b82f6;
        border: none;
        border-radius: 0.5rem;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-send-message:hover {
        background: #2563eb;
        transform: scale(1.05);
    }

    .btn-send-message:disabled {
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.3);
        transform: none;
        cursor: not-allowed;
    }

    /* Indicador de Digitação (Pulsando) */
    .typing-indicator {
        display: none;
        align-self: flex-start;
        align-items: center;
        gap: 0.5rem;
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.03);
        padding: 0.75rem 1.25rem;
        border-radius: 1rem;
        border-top-left-radius: 0.25rem;
        font-size: 0.85rem;
        color: var(--noc-secondary);
        animation: pulse 1.5s infinite ease-in-out;
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
    }

    .typing-dots {
        display: flex;
        gap: 3px;
    }

    .typing-dots span {
        width: 6px;
        height: 6px;
        background: #3b82f6;
        border-radius: 50%;
        animation: typingBounce 1.4s infinite ease-in-out both;
    }

    .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .typing-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes typingBounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    /* Markdown Formats */
    .message-bubble p {
        margin-bottom: 0.75rem;
    }
    
    .message-bubble p:last-child {
        margin-bottom: 0;
    }

    .noc-code-block {
        background: rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        overflow-x: auto;
        margin: 0.75rem 0;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 0.85rem;
    }

    .noc-inline-code {
        background: rgba(255, 255, 255, 0.08);
        border-radius: 0.25rem;
        padding: 0.15rem 0.35rem;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 0.85rem;
        color: #f43f5e;
    }

    .message-bubble ul, .message-bubble ol {
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
        padding-left: 1.25rem;
    }

    .message-bubble li {
        margin-bottom: 0.25rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-light">Assistente de Inteligência Artificial</h1>
            <p class="text-secondary mb-0">Análise inteligente, diagnóstico preditivo de infraestrutura e suporte do NOC.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label for="modelSelector" class="text-secondary mb-0 me-2" style="font-size: 0.85rem;"><i class="fa-solid fa-microchip text-primary me-1"></i> Modelo:</label>
            <select id="modelSelector" class="form-select form-select-sm bg-dark text-light border-secondary border-opacity-25" style="width: auto; min-width: 190px;">
                <?php foreach ($candidates as $model): ?>
                    <option value="<?= htmlspecialchars($model['name']) ?>">
                        <?= htmlspecialchars($model['name']) ?> (<?= htmlspecialchars($model['details']['parameter_size']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="ai-analyst-container">
        <!-- Cabeçalho do Chat -->
        <div class="ai-header">
            <div class="ai-title-section">
                <div class="ai-avatar-wrapper">
                    <div class="ai-avatar">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div class="ai-status-indicator"></div>
                </div>
                <div class="ai-meta">
                    <h5 id="aiModelTitle">Groq Cloud IA</h5>
                    <span id="aiModelStatus"><i class="fa-solid fa-cloud text-success"></i> Nuvem Groq (Ultra-rápido)</span>
                </div>
            </div>

            <!-- Atalhos para Análise Rápida -->
            <div class="ai-shortcuts">
                <button type="button" class="btn-ai-shortcut" id="btnAnalyzeLogs">
                    <i class="fa-solid fa-terminal"></i> Correlacionar Logs e Alertas
                </button>
                <button type="button" class="btn-ai-shortcut" id="btnAnalyzeDevices">
                    <i class="fa-solid fa-server"></i> Avaliar Saúde de Dispositivos
                </button>
            </div>
        </div>

        <!-- Histórico do Chat -->
        <div class="ai-chat-history" id="chatHistory">
            <!-- Mensagem Inicial do Assistente -->
            <div class="chat-message assistant">
                <div class="message-bubble">
                    <p>Olá! Sou o seu assistente inteligente do NOC, agora rodando nos supercomputadores do <strong>Groq Cloud</strong>.</p>
                    <p>Você não depende mais do seu hardware local. Posso correlacionar logs e fazer diagnósticos complexos em frações de segundo. Como posso ajudar?</p>
                </div>
            </div>
        </div>

        <!-- Painel de Input -->
        <div class="ai-chat-input-panel">
            <!-- Indicador de Digitação -->
            <div class="typing-indicator" id="typingIndicator">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span>Processando a requisição e gerando o diagnóstico...</span>
            </div>

            <div class="chat-input-container mt-2">
                <textarea id="chatInput" placeholder="Pergunte sobre status da rede, logs ou diagnóstico de servidores..." rows="1"></textarea>
                <button type="button" class="btn-send-message" id="btnSend" disabled>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const chatHistory = document.getElementById("chatHistory");
        const chatInput = document.getElementById("chatInput");
        const btnSend = document.getElementById("btnSend");
        const typingIndicator = document.getElementById("typingIndicator");
        const btnAnalyzeLogs = document.getElementById("btnAnalyzeLogs");
        const btnAnalyzeDevices = document.getElementById("btnAnalyzeDevices");
 
        const chatEndpoint = "<?= $base_path ?>/ai-analyst/chat";

        const modelSelector = document.getElementById("modelSelector");
        const chatTitle = document.getElementById("aiModelTitle");
        const typingIndicatorText = document.querySelector("#typingIndicator > span");

        function updateUIForSelectedModel() {
            if (!modelSelector || modelSelector.options.length === 0) return;
            const selectedText = modelSelector.options[modelSelector.selectedIndex].text;
            if (chatTitle) chatTitle.textContent = selectedText;
            if (typingIndicatorText) {
                typingIndicatorText.textContent = `O modelo ${selectedText} está processando na nuvem Groq...`;
            }
        }

        const savedModel = localStorage.getItem("preferred_groq_model");
        if (savedModel && modelSelector.querySelector(`option[value="${savedModel}"]`)) {
            modelSelector.value = savedModel;
        }
        updateUIForSelectedModel();

        modelSelector.addEventListener("change", function() {
            localStorage.setItem("preferred_groq_model", modelSelector.value);
            updateUIForSelectedModel();
        });

        // Formatação simples de Markdown para HTML
        function parseMarkdown(text) {
            let escaped = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            // Code blocks
            escaped = escaped.replace(/```(\w*)\n([\s\S]*?)```/g, function(match, lang, code) {
                return `<pre class="noc-code-block"><code class="language-${lang}">${code.trim()}</code></pre>`;
            });

            escaped = escaped.replace(/`([^`]+)`/g, '<code class="noc-inline-code">$1</code>');
            escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            escaped = escaped.replace(/^\s*-\s+(.+)$/gm, '<li>$1</li>');
            escaped = escaped.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');

            let lines = escaped.split(/\n\n+/);
            let formatted = lines.map(line => {
                if (line.trim().startsWith('<pre') || line.trim().startsWith('<ul') || line.trim().startsWith('<li>')) {
                    return line;
                }
                return `<p>${line.replace(/\n/g, '<br>')}</p>`;
            }).join('');

            return formatted;
        }

        function formatExistingMessages() {
            const bubbles = chatHistory.querySelectorAll(".message-bubble");
            bubbles.forEach(bubble => {
                if (!bubble.innerHTML.includes("<p>") && !bubble.innerHTML.includes("<strong>")) {
                    bubble.innerHTML = parseMarkdown(bubble.innerText);
                }
            });
        }
        formatExistingMessages();

        chatInput.addEventListener("input", function() {
            btnSend.disabled = chatInput.value.trim() === "";
            chatInput.style.height = "auto";
            chatInput.style.height = (chatInput.scrollHeight) + "px";
        });

        chatInput.addEventListener("keydown", function(e) {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                if (chatInput.value.trim() !== "") {
                    enviarMensagem(chatInput.value.trim());
                }
            }
        });

        btnSend.addEventListener("click", function() {
            if (chatInput.value.trim() !== "") {
                enviarMensagem(chatInput.value.trim());
            }
        });

        btnAnalyzeLogs.addEventListener("click", function() {
            enviarMensagem("", "logs");
        });

        btnAnalyzeDevices.addEventListener("click", function() {
            enviarMensagem("", "dispositivos");
        });

        function formatarHora() {
            const agora = new Date();
            return agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }

        function scrollParaBaixo() {
            chatHistory.scrollTop = chatHistory.scrollHeight;
        }

        function enviarMensagem(mensagemText, tipoAnalise = "chat") {
            chatInput.disabled = true;
            btnSend.disabled = true;
            btnAnalyzeLogs.disabled = true;
            btnAnalyzeDevices.disabled = true;

            if (mensagemText !== "") {
                const userMsgDiv = document.createElement("div");
                userMsgDiv.className = "chat-message operator";
                userMsgDiv.innerHTML = `
                    <div class="message-bubble">
                        ${parseMarkdown(mensagemText)}
                        <div class="message-time">${formatarHora()}</div>
                    </div>
                `;
                chatHistory.appendChild(userMsgDiv);
            } else if (tipoAnalise === "logs") {
                const sysMsgDiv = document.createElement("div");
                sysMsgDiv.className = "chat-message system";
                sysMsgDiv.innerHTML = `<div><i class="fa-solid fa-spinner fa-spin me-2"></i>Consultando Logs do Sistema...</div>`;
                chatHistory.appendChild(sysMsgDiv);
            } else if (tipoAnalise === "dispositivos") {
                const sysMsgDiv = document.createElement("div");
                sysMsgDiv.className = "chat-message system";
                sysMsgDiv.innerHTML = `<div><i class="fa-solid fa-spinner fa-spin me-2"></i>Consultando Métricas dos Dispositivos...</div>`;
                chatHistory.appendChild(sysMsgDiv);
            }

            chatInput.value = "";
            chatInput.style.height = "auto";
            scrollParaBaixo();
            typingIndicator.style.display = "flex";

            const selectedModel = modelSelector ? modelSelector.value : "gemma2:2b";

            // 1. Pedir a resposta para o Backend PHP (Nuvem Groq)
            fetch(chatEndpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    mensagem: mensagemText,
                    tipo_analise: tipoAnalise,
                    modelo: selectedModel
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== "sucesso") {
                    throw new Error(data.mensagem || "Erro na comunicação com a API Groq.");
                }

                typingIndicator.style.display = "none";
                const responseMsgDiv = document.createElement("div");
                responseMsgDiv.className = "chat-message assistant";
                responseMsgDiv.innerHTML = `
                    <div class="message-bubble">
                        ${parseMarkdown(data.resposta)}
                        <div class="message-time">${formatarHora()}</div>
                    </div>
                `;
                chatHistory.appendChild(responseMsgDiv);
                scrollParaBaixo();
            })
            .catch(error => {
                typingIndicator.style.display = "none";
                const errorMsgDiv = document.createElement("div");
                errorMsgDiv.className = "chat-message assistant";
                errorMsgDiv.innerHTML = `
                    <div class="message-bubble border-danger text-danger bg-danger bg-opacity-10">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Falha:</strong> ${error.message}
                        <div class="message-time text-danger">${formatarHora()}</div>
                    </div>
                `;
                chatHistory.appendChild(errorMsgDiv);
                scrollParaBaixo();
            })
            .finally(() => {
                chatInput.disabled = false;
                chatInput.focus();
                btnAnalyzeLogs.disabled = false;
                btnAnalyzeDevices.disabled = false;
            });
        }
    });
</script>
