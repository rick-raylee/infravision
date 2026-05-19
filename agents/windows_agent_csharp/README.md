# InfraVision Agent (C#)

Este é o agente de monitoramento nativo para Windows do InfraVision, desenvolvido em C# (.NET 8). Ele substitui o agente PowerShell anterior, trazendo maior robustez, performance e resolvendo problemas com políticas de execução do Windows.

## Pré-requisitos

Para compilar, você precisa do **.NET 8.0 SDK** (já detectado e disponível no sistema atual).

## Como Compilar

Você pode compilar o agente de duas formas:

### 1. Executável Autônomo (Recomendado para Produção)
Gera um único arquivo `.exe` contendo todas as dependências internas (.NET runtime embutido). Não requer que o servidor de destino tenha o .NET instalado.

Abra o prompt de comando ou PowerShell na pasta deste agente e execute:
```bash
dotnet publish -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true -p:PublishReadyToRun=true
```
O executável final ficará localizado em:
`bin/Release/net8.0-windows/win-x64/publish/InfraVisionAgent.exe`

### 2. Compilação Padrão (Dependente de Framework)
Gera um executável menor, mas exige que a máquina de destino tenha o runtime do .NET 8 instalado.

```bash
dotnet build -c Release
```
O executável final ficará localizado em:
`bin/Release/net8.0-windows/InfraVisionAgent.exe`

---

## Como Utilizar

### 1. Configuração Interativa (Primeira Execução)
Execute o arquivo `.exe` diretamente. Se não houver configuração anterior, ele iniciará um assistente interativo pedindo a URL e o Token:
```bash
.\InfraVisionAgent.exe
```

### 2. Configuração via Linha de Comando
Configure a URL do servidor e o token diretamente usando parâmetros:
```bash
.\InfraVisionAgent.exe --url "http://localhost/infravision" --token "QUALQUER_TOKEN"
```

### 3. Instalar como Serviço/Tarefa Oculta em Segundo Plano
Para fazer o agente rodar de forma oculta sempre que o Windows iniciar (como conta `SYSTEM`), execute como **Administrador**:
```bash
.\InfraVisionAgent.exe --install
```
Isso cadastrará uma tarefa no Agendador de Tarefas do Windows. Para iniciar a tarefa imediatamente sem precisar reiniciar, execute:
```bash
schtasks.exe /Run /TN "InfraVisionAgent"
```

### 4. Desinstalar do Sistema
Para remover a tarefa do Agendador de Tarefas do Windows, execute como **Administrador**:
```bash
.\InfraVisionAgent.exe --uninstall
```

### 5. Redefinir Configurações
Para apagar as configurações salvas no `agent_config.json`:
```bash
.\InfraVisionAgent.exe --reset
```
