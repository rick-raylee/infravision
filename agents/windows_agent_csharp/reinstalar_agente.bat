@echo off
:: InfraVision Agent - Script de Reinstalacao
:: Execute como Administrador!
echo =====================================================
echo   InfraVision NOC - Reinstalando Agente...
echo =====================================================

:: Tentar encontrar o executavel nas pastas de publish (x64 ou x86)
set AGENT_EXE=%~dp0publish\x64\InfraVisionAgent.exe

if not exist "%AGENT_EXE%" (
    set AGENT_EXE=%~dp0publish\x86\InfraVisionAgent.exe
)

if not exist "%AGENT_EXE%" (
    echo ERRO: InfraVisionAgent.exe nao encontrado nas pastas publish\x64 ou publish\x86.
    echo Por favor, dê um clique duplo no arquivo 'build_agent.bat' primeiro para gerar o executavel!
    pause
    exit /b 1
)

echo [1/3] Executavel encontrado: %AGENT_EXE%

:: Instalar/Registrar na inicializacao do Windows via Registro
echo [2/3] Adicionando a inicializacao do Windows (Registro)...
"%AGENT_EXE%" --install

:: Iniciar o processo agora (sem bloquear o console)
echo [3/3] Iniciando o agente em background...
start "" "%AGENT_EXE%"

echo.
echo =====================================================
echo   CONCLUIDO! O agente esta rodando.
echo   Ele tambem ira iniciar automaticamente ao ligar o PC.
echo =====================================================
pause
