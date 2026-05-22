# ========================================
# InfraVision Agent - Script de Build
# ========================================
# Usa o compilador C# nativo do .NET Framework (disponivel em todo Windows)
# Nao requer instalacao do SDK do .NET ou Visual Studio!
#
# Uso: powershell -ExecutionPolicy Bypass -File build.ps1
# ========================================

$CSC = "C:\Windows\Microsoft.NET\Framework64\v4.0.30319\csc.exe"
$Output = "InfraVisionAgent.exe"
$Source = "Program.cs"
$Refs = "System.Management.dll"

Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "   InfraVision Agent - Compilando..." -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

if (-not (Test-Path $CSC)) {
    Write-Host "ERRO: Compilador nao encontrado em: $CSC" -ForegroundColor Red
    Write-Host "Verifique se o .NET Framework 4.x esta instalado." -ForegroundColor Yellow
    exit 1
}

$IconParam = ""
if (Test-Path "icon.ico") {
    $IconParam = "/win32icon:icon.ico"
    Write-Host "Usando icone: icon.ico" -ForegroundColor Yellow
}

& $CSC $IconParam /out:$Output /r:$Refs $Source 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "==============================================" -ForegroundColor Green
    Write-Host "   COMPILADO COM SUCESSO!" -ForegroundColor Green
    Write-Host "==============================================" -ForegroundColor Green
    $file = Get-Item $Output
    Write-Host "Arquivo: $($file.FullName)" -ForegroundColor White
    Write-Host "Tamanho: $([math]::Round($file.Length / 1KB, 1)) KB" -ForegroundColor White
    Write-Host ""
    Write-Host "Para usar o agente:" -ForegroundColor Cyan
    Write-Host "  Primeira execucao (configuracao interativa):" -ForegroundColor White
    Write-Host "    .\InfraVisionAgent.exe" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  Configurar via parametros:" -ForegroundColor White
    Write-Host "    .\InfraVisionAgent.exe --url localhost/infravision --token SEU_TOKEN" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  Instalar como tarefa automatica (rodar como Admin):" -ForegroundColor White
    Write-Host "    .\InfraVisionAgent.exe --install" -ForegroundColor Gray
    Write-Host "==============================================" -ForegroundColor Cyan
} else {
    Write-Host ""
    Write-Host "ERRO: Falha na compilacao. Verifique os erros acima." -ForegroundColor Red
    exit 1
}
