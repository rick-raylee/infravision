param (
    [string]$ServerUrl,
    [string]$Token,
    [switch]$Install,
    [switch]$Uninstall,
    [switch]$Reset
)

# ============================
# InfraVision Agent
# ============================
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if ([string]::IsNullOrEmpty($ScriptDir)) {
    $ScriptDir = Get-Location
}
$ConfigPath = Join-Path $ScriptDir "agent_config.json"

# Redefinir configuração
if ($Reset) {
    if (Test-Path $ConfigPath) {
        Remove-Item $ConfigPath -Force
        Write-Host "Arquivo de configuração 'agent_config.json' removido." -ForegroundColor Yellow
    }
}

# Instalar no Agendador de Tarefas do Windows (Inicialização automática)
if ($Install) {
    $isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    if (-not $isAdmin) {
        Write-Host "ERRO: Você precisa executar o PowerShell como Administrador para instalar o agente como serviço!" -ForegroundColor Red
        Exit
    }
    
    $ScriptPath = $MyInvocation.MyCommand.Path
    $TaskName = "InfraVisionAgent"
    $Command = "powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$ScriptPath`""
    
    # Criar tarefa usando schtasks
    & schtasks.exe /Create /TN $TaskName /TR $Command /SC ONSTART /RU "SYSTEM" /F 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "==================================================" -ForegroundColor Green
        Write-Host "   AGENTE INSTALADO COM SUCESSO NO SISTEMA!" -ForegroundColor Green
        Write-Host "==================================================" -ForegroundColor Green
        Write-Host "O agente InfraVision rodará de forma oculta em segundo plano" -ForegroundColor White
        Write-Host "toda vez que o Windows iniciar (como SYSTEM)." -ForegroundColor White
        Write-Host ""
        Write-Host "Para iniciar o serviço agora sem precisar reiniciar a máquina, rode:" -ForegroundColor Cyan
        Write-Host "  schtasks.exe /Run /TN `"$TaskName`"" -ForegroundColor Cyan
    } else {
        Write-Host "Erro ao cadastrar agente no Agendador de Tarefas do Windows." -ForegroundColor Red
    }
    Exit
}

# Desinstalar do Agendador de Tarefas
if ($Uninstall) {
    $isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    if (-not $isAdmin) {
        Write-Host "ERRO: Você precisa executar o PowerShell como Administrador para desinstalar o agente!" -ForegroundColor Red
        Exit
    }
    & schtasks.exe /Delete /TN "InfraVisionAgent" /F 2>&1 | Out-Null
    Write-Host "Agente desinstalado com sucesso do Agendador de Tarefas." -ForegroundColor Yellow
    Exit
}

# Configuração via Parâmetros de Linha de Comando
if ($ServerUrl) {
    $CleanUrl = $ServerUrl
    if (-not ($CleanUrl.StartsWith("http://") -or $CleanUrl.StartsWith("https://"))) {
        $CleanUrl = "http://" + $CleanUrl
    }
    if (-not $CleanUrl.EndsWith("receber_agente.php")) {
        $CleanUrl = $CleanUrl.TrimEnd("/") + "/api/receber_agente.php"
    }
    $CleanToken = if ($Token) { $Token } else { "QUALQUER_TOKEN" }
    
    $Config = @{
        ApiUrl = $CleanUrl
        AuthToken = $CleanToken
        Intervalo = 60
    }
    $Config | ConvertTo-Json | Out-File $ConfigPath -Encoding UTF8 -Force
    Write-Host "Configuração salva com sucesso por parâmetro em: $ConfigPath" -ForegroundColor Green
}

# Configuração Interativa (Caso não exista arquivo de config)
if (-not (Test-Path $ConfigPath)) {
    Write-Host "=============================================" -ForegroundColor Cyan
    Write-Host "     BEM-VINDO AO INFRAVISION AGENT" -ForegroundColor Green
    Write-Host "=============================================" -ForegroundColor Cyan
    Write-Host "Nenhuma configuração encontrada. Vamos configurar agora." -ForegroundColor White
    Write-Host ""
    
    $InputUrl = Read-Host "Digite a URL ou IP do InfraVision (Ex: localhost/infravision ou seu-nock.onrender.com)"
    if ([string]::IsNullOrEmpty($InputUrl)) {
        $InputUrl = "localhost/infravision"
    }
    
    if (-not ($InputUrl.StartsWith("http://") -or $InputUrl.StartsWith("https://"))) {
        $InputUrl = "http://" + $InputUrl
    }
    if (-not $InputUrl.EndsWith("receber_agente.php")) {
        $InputUrl = $InputUrl.TrimEnd("/") + "/api/receber_agente.php"
    }
    
    $InputToken = Read-Host "Digite o token de autenticação [Deixe em branco para o padrão]"
    if ([string]::IsNullOrEmpty($InputToken)) { 
        $InputToken = "QUALQUER_TOKEN" 
    }
    
    $Config = @{
        ApiUrl = $InputUrl
        AuthToken = $InputToken
        Intervalo = 60
    }
    $Config | ConvertTo-Json | Out-File $ConfigPath -Encoding UTF8 -Force
    Write-Host ""
    Write-Host "Configuração salva em: $ConfigPath" -ForegroundColor Green
    Write-Host "DICA: Para instalar como tarefa de inicialização em segundo plano, rode:" -ForegroundColor Gray
    Write-Host "      powershell -ExecutionPolicy Bypass -File windows_agent.ps1 -Install" -ForegroundColor Gray
    Write-Host "=============================================" -ForegroundColor Cyan
    Write-Host ""
}

# Carregar Arquivo de Configuração
$ConfigContent = Get-Content $ConfigPath -Raw | ConvertFrom-Json
$ApiUrl = $ConfigContent.ApiUrl
$AuthToken = $ConfigContent.AuthToken
$IntervaloSegundos = if ($ConfigContent.Intervalo) { $ConfigContent.Intervalo } else { 60 }

Write-Host "InfraVision Agent iniciado com sucesso!" -ForegroundColor Green
Write-Host "URL de Destino: $ApiUrl" -ForegroundColor Cyan
Write-Host "Intervalo: $IntervaloSegundos segundos" -ForegroundColor Cyan
Write-Host "Pressione Ctrl+C para encerrar." -ForegroundColor Yellow
Write-Host ""

while ($true) {

    try {

        # =========================
        # HOSTNAME
        # =========================
        $Hostname = $env:COMPUTERNAME

        # =========================
        # IP (SEM INTERFACE FIXA)
        # =========================
        $IpAddress = Get-NetIPAddress `
            -AddressFamily IPv4 `
            -ErrorAction SilentlyContinue |
            Where-Object {
                $_.IPAddress -ne "127.0.0.1" -and
                $_.PrefixOrigin -ne "WellKnown"
            } |
            Select-Object -First 1 -ExpandProperty IPAddress

        if (-not $IpAddress) {
            $IpAddress = (hostname)
        }

        # =========================
        # CPU
        # =========================
        $CpuLoad = (Get-CimInstance Win32_Processor |
            Measure-Object -Property LoadPercentage -Average).Average

        # =========================
        # RAM
        # =========================
        $OS = Get-CimInstance Win32_OperatingSystem

        $RamTotalMB = [math]::Round($OS.TotalVisibleMemorySize / 1024, 0)
        $RamLivreMB = [math]::Round($OS.FreePhysicalMemory / 1024, 0)

        # =========================
        # DISCOS
        # =========================
        $DiscosArray = @()

        Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" | ForEach-Object {
            $DiscosArray += @{
                letra = $_.DeviceID
                tamanho_gb = [math]::Round($_.Size / 1GB, 2)
                livre_gb = [math]::Round($_.FreeSpace / 1GB, 2)
            }
        }

        # =========================
        # SERVIÇOS
        # =========================
        $ServicosMonitorados = @("Spooler", "LanmanServer")
        $ServicosArray = @()

        foreach ($s in $ServicosMonitorados) {
            $srv = Get-Service $s -ErrorAction SilentlyContinue
            if ($srv) {
                $ServicosArray += @{
                    nome = $srv.Name
                    status = $srv.Status.ToString()
                }
            }
        }

        # =========================
        # CONEXÕES ATIVAS (REAIS)
        # =========================
        $ConexoesArray = @()
        try {
            $TCPConnections = Get-NetTCPConnection -State Established -ErrorAction SilentlyContinue | 
                Where-Object { $_.RemoteAddress -ne "127.0.0.1" -and $_.RemoteAddress -ne "::1" } |
                Select-Object -First 5
                
            foreach ($conn in $TCPConnections) {
                $Port = $conn.RemotePort
                $Service = "Porta $Port"
                if ($Port -eq 80) { $Service = "HTTP (80)" }
                elseif ($Port -eq 443) { $Service = "HTTPS (443)" }
                elseif ($Port -eq 445) { $Service = "SMB (445)" }
                elseif ($Port -eq 3306) { $Service = "MySQL (3306)" }
                elseif ($Port -eq 22) { $Service = "SSH (22)" }
                elseif ($Port -eq 3389) { $Service = "RDP (3389)" }
                
                $ConexoesArray += @{
                    origem = $Hostname
                    ip_origem = $IpAddress
                    destino = $conn.RemoteAddress
                    servico = $Service
                    latencia = 0
                    carga = 0
                }
            }
        } catch {}

        # =========================
        # BATERIA / UPS (WMI)
        # =========================
        $Battery = Get-CimInstance Win32_Battery -ErrorAction SilentlyContinue
        $BatteryPercent = $null
        $BatteryRuntime = $null
        $BatteryStatus = $null
        
        if ($Battery) {
            $BatteryPercent = $Battery.EstimatedChargeRemaining
            $BatteryRuntime = $Battery.EstimatedRunTime
            $BatteryStatus = $Battery.BatteryStatus
            $BatteryName = $Battery.Name
        }

        # =========================
        # FICHA TÉCNICA (HARDWARE WMI)
        # =========================
        $CompSystem = Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue
        $Bios = Get-CimInstance Win32_Bios -ErrorAction SilentlyContinue
        $OSInfo = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
        $Processor = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1

        $Fabricante = if ($CompSystem) { $CompSystem.Manufacturer } else { "Desconhecido" }
        $Modelo = if ($CompSystem) { $CompSystem.Model } else { "Desconhecido" }
        $SerialNumber = if ($Bios) { $Bios.SerialNumber } else { "Desconhecido" }
        $SO = if ($OSInfo) { $OSInfo.Caption + " (" + $OSInfo.OSArchitecture + ")" } else { "Desconhecido" }
        $CPUName = if ($Processor) { $Processor.Name } else { "Desconhecido" }
        
        # Obter usuário logado atual de forma robusta (explorer.exe owner)
        $LoggedUser = $null
        try {
            $ExplorerProcesses = Get-CimInstance Win32_Process -Filter "name='explorer.exe'" -ErrorAction SilentlyContinue
            if ($ExplorerProcesses) {
                foreach ($proc in $ExplorerProcesses) {
                    $OwnerInfo = Invoke-CimMethod -InputObject $proc -MethodName GetOwner -ErrorAction SilentlyContinue
                    if ($OwnerInfo -and $OwnerInfo.User) {
                        if ($OwnerInfo.Domain) {
                            $LoggedUser = $OwnerInfo.Domain + "\" + $OwnerInfo.User
                        } else {
                            $LoggedUser = $OwnerInfo.User
                        }
                        break
                    }
                }
            }
        } catch {}

        if ([string]::IsNullOrEmpty($LoggedUser)) {
            $LoggedUser = if ($CompSystem) { $CompSystem.UserName } else { "" }
        }
        if ([string]::IsNullOrEmpty($LoggedUser)) {
            $LoggedUser = [System.Environment]::UserDomainName + "\" + [System.Environment]::UserName
        }

        # Classificar tipo (ProductType 1 = Workstation/Notebook/PC)
        $TipoDispositivo = "servidor_windows"
        if ($OSInfo -and $OSInfo.ProductType -eq 1) {
            $TipoDispositivo = "computador"
        }

        # =========================
        # PAYLOAD JSON
        # =========================
        $PayloadHash = @{
            hostname = $Hostname
            ip = $IpAddress
            cpu_load = [math]::Round($CpuLoad, 2)
            ram_total_mb = $RamTotalMB
            ram_livre_mb = $RamLivreMB
            discos = $DiscosArray
            servicos = $ServicosArray
            conexoes = $ConexoesArray
            tipo = $TipoDispositivo
            usuario_logado = $LoggedUser
            fabricante = $Fabricante
            modelo = $Modelo
            numero_serie = $SerialNumber
            sistema_operacional = $SO
            processador = $CPUName
            timestamp = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
        }

        if ($null -ne $BatteryPercent) {
            $NbNome = if ($BatteryName -and $BatteryName -notmatch '^\d+$') { $BatteryName } else { "Nobreak USB - $Hostname" }
            $NbAutonomia = $null
            if ($null -ne $BatteryRuntime -and $BatteryRuntime -gt 0 -and $BatteryRuntime -lt 65535 -and $BatteryRuntime -lt 71582700 -and $BatteryRuntime -le 10080) {
                $NbAutonomia = $BatteryRuntime
            }
            $NbPayload = @{
                nome = $NbNome
                ip = $IpAddress
                bateria = [math]::Min(100, [math]::Max(0, [int]$BatteryPercent))
                status = if ($BatteryStatus -eq 1 -or $BatteryStatus -eq 8 -or $BatteryStatus -eq 9) { "alerta" } else { "online" }
            }
            if ($null -ne $NbAutonomia) { $NbPayload["autonomia"] = $NbAutonomia }
            $PayloadHash["nobreak"] = $NbPayload
        }

        $Payload = $PayloadHash | ConvertTo-Json -Depth 5

        # =========================
        # HEADERS
        # =========================
        $Headers = @{
            "Content-Type" = "application/json"
            "Authorization" = "Bearer $AuthToken"
        }

        # =========================
        # ENVIO (TLS 1.2)
        # =========================
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

        $Response = Invoke-RestMethod `
            -Uri $ApiUrl `
            -Method Post `
            -Body $Payload `
            -Headers $Headers `
            -TimeoutSec 10 `
            -ErrorAction Stop

        Write-Host "OK [$((Get-Date).ToString('HH:mm:ss'))] CPU:$([math]::Round($CpuLoad,1))% RAM:$RamLivreMB MB" -ForegroundColor Cyan

    }
    catch {
        Write-Host "ERRO: $($_.Exception.Message)" -ForegroundColor Red
    }

    Start-Sleep -Seconds $IntervaloSegundos
}
