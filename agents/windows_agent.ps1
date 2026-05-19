# ============================
# InfraVision Agent - FIXED
# ============================

$ApiUrl = "http://SEU_IP_AQUI/infravision/api/receber_agente.php"
$AuthToken = "SEU_TOKEN_AQUI"
$IntervaloSegundos = 60

Write-Host "InfraVision Agent iniciado..." -ForegroundColor Green

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
        # PAYLOAD JSON
        # =========================
        $Payload = @{
            hostname = $Hostname
            ip = $IpAddress
            cpu_load = [math]::Round($CpuLoad, 2)
            ram_total_mb = $RamTotalMB
            ram_livre_mb = $RamLivreMB
            discos = $DiscosArray
            servicos = $ServicosArray
            timestamp = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
        } | ConvertTo-Json -Depth 5

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
