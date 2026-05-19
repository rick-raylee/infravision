<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-laptop me-2 text-primary"></i> Computadores & Notebooks Ativos</h1>
            <div class="badge bg-primary p-2"><i class="fa-solid fa-desktop me-1"></i> Inventário Automático</div>
        </div>
        <p class="text-secondary">Lista de estações de trabalho monitoradas ativas, fichas técnicas detalhadas e controle de periféricos.</p>
    </div>
</div>

<div class="row g-4">
    <?php if (empty($computadores)): ?>
        <div class="col-12">
            <div class="noc-card p-5 text-center text-secondary">
                <i class="fa-solid fa-network-wired fa-3x mb-3 text-muted"></i>
                <h4>Nenhum Computador Ativo</h4>
                <p class="mb-0">Execute o agente PowerShell em computadores ou notebooks para registrá-los e ver a ficha técnica em tempo real.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($computadores as $c): 
            $cpu = $c['cpu'] !== null ? round($c['cpu']) : 0;
            $ram = $c['ram'] !== null ? round($c['ram']) : 0;
        ?>
            <div class="col-12 col-lg-6">
                <div class="noc-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-start border-bottom border-secondary border-opacity-10 pb-3 mb-3">
                            <div>
                                <h4 class="mb-0 text-light"><i class="fa-solid fa-laptop me-2 text-primary"></i><?= htmlspecialchars($c['nome']) ?></h4>
                                <span class="badge border border-info text-info small mt-1"><code class="text-info"><?= htmlspecialchars($c['ip']) ?></code></span>
                            </div>
                            <div class="text-end">
                                <span class="badge <?= $c['status'] === 'online' ? 'bg-success' : 'bg-danger' ?> px-3 py-2 rounded-pill">
                                    <?= strtoupper($c['status']) ?>
                                </span>
                                <div class="text-secondary small mt-1">Check: <?= $c['ultimo_check'] ? date('H:i:s d/m', strtotime($c['ultimo_check'])) : 'Nunca' ?></div>
                            </div>
                        </div>

                        <!-- Ficha Técnica -->
                        <div class="mb-4">
                            <h6 class="text-primary text-uppercase mb-3 small fw-bold tracking-wider"><i class="fa-solid fa-circle-info me-1"></i> Ficha Técnica</h6>
                            <table class="table table-sm table-borderless text-light mb-0" style="font-size: 0.9rem;">
                                <tbody>
                                    <tr>
                                        <td class="text-secondary ps-0" style="width: 140px;"><i class="fa-solid fa-user me-2"></i>Usuário Logado:</td>
                                        <td><strong><?= htmlspecialchars($c['usuario_logado'] ?? 'Desconhecido') ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary ps-0"><i class="fa-solid fa-window-restore me-2"></i>Sistema:</td>
                                        <td><?= htmlspecialchars($c['sistema_operacional'] ?? 'Desconhecido') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary ps-0"><i class="fa-solid fa-microchip me-2"></i>Processador:</td>
                                        <td><?= htmlspecialchars($c['processador'] ?? 'Desconhecido') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary ps-0"><i class="fa-solid fa-industry me-2"></i>Fabricante / Mod:</td>
                                        <td><?= htmlspecialchars($c['fabricante'] ?? '') ?> - <?= htmlspecialchars($c['modelo'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary ps-0"><i class="fa-solid fa-barcode me-2"></i>Nº de Série:</td>
                                        <td><code class="text-warning"><?= htmlspecialchars($c['numero_serie'] ?? 'Desconhecido') ?></code></td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary ps-0"><i class="fa-solid fa-gauge-high me-2"></i>Uso Recente:</td>
                                        <td>
                                            <span class="badge bg-secondary me-1">CPU: <?= $cpu ?>%</span>
                                            <span class="badge bg-secondary">RAM: <?= $ram ?>%</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Controle Manual de Periféricos (Mouse e Teclado) -->
                    <div class="border-top border-secondary border-opacity-10 pt-3 mt-auto">
                        <h6 class="text-warning text-uppercase mb-3 small fw-bold tracking-wider"><i class="fa-solid fa-keyboard me-1"></i> Controle de Periféricos (Troca)</h6>
                        
                        <div class="row g-3">
                            <!-- Troca de Mouse -->
                            <div class="col-6">
                                <div class="p-3 rounded" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-secondary"><i class="fa-solid fa-mouse me-1"></i> Mouse:</span>
                                    </div>
                                    <div class="mb-2">
                                        <?php if ($c['mouse_trocado_em']): ?>
                                            <span class="badge bg-success">Trocado em <?= date('d/m/Y', strtotime($c['mouse_trocado_em'])) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Não registrado</span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Form para Registrar Troca -->
                                    <form action="<?= $base_path ?>/computer/update-peripherals" method="POST" class="d-flex gap-1 align-items-center">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="tipo_periferico" value="mouse">
                                        <input type="date" name="data" class="form-control form-control-sm bg-dark text-white border-secondary" required style="font-size: 0.8rem; padding: 0.1rem 0.3rem;">
                                        <button type="submit" class="btn btn-outline-warning btn-sm py-0 px-2" title="Registrar Troca" style="font-size: 0.8rem;"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                </div>
                            </div>

                            <!-- Troca de Teclado -->
                            <div class="col-6">
                                <div class="p-3 rounded" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-secondary"><i class="fa-solid fa-keyboard me-1"></i> Teclado:</span>
                                    </div>
                                    <div class="mb-2">
                                        <?php if ($c['teclado_trocado_em']): ?>
                                            <span class="badge bg-success">Trocado em <?= date('d/m/Y', strtotime($c['teclado_trocado_em'])) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Não registrado</span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Form para Registrar Troca -->
                                    <form action="<?= $base_path ?>/computer/update-peripherals" method="POST" class="d-flex gap-1 align-items-center">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="tipo_periferico" value="teclado">
                                        <input type="date" name="data" class="form-control form-control-sm bg-dark text-white border-secondary" required style="font-size: 0.8rem; padding: 0.1rem 0.3rem;">
                                        <button type="submit" class="btn btn-outline-warning btn-sm py-0 px-2" title="Registrar Troca" style="font-size: 0.8rem;"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
