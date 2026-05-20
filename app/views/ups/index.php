<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-battery-three-quarters me-2 text-warning"></i> Monitor de Nobreaks & UPS</h1>
            <div class="badge bg-success p-2"><i class="fa-solid fa-circle pulse-green me-1"></i> Real-time Telemetry</div>
        </div>
        <p class="text-secondary">Acompanhamento de energia, carga das baterias, autonomia estimada e tensões da rede elétrica.</p>
    </div>
</div>

<div class="row g-4">
    <?php if (empty($nobreaks)): ?>
        <div class="col-12">
            <div class="noc-card p-5 text-center text-secondary">
                <i class="fa-solid fa-battery-empty fa-3x mb-3 text-muted"></i>
                <h4>Nenhum Nobreak Cadastrado</h4>
                <p class="mb-0">Cadastre um nobreak em <strong>Dispositivos</strong> (tipo <strong>nobreak</strong>) com o IP do UPS e ative <code>MonitorNobreak: true</code> no <code>agent_config.json</code> da máquina ligada ao UPS. Servidores e notebooks sem UPS não devem enviar dados de bateria.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($nobreaks as $nb): 
            $bateria = $nb['bateria'];
            $tensao = $nb['tensao'];
            $carga = $nb['carga'];
            $autonomia = $nb['autonomia'];
            $autonomiaLabel = '—';
            if ($autonomia !== null) {
                $autonomiaLabel = $autonomia >= 60
                    ? (int)floor($autonomia / 60) . 'h ' . ($autonomia % 60) . 'min'
                    : $autonomia . ' min';
            }

            // Cores do status da bateria
            $batteryColor = 'bg-success';
            $batteryIcon = 'fa-battery-full';
            if ($bateria === null) {
                $batteryColor = 'bg-secondary';
                $batteryIcon = 'fa-battery-empty';
            } elseif ($bateria < 25) {
                $batteryColor = 'bg-danger';
                $batteryIcon = 'fa-battery-empty';
            } elseif ($bateria < 50) {
                $batteryColor = 'bg-warning';
                $batteryIcon = 'fa-battery-quarter';
            } elseif ($bateria < 80) {
                $batteryColor = 'bg-info';
                $batteryIcon = 'fa-battery-half';
            }

            // Cores do status da carga
            $loadColor = 'bg-success';
            if ($carga === null) {
                $loadColor = 'bg-secondary';
            } elseif ($carga > 80) {
                $loadColor = 'bg-danger';
            } elseif ($carga > 50) {
                $loadColor = 'bg-warning';
            }
        ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="noc-card h-100">
                    <div class="noc-card-header d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-10 pb-3">
                        <div>
                            <h5 class="mb-0 text-light"><i class="fa-solid fa-car-battery me-2 text-primary"></i><?= htmlspecialchars($nb['nome']) ?></h5>
                            <small class="text-secondary"><?= htmlspecialchars($nb['ip']) ?></small>
                        </div>
                        <span class="badge <?= $nb['status'] === 'online' ? 'bg-success' : 'bg-danger' ?> rounded-pill">
                            <?= strtoupper($nb['status']) ?>
                        </span>
                    </div>
                    <div class="noc-card-body pt-3">
                        <!-- Nível de Bateria -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-secondary small fw-medium"><i class="fa-solid <?= $batteryIcon ?> me-2 text-success"></i>Bateria</span>
                                <span class="fw-bold"><?= $bateria !== null ? $bateria . '%' : '—' ?></span>
                            </div>
                            <div class="progress" style="height: 10px; background-color: #1e2638;">
                                <div class="progress-bar <?= $batteryColor ?>" role="progressbar" style="width: <?= $bateria ?? 0 ?>%"></div>
                            </div>
                        </div>

                        <!-- Carga do Inversor -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-secondary small fw-medium"><i class="fa-solid fa-bolt-lightning me-2 text-warning"></i>Carga Consumida</span>
                                <span class="fw-bold"><?= $carga !== null ? $carga . '%' : '—' ?></span>
                            </div>
                            <div class="progress" style="height: 10px; background-color: #1e2638;">
                                <div class="progress-bar <?= $loadColor ?>" role="progressbar" style="width: <?= $carga ?? 0 ?>%"></div>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-3 rounded" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                    <div class="text-secondary small mb-1">Tensão Entrada</div>
                                    <h4 class="mb-0 fw-bold text-info"><?= $tensao !== null ? $tensao . 'V' : '—' ?></h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                    <div class="text-secondary small mb-1">Autonomia Est.</div>
                                    <h4 class="mb-0 fw-bold text-warning" style="font-size: 1.1rem;"><?= htmlspecialchars($autonomiaLabel) ?></h4>
                                    <?php if ($autonomia === null): ?>
                                        <div class="text-secondary" style="font-size: 0.7rem;">Tomada AC / WMI sem estimativa</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="noc-card-footer border-top border-secondary border-opacity-10 pt-3 text-secondary small d-flex justify-content-between">
                        <span>Último Check:</span>
                        <span><?= $nb['ultimo_check'] ? date('H:i:s d/m/Y', strtotime($nb['ultimo_check'])) : 'Nunca' ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.pulse-green {
    animation: pulse-green 1.5s infinite;
}
@keyframes pulse-green {
    0% { opacity: 1; }
    50% { opacity: 0.3; }
    100% { opacity: 1; }
}
.noc-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 1.5rem;
}
</style>
