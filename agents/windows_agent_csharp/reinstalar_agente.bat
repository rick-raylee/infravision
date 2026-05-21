@echo off
:: InfraVision Agent - Script de Reinstalacao
:: Execute como Administrador!
echo =====================================================
echo   InfraVision NOC - Reinstalando Agente...
echo =====================================================

:: Parar tarefa existente
schtasks /End /TN "InfraVisionAgent" 2>nul
schtasks /Delete /TN "InfraVisionAgent" /F 2>nul
echo [1/4] Tarefa antiga removida.

:: Aguardar processo terminar
timeout /t 3 /nobreak >nul

:: Caminho do executavel
set AGENT_EXE=%~dp0InfraVisionAgent.exe

if not exist "%AGENT_EXE%" (
    echo ERRO: InfraVisionAgent.exe nao encontrado em %~dp0
    echo Compile o agente primeiro!
    pause
    exit /b 1
)

echo [2/4] Executavel encontrado: %AGENT_EXE%

:: Criar XML da tarefa com PT0S (sem limite de tempo) e reinicio automatico
set XML_FILE=%TEMP%\InfraVisionTask.xml

echo ^<?xml version="1.0" encoding="UTF-16"?^> > "%XML_FILE%"
echo ^<Task version="1.2" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task"^> >> "%XML_FILE%"
echo   ^<RegistrationInfo^>^<Description^>InfraVision NOC Agent^</Description^>^</RegistrationInfo^> >> "%XML_FILE%"
echo   ^<Triggers^> >> "%XML_FILE%"
echo     ^<BootTrigger^>^<Enabled^>true^</Enabled^>^<Delay^>PT30S^</Delay^>^</BootTrigger^> >> "%XML_FILE%"
echo   ^</Triggers^> >> "%XML_FILE%"
echo   ^<Principals^> >> "%XML_FILE%"
echo     ^<Principal id="Author"^>^<UserId^>S-1-5-18^</UserId^>^<RunLevel^>HighestAvailable^</RunLevel^>^</Principal^> >> "%XML_FILE%"
echo   ^</Principals^> >> "%XML_FILE%"
echo   ^<Settings^> >> "%XML_FILE%"
echo     ^<MultipleInstancesPolicy^>IgnoreNew^</MultipleInstancesPolicy^> >> "%XML_FILE%"
echo     ^<DisallowStartIfOnBatteries^>false^</DisallowStartIfOnBatteries^> >> "%XML_FILE%"
echo     ^<StopIfGoingOnBatteries^>false^</StopIfGoingOnBatteries^> >> "%XML_FILE%"
echo     ^<AllowHardTerminate^>false^</AllowHardTerminate^> >> "%XML_FILE%"
echo     ^<ExecutionTimeLimit^>PT0S^</ExecutionTimeLimit^> >> "%XML_FILE%"
echo     ^<RestartOnFailure^>^<Interval^>PT1M^</Interval^>^<Count^>999^</Count^>^</RestartOnFailure^> >> "%XML_FILE%"
echo     ^<Enabled^>true^</Enabled^> >> "%XML_FILE%"
echo     ^<RunOnlyIfNetworkAvailable^>true^</RunOnlyIfNetworkAvailable^> >> "%XML_FILE%"
echo   ^</Settings^> >> "%XML_FILE%"
echo   ^<Actions Context="Author"^> >> "%XML_FILE%"
echo     ^<Exec^>^<Command^>"%AGENT_EXE%"^</Command^>^</Exec^> >> "%XML_FILE%"
echo   ^</Actions^> >> "%XML_FILE%"
echo ^</Task^> >> "%XML_FILE%"

:: Registrar tarefa
schtasks /Create /TN "InfraVisionAgent" /XML "%XML_FILE%" /F
if %errorlevel% neq 0 (
    echo ERRO ao registrar tarefa. Tente executar como Administrador!
    del "%XML_FILE%" 2>nul
    pause
    exit /b 1
)
del "%XML_FILE%" 2>nul
echo [3/4] Tarefa registrada com sucesso (sem limite de tempo, reinicia automaticamente).

:: Iniciar agora
schtasks /Run /TN "InfraVisionAgent"
echo [4/4] Agente iniciado!

echo.
echo =====================================================
echo   CONCLUIDO! O agente esta rodando em background.
echo   Verifique em: Agendador de Tarefas > InfraVisionAgent
echo =====================================================
pause
