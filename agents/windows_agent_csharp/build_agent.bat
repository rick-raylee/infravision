@echo off
echo =====================================================
echo   Compilando InfraVision Agent (Self-Contained)
echo =====================================================
echo.
echo Este script vai gerar executaveis unicos (.exe) que NAO
echo exigem a instalacao do .NET 8 nas maquinas dos clientes.
echo.

echo [1/2] Compilando versao 64-bits (x64)...
dotnet publish InfraVisionAgent.csproj -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true -p:IncludeNativeLibrariesForSelfExtract=true -o ./publish/x64

echo.
echo [2/2] Compilando versao 32-bits (x86)...
dotnet publish InfraVisionAgent.csproj -c Release -r win-x86 --self-contained true -p:PublishSingleFile=true -p:IncludeNativeLibrariesForSelfExtract=true -o ./publish/x86

echo.
echo =====================================================
echo COMPILACAO CONCLUIDA COM SUCESSO!
echo =====================================================
echo.
echo Os executaveis prontos para uso estao nas pastas:
echo - Para Windows 64-bits: publish\x64\InfraVisionAgent.exe
echo - Para Windows 32-bits: publish\x86\InfraVisionAgent.exe
echo.
pause
