<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1><i class="fa-solid fa-terminal me-2 text-primary"></i> Central de Logs e Eventos</h1>
            <div class="d-flex gap-2">
                <select class="form-select bg-dark border-secondary text-light w-auto" id="filterServer">
                    <option value="">Todos os dispositivos/origens</option>
                    <?php foreach ($servidores as $srv): ?>
                        <option value="<?= htmlspecialchars($srv) ?>"><?= htmlspecialchars($srv) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-select bg-dark border-secondary text-light w-auto" id="filterCategory">
                    <option value="">Todas as categorias</option>
                    <option value="Sistema Operacional">Sistema Operacional</option>
                    <option value="Aplicações">Aplicações</option>
                    <option value="Rede/Segurança">Rede/Segurança</option>
                    <option value="Servidores Web">Servidores Web</option>
                </select>
            </div>
        </div>
        <p class="text-secondary">Alertas e registros de auditoria reais do banco de dados.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="noc-card bg-black">
            <div class="noc-card-header border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                <span class="text-success fw-mono"><i class="fa-solid fa-bolt me-2"></i> Eventos registrados</span>
                <span class="badge bg-secondary rounded-pill"><?= count($eventos) ?> eventos</span>
            </div>
            <div class="noc-card-body p-0" style="height: 550px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 0.85rem;">
                <table class="table table-dark table-hover table-sm mb-0">
                    <thead>
                        <tr class="text-secondary border-secondary border-opacity-25">
                            <th style="width: 180px;">Timestamp</th>
                            <th style="width: 150px;">Origem</th>
                            <th style="width: 180px;">Categoria</th>
                            <th style="width: 110px;">Severidade</th>
                            <th>Mensagem</th>
                            <th style="width: 110px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="log-stream">
                        <?php if (empty($eventos)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    Nenhum evento registrado. Alertas e ações do sistema aparecerão aqui.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($eventos as $log): ?>
                                <?php
                                    $sev = $log['severidade'] ?? 'info';
                                    $levelClass = in_array($sev, ['erro', 'critico'], true) ? 'text-danger'
                                        : ($sev === 'aviso' ? 'text-warning' : 'text-info');
                                    
                                    $cat = $log['categoria'] ?? 'Sistema Operacional';
                                    $badgeClass = 'bg-primary';
                                    $catIcon = 'fa-desktop';
                                    
                                    if ($cat === 'Rede/Segurança') {
                                        $badgeClass = 'bg-danger text-light';
                                        $catIcon = 'fa-shield-halved';
                                    } elseif ($cat === 'Aplicações') {
                                        $badgeClass = 'bg-warning text-dark';
                                        $catIcon = 'fa-cubes';
                                    } elseif ($cat === 'Servidores Web') {
                                        $badgeClass = 'bg-info text-dark';
                                        $catIcon = 'fa-globe';
                                    }
                                ?>
                                <tr class="border-secondary border-opacity-10 log-row" 
                                    data-server="<?= htmlspecialchars($log['servidor'] ?? '') ?>"
                                    data-category="<?= htmlspecialchars($cat) ?>">
                                    <td class="text-secondary"><?= date('Y-m-d H:i:s', strtotime($log['criado_em'])) ?></td>
                                    <td class="text-primary"><?= htmlspecialchars($log['servidor'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?> fw-normal" style="font-size: 0.75rem;">
                                            <i class="fa-solid <?= $catIcon ?> me-1"></i> <?= htmlspecialchars($cat) ?>
                                        </span>
                                    </td>
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
    document.addEventListener('DOMContentLoaded', () => {
        const filterServer = document.getElementById('filterServer');
        const filterCategory = document.getElementById('filterCategory');

        function applyFilters() {
            const serverVal = filterServer ? filterServer.value : '';
            const categoryVal = filterCategory ? filterCategory.value : '';

            document.querySelectorAll('.log-row').forEach(row => {
                const server = row.getAttribute('data-server') || '';
                const category = row.getAttribute('data-category') || '';

                const serverMatch = !serverVal || server === serverVal;
                const categoryMatch = !categoryVal || category === categoryVal;

                row.style.display = serverMatch && categoryMatch ? '' : 'none';
            });
        }

        filterServer?.addEventListener('change', applyFilters);
        filterCategory?.addEventListener('change', applyFilters);
    });
</script>
