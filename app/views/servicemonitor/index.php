<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fa-solid fa-globe me-2 text-primary"></i> Monitor de Serviços e URLs</h1>
            <button class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Nova URL</button>
        </div>
        <p class="text-secondary">Acompanhamento de disponibilidade de sites, APIs e serviços específicos como Exchange.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Monitoramento de URLs -->
    <div class="col-12 col-lg-7">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-link me-2 text-info"></i> URLs e Endpoints Externos</span>
            </div>
            <div class="noc-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th>URL / Endpoint</th>
                                <th>Status</th>
                                <th>Tempo Resp.</th>
                                <th>Uptime (7d)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Site Corporativo</td>
                                <td><a href="#" class="text-primary">https://empresa.com.br</a></td>
                                <td><span class="badge bg-success">200 OK</span></td>
                                <td>145ms</td>
                                <td>99.9%</td>
                            </tr>
                            <tr>
                                <td>API de Clientes</td>
                                <td><code>https://api.empresa.com/v1</code></td>
                                <td><span class="badge bg-success">200 OK</span></td>
                                <td>82ms</td>
                                <td>100%</td>
                            </tr>
                            <tr>
                                <td>Webmail (OWA)</td>
                                <td><a href="#" class="text-primary">https://mail.empresa.com/owa</a></td>
                                <td><span class="badge bg-warning text-dark">503 Busy</span></td>
                                <td>2500ms</td>
                                <td>98.5%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitoramento Microsoft Exchange -->
    <div class="col-12 col-lg-5">
        <div class="noc-card">
            <div class="noc-card-header">
                <span><i class="fa-solid fa-envelope-open-text me-2 text-primary"></i> Microsoft Exchange Health</span>
            </div>
            <div class="noc-card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Fila de Mensagens (Submission)</span>
                        <span class="text-warning">452 msgs</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: 75%"></div>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 border border-secondary rounded text-center">
                            <div class="small text-secondary">Mailbox DB</div>
                            <div class="h5 mb-0 text-success">Mounted</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border border-secondary rounded text-center">
                            <div class="small text-secondary">Transport Svc</div>
                            <div class="h5 mb-0 text-success">Running</div>
                        </div>
                    </div>
                </div>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        Active Sync Performance
                        <span class="badge bg-success">Healthy</span>
                    </li>
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        Outlook Anywhere (RPC)
                        <span class="badge bg-success">Healthy</span>
                    </li>
                    <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        DAG Replication State
                        <span class="badge bg-danger">Out of Sync</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
