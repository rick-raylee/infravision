@echo off
echo =====================================================
echo   Compilando InfraVision Agent (Nativo do Windows)
echo =====================================================
echo.
echo Este script usa o compilador C# embutido no proprio Windows,
echo dispensando a instalacao do .NET SDK.
echo.

powershell -ExecutionPolicy Bypass -File "%~dp0build.ps1"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERRO: Falha ao compilar o agente.
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo Compilacao finalizada com sucesso! O arquivo InfraVisionAgent.exe foi gerado na mesma pasta.
pause
