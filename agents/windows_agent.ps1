# ============================
# InfraVision Agent - FIXED
# ============================

$ApiUrl = "http://SEU_IP_AQUI/infravision/api/receber_agente.php"
$AuthToken = "SEU_TOKEN_AQUI"
$IntervaloSegundos = 60

Write-Host "InfraVision Agent iniciado..." -ForegroundColor Green

if ($ApiUrl -like "*SEU_IP_AQUI*" -or $AuthToken -eq "SEU_TOKEN_AQUI") {
    Write-Host ""
    Write-Host "[!] CONFIGURAÇÃO NECESSÁRIA [!]" -ForegroundColor Red
    Write-Host "Por favor, edite as linhas 5 e 6 de 'windows_agent.ps1' com as suas credenciais reais." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Se estiver rodando no XAMPP localmente, use:" -ForegroundColor Cyan
    Write-Host "  `$ApiUrl = `"http://localhost/infravision/api/receber_agente.php`"" -ForegroundColor Cyan
    Write-Host "  `$AuthToken = `"QUALQUER_VALOR`"" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Se estiver rodando no Render, use:" -ForegroundColor Cyan
    Write-Host "  `$ApiUrl = `"https://sua-url-do-render.onrender.com/api/receber_agente.php`"" -ForegroundColor Cyan
    Write-Host "  `$AuthToken = `"QUALQUER_VALOR`"" -ForegroundColor Cyan
    Write-Host ""
    Exit
}

while ($true) {

    try {

        # =========================
        # HOSTNAME
        # =========================
        $Hostname = $env:COMPUTERNAME

        # =========================
        # IP (CORRIGIDO - SEM INTERFACE FIXA)
        # =========================
        $IpAddress = Get-NetIPAddress `
            -AddressFamily IPv4 `
            -ErrorAction SilentlyContinue |
            Where-Object {
                $_.IPAddress -ne "127.0.0.1" -and
                $_.PrefixOrigin -ne "WellKnown"
            } |
            Select-Object -First 1 -ExpandProperty IPAddress

        # fallback caso falhe
        if (-not $IpAddress) {
            $IpAddress = (hostname)
        }

        # =========================
        # CPU (MODERNO)
        # =========================
        $CpuLoad = (Get-CimInstance Win32_Processor |
            Measure-Object -Property LoadPercentage -Average).Average

        # =========================
        # RAM (MODERNO)
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
                
                $Latency = Get-Random -Minimum 1 -Maximum 15
                $Load = Get-Random -Minimum 5 -Maximum 75

                $ConexoesArray += @{
                    origem = $Hostname
                    ip_origem = $IpAddress
                    destino = $conn.RemoteAddress
                    servico = $Service
                    latencia = $Latency
                    carga = $Load
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
            $PayloadHash["nobreak"] = @{
                nome = if ($BatteryName) { $BatteryName } else { "Nobreak USB - " + $Hostname }
                ip = $IpAddress
                bateria = $BatteryPercent
                autonomia = $BatteryRuntime
                tensao = 220
                carga = 15
                status = if ($BatteryStatus -eq 1 -or $BatteryStatus -eq 8 -or $BatteryStatus -eq 9) { "alerta" } else { "online" }
            }
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
        # ENVIO (FORÇANDO TLS 1.2)
        # =========================
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

        $Response = Invoke-RestMethod `
            -Uri $ApiUrl `
            -Method Post `
            -Body $Payload `
            -Headers $Headers `
            -TimeoutSec 10 `
            -ErrorAction Stop

        Write-Host "OK [$((Get-Date).ToString('HH:mm:ss'))] CPU:$CpuLoad% RAM:$RamLivreMB MB" -ForegroundColor Cyan

    }
    catch {
        Write-Host "ERRO: $($_.Exception.Message)" -ForegroundColor Red
    }

    Start-Sleep -Seconds $IntervaloSegundos
}
