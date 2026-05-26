<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-laptop me-2 text-primary"></i> Computadores &amp; Notebooks Ativos</h1>
            <div class="badge bg-primary p-2"><i class="fa-solid fa-desktop me-1"></i> Inventário Automático</div>
        </div>
        <p class="text-secondary">Lista de estações de trabalho monitoradas ativas, fichas técnicas detalhadas e controle de periféricos.</p>
    </div>
</div>

<style>
/* ============================================================
   COMPUTER CARDS — FICHA TÉCNICA REDESIGN
   ============================================================ */
.computer-card {
    background: linear-gradient(145deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    transition: box-shadow 0.25s ease, transform 0.25s ease, border-color 0.25s ease;
    overflow: hidden;
}
.computer-card:hover {
    box-shadow: 0 8px 40px rgba(0,120,255,0.12);
    border-color: rgba(0,120,255,0.25);
    transform: translateY(-2px);
}

/* ---- Header ---- */
.computer-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.2rem 1.2rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.computer-card__hostname {
    font-size: 1.05rem;
    font-weight: 700;
    color: #f0f4ff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 220px;
}
.computer-card__ip {
    font-size: 0.75rem;
    color: #60a5fa;
    font-family: monospace;
    margin-top: 2px;
}

/* ---- Ficha Técnica ---- */
.ficha-section {
    padding: 1rem 1.2rem;
}
.ficha-section__title {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #60a5fa;
    margin-bottom: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.ficha-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.ficha-row:last-child { border-bottom: none; }
.ficha-row__icon {
    color: rgba(255,255,255,0.3);
    font-size: 0.8rem;
    width: 18px;
    text-align: center;
    flex-shrink: 0;
    padding-top: 2px;
}
.ficha-row__label {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.45);
    white-space: nowrap;
    flex-shrink: 0;
    min-width: 105px;
    line-height: 1.4;
}
.ficha-row__value {
    font-size: 0.8rem;
    color: #e2e8f0;
    word-break: break-word;
    line-height: 1.4;
}
.ficha-row__value code {
    font-size: 0.78rem;
    color: #fbbf24;
    background: rgba(251,191,36,0.08);
    padding: 1px 6px;
    border-radius: 4px;
    font-family: monospace;
}

/* ---- CPU / RAM badges ---- */
.usage-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 20px;
    background: rgba(255,255,255,0.07);
    color: #cbd5e1;
    border: 1px solid rgba(255,255,255,0.08);
    margin-right: 4px;
    margin-top: 2px;
}
.usage-badge--cpu { border-color: rgba(99,102,241,0.4); color: #a5b4fc; background: rgba(99,102,241,0.1); }
.usage-badge--ram { border-color: rgba(16,185,129,0.4); color: #6ee7b7; background: rgba(16,185,129,0.1); }

/* ---- Periféricos ---- */
.perifericos-section {
    padding: 0.85rem 1.2rem 1.1rem;
    border-top: 1px solid rgba(255,255,255,0.06);
    background: rgba(0,0,0,0.1);
}
.perifericos-section__title {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #fbbf24;
    margin-bottom: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.periferico-block {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 10px;
    padding: 0.7rem 0.85rem;
    height: 100%;
}
.periferico-block__label {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.45);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.periferico-block__status {
    margin-bottom: 8px;
}
.badge-trocado {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: rgba(16,185,129,0.15);
    color: #34d399;
    border: 1px solid rgba(16,185,129,0.3);
}
.badge-nao-reg {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: rgba(255,255,255,0.05);
    color: rgba(255,255,255,0.3);
    border: 1px solid rgba(255,255,255,0.07);
}
.periferico-form {
    display: flex;
    gap: 5px;
    align-items: center;
    margin-top: 4px;
}
.periferico-form input[type="date"] {
    flex: 1;
    min-width: 0;
    font-size: 0.72rem;
    padding: 4px 7px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: #e2e8f0;
    border-radius: 7px;
    outline: none;
}
.periferico-form input[type="date"]:focus {
    border-color: rgba(251,191,36,0.5);
    background: rgba(251,191,36,0.05);
}
.btn-periferico {
    flex-shrink: 0;
    background: rgba(251,191,36,0.15);
    border: 1px solid rgba(251,191,36,0.3);
    color: #fbbf24;
    border-radius: 7px;
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    transition: background 0.2s;
    cursor: pointer;
}
.btn-periferico:hover {
    background: rgba(251,191,36,0.3);
    color: #fff;
}
</style>

<div class="row g-4">
    <?php if (empty($computadores)): ?>
        <div class="col-12">
            <div class="noc-card p-5 text-center text-secondary">
                <i class="fa-solid fa-network-wired fa-3x mb-3 text-muted"></i>
                <h4>Nenhum Computador Ativo</h4>
                <p class="mb-0">Execute o agente em computadores ou notebooks para registrá-los e ver a ficha técnica em tempo real.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($computadores as $c):
            $cpu = $c['cpu'] !== null ? round($c['cpu']) : 0;
            $ram = $c['ram'] !== null ? round($c['ram']) : 0;
            $statusClass = $c['status'] === 'online' ? 'bg-success' : ($c['status'] === 'alerta' ? 'bg-warning' : 'bg-danger');
        ?>
            <div class="col-12 col-xl-6">
                <div class="computer-card h-100 d-flex flex-column">

                    <!-- ======= HEADER ======= -->
                    <div class="computer-card__header">
                        <div style="min-width:0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-laptop text-primary" style="font-size:1rem;"></i>
                                <span class="computer-card__hostname" title="<?= htmlspecialchars($c['nome']) ?>">
                                    <?= htmlspecialchars($c['nome']) ?>
                                </span>
                            </div>
                            <div class="computer-card__ip">
                                <i class="fa-solid fa-network-wired me-1" style="font-size:0.65rem;"></i>
                                <?= htmlspecialchars($c['ip']) ?>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0 ms-2">
                            <span class="badge <?= $statusClass ?> px-3 py-1 rounded-pill" style="font-size:0.7rem;">
                                <?= strtoupper($c['status']) ?>
                            </span>
                            <div class="text-secondary mt-1" style="font-size:0.68rem;">
                                <i class="fa-solid fa-clock me-1"></i>
                                <?= $c['ultimo_check'] ? date('H:i d/m', strtotime($c['ultimo_check'])) : 'Nunca' ?>
                            </div>
                        </div>
                    </div>

                    <!-- ======= FICHA TÉCNICA ======= -->
                    <div class="ficha-section flex-grow-1">
                        <div class="ficha-section__title">
                            <i class="fa-solid fa-circle-info"></i> Ficha Técnica
                        </div>

                        <div class="ficha-row">
                            <div class="ficha-row__icon"><i class="fa-regular fa-calendar-check"></i></div>
                            <div class="ficha-row__label">1º Registro (Entrega)</div>
                            <div class="ficha-row__value">
                                <?= !empty($c['criado_em']) ? date('d/m/Y \à\s H:i', strtotime($c['criado_em'])) : 'Desconhecido' ?>
                            </div>
                        </div>

                        <div class="ficha-row">
                            <div class="ficha-row__icon"><i class="fa-solid fa-user"></i></div>
                            <div class="ficha-row__label">Usuário Logado</div>
                            <div class="ficha-row__value">
                                <strong><?= htmlspecialchars($c['usuario_logado'] ?? 'Desconhecido') ?></strong>
                            </div>
                        </div>

                        <div class="ficha-row">
                            <div class="ficha-row__icon"><i class="fa-brands fa-windows"></i></div>
                            <div class="ficha-row__label">Sistema</div>
                            <div class="ficha-row__value"><?= htmlspecialchars($c['sistema_operacional'] ?? 'Desconhecido') ?></div>
                        </div>

                        <div class="ficha-row">
                            <div class="ficha-row__icon"><i class="fa-solid fa-microchip"></i></div>
                            <div class="ficha-row__label">Processador</div>
                            <div class="ficha-row__value"><?= htmlspecialchars($c['processador'] ?? 'Desconhecido') ?></div>
                        </div>

                        <div class="ficha-row">
                            <div class="ficha-row__icon"><i class="fa-solid fa-building"></i></div>
                            <div class="ficha-row__label">Fabricante / Modelo</div>
                            <div class="ficha-row__value">
                                <?= htmlspecialchars(trim(($c['fabricante'] ?? '') . ' — ' . ($c['modelo'] ?? ''), ' —') ?: 'Desconhecido') ?>
                            </div>
                        </div>

                        <div class="ficha-row">
                            <div class="ficha-row__icon"><i class="fa-solid fa-barcode"></i></div>
                            <div class="ficha-row__label">Nº de Série</div>
                            <div class="ficha-row__value">
                                <code><?= htmlspecialchars($c['numero_serie'] ?? 'Desconhecido') ?></code>
                            </div>
                        </div>

                        <div class="ficha-row">
                            <div class="ficha-row__icon"><i class="fa-solid fa-gauge-high"></i></div>
                            <div class="ficha-row__label">Uso Recente</div>
                            <div class="ficha-row__value">
                                <span class="usage-badge usage-badge--cpu">
                                    <i class="fa-solid fa-microchip" style="font-size:0.65rem;"></i> CPU <?= $cpu ?>%
                                </span>
                                <span class="usage-badge usage-badge--ram">
                                    <i class="fa-solid fa-memory" style="font-size:0.65rem;"></i> RAM <?= $ram ?> MB
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ======= PERIFÉRICOS ======= -->
                    <div class="perifericos-section">
                        <div class="perifericos-section__title">
                            <i class="fa-solid fa-keyboard"></i> Controle de Periféricos (Troca)
                        </div>
                        <div class="row g-2">

                            <!-- Mouse -->
                            <div class="col-6">
                                <div class="periferico-block">
                                    <div class="periferico-block__label">
                                        <i class="fa-solid fa-mouse"></i> Mouse
                                    </div>
                                    <div class="periferico-block__status">
                                        <?php if ($c['mouse_trocado_em']): ?>
                                            <span class="badge-trocado">
                                                <i class="fa-solid fa-check me-1" style="font-size:0.6rem;"></i>
                                                <?= date('d/m/Y', strtotime($c['mouse_trocado_em'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-nao-reg">Não registrado</span>
                                        <?php endif; ?>
                                    </div>
                                    <form action="<?= $base_path ?>/computer/update-peripherals" method="POST" class="periferico-form">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="tipo_periferico" value="mouse">
                                        <input type="date" name="data" required>
                                        <button type="submit" class="btn-periferico" title="Registrar Troca">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Teclado -->
                            <div class="col-6">
                                <div class="periferico-block">
                                    <div class="periferico-block__label">
                                        <i class="fa-solid fa-keyboard"></i> Teclado
                                    </div>
                                    <div class="periferico-block__status">
                                        <?php if ($c['teclado_trocado_em']): ?>
                                            <span class="badge-trocado">
                                                <i class="fa-solid fa-check me-1" style="font-size:0.6rem;"></i>
                                                <?= date('d/m/Y', strtotime($c['teclado_trocado_em'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-nao-reg">Não registrado</span>
                                        <?php endif; ?>
                                    </div>
                                    <form action="<?= $base_path ?>/computer/update-peripherals" method="POST" class="periferico-form">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="tipo_periferico" value="teclado">
                                        <input type="date" name="data" required>
                                        <button type="submit" class="btn-periferico" title="Registrar Troca">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
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
