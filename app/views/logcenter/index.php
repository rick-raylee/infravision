<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-terminal me-2 text-primary"></i> Central de Logs e Eventos</h1>
            <div class="d-flex gap-2">
                <select class="form-select bg-dark border-secondary text-light w-auto" id="filterServer">
                    <option value="">Todos os dispositivos</option>
                    <?php foreach ($servidores as $srv): ?>
                        <option value="<?= htmlspecialchars($srv) ?>"><?= htmlspecialchars($srv) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <p class="text-secondary">Alertas e registros de auditoria reais do banco de dados.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="noc-card bg-black">
            <div class="noc-card-header border-secondary border-opacity-25">
                <span class="text-success fw-mono"><i class="fa-solid fa-bolt me-2"></i> Eventos registrados</span>
                <span class="badge bg-secondary rounded-pill"><?= count($eventos) ?> eventos</span>
            </div>
            <div class="noc-card-body p-0" style="height: 500px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 0.85rem;">
                <table class="table table-dark table-hover table-sm mb-0">
                    <thead>
                        <tr class="text-secondary border-secondary border-opacity-25">
                            <th style="width: 180px;">Timestamp</th>
                            <th style="width: 150px;">Origem</th>
                            <th style="width: 120px;">Severidade</th>
                            <th>Mensagem</th>
                            <th style="width: 100px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="log-stream">
                        <?php if (empty($eventos)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    Nenhum evento registrado. Alertas e ações do sistema aparecerão aqui.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($eventos as $log): ?>
                                <?php
                                    $sev = $log['severidade'] ?? 'info';
                                    $levelClass = in_array($sev, ['erro', 'critico'], true) ? 'text-danger'
                                        : ($sev === 'aviso' ? 'text-warning' : 'text-info');
                                ?>
                                <tr class="border-secondary border-opacity-10 log-row" data-server="<?= htmlspecialchars($log['servidor'] ?? '') ?>">
                                    <td class="text-secondary"><?= date('Y-m-d H:i:s', strtotime($log['criado_em'])) ?></td>
                                    <td class="text-primary"><?= htmlspecialchars($log['servidor'] ?? '—') ?></td>
                                    <td><span class="<?= $levelClass ?>"><?= htmlspecialchars(ucfirst($sev)) ?></span></td>
                                    <td class="text-light"><?= htmlspecialchars($log['mensagem']) ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($log['status'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('filterServer')?.addEventListener('change', (e) => {
        const value = e.target.value;
        document.querySelectorAll('.log-row').forEach(row => {
            const server = row.getAttribute('data-server') || '';
            row.style.display = !value || server === value ? '' : 'none';
        });
    });
</script>
