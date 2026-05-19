<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-terminal me-2 text-primary"></i> Central de Logs e Eventos</h1>
            <div class="d-flex gap-2">
                <select class="form-select bg-dark border-secondary text-light w-auto">
                    <option>Filtrar por Servidor...</option>
                    <option>SRV-EXCHANGE-01</option>
                    <option>SRV-AD-PRIMARY</option>
                </select>
                <button class="btn btn-outline-secondary"><i class="fa-solid fa-download"></i> Exportar</button>
            </div>
        </div>
        <p class="text-secondary">Visualização consolidada de Syslog, Event Viewer (Windows) e logs de aplicações.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Log Live Stream -->
    <div class="col-12">
        <div class="noc-card bg-black">
            <div class="noc-card-header border-secondary border-opacity-25">
                <span class="text-success fw-mono"><i class="fa-solid fa-bolt me-2"></i> Real-time Event Stream</span>
                <span class="badge bg-success rounded-pill">Receiving</span>
            </div>
            <div class="noc-card-body p-0" style="height: 500px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 0.85rem;">
                <table class="table table-dark table-hover table-sm mb-0">
                    <thead>
                        <tr class="text-secondary border-secondary border-opacity-25">
                            <th style="width: 180px;">Timestamp</th>
                            <th style="width: 150px;">Servidor</th>
                            <th style="width: 120px;">Fonte</th>
                            <th style="width: 80px;">ID</th>
                            <th>Mensagem</th>
                            <th style="width: 100px;">Nível</th>
                        </tr>
                    </thead>
                    <tbody id="log-stream">
                        <!-- Gerado via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const logEntries = [
        { time: '2026-05-14 11:40:05', server: 'SRV-EXCHANGE-01', source: 'MSExchangeIS', id: '1002', msg: 'The information store has successfully mounted database "Mailbox Database 01".', level: 'Info' },
        { time: '2026-05-14 11:40:12', server: 'SRV-AD-PRIMARY', source: 'Security', id: '4624', msg: 'An account was successfully logged on: Administrator.', level: 'Success' },
        { time: '2026-05-14 11:41:00', server: 'SRV-WEB-03', source: 'IIS-W3SVC', id: '200', msg: 'GET /api/v1/status - Response 200 OK', level: 'Info' },
        { time: '2026-05-14 11:41:30', server: 'SRV-EXCHANGE-01', source: 'MSExchangeTransport', id: '15004', msg: 'The resource pressure on the transport service has increased.', level: 'Warning' },
        { time: '2026-05-14 11:42:10', server: 'VM-HOST-ESX-01', source: 'vpxa', id: '741', msg: 'Virtual machine SRV-SQL-PROD power state changed to ON.', level: 'Info' }
    ];

    function renderLogs() {
        const body = document.getElementById('log-stream');
        body.innerHTML = '';
        logEntries.reverse().forEach(log => {
            const levelClass = log.level === 'Warning' ? 'text-warning' : (log.level === 'Error' ? 'text-danger' : 'text-info');
            const row = `
                <tr class="border-secondary border-opacity-10">
                    <td class="text-secondary">${log.time}</td>
                    <td class="text-primary">${log.server}</td>
                    <td><span class="badge border border-secondary text-secondary">${log.source}</span></td>
                    <td class="text-secondary">${log.id}</td>
                    <td class="text-light">${log.msg}</td>
                    <td><span class="${levelClass}">${log.level}</span></td>
                </tr>
            `;
            body.innerHTML += row;
        });
    }

    renderLogs();

    // Simulação de novos logs
    setInterval(() => {
        const newLog = {
            time: new Date().toISOString().replace('T', ' ').split('.')[0],
            server: 'SRV-EXCHANGE-01',
            source: 'MSExchangeTransport',
            id: '15006',
            msg: 'Queue "Submission" has reached threshold of 500 messages.',
            level: 'Warning'
        };
        logEntries.push(newLog);
        if (logEntries.length > 50) logEntries.shift();
        renderLogs();
    }, 5000);
</script>
