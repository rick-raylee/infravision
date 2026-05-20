using System;
using System.Collections.Generic;
using System.IO;
using System.Text;
using System.Net;
using System.Net.NetworkInformation;
using System.Net.Sockets;
using System.Management;
using System.Security.Principal;
using System.Diagnostics;
using System.Threading;

namespace InfraVisionAgent
{
    public class AgentConfig
    {
        public string ApiUrl { get; set; }
        public string AuthToken { get; set; }
        public int Intervalo { get; set; }
        public bool MonitorNobreak { get; set; }

        public AgentConfig()
        {
            ApiUrl = string.Empty;
            AuthToken = "QUALQUER_TOKEN";
            Intervalo = 60;
            MonitorNobreak = false;
        }
    }

    public static class SimpleJson
    {
        public static string Serialize(object obj)
        {
            if (obj == null) return "null";
            if (obj is string) return "\"" + EscapeString((string)obj) + "\"";
            if (obj is bool) return ((bool)obj) ? "true" : "false";
            if (obj is double || obj is float || obj is decimal) 
                return ((IFormattable)obj).ToString(null, System.Globalization.CultureInfo.InvariantCulture);
            if (obj is int || obj is long || obj is uint || obj is ulong || obj is short || obj is ushort || obj is byte) 
                return obj.ToString();
            if (obj is IDictionary<string, object>)
            {
                var dict = (IDictionary<string, object>)obj;
                var sb = new StringBuilder();
                sb.Append("{");
                bool first = true;
                foreach (var kvp in dict)
                {
                    if (!first) sb.Append(",");
                    sb.Append("\"" + EscapeString(kvp.Key) + "\":");
                    sb.Append(Serialize(kvp.Value));
                    first = false;
                }
                sb.Append("}");
                return sb.ToString();
            }
            if (obj is System.Collections.IEnumerable)
            {
                var list = (System.Collections.IEnumerable)obj;
                var sb = new StringBuilder();
                sb.Append("[");
                bool first = true;
                foreach (var item in list)
                {
                    if (!first) sb.Append(",");
                    sb.Append(Serialize(item));
                    first = false;
                }
                sb.Append("]");
                return sb.ToString();
            }
            return "\"" + EscapeString(obj.ToString()) + "\"";
        }

        private static string EscapeString(string s)
        {
            if (string.IsNullOrEmpty(s)) return "";
            return s.Replace("\\", "\\\\").Replace("\"", "\\\"").Replace("\n", "\\n").Replace("\r", "\\r").Replace("\t", "\\t");
        }
    }

    public class SystemDetails
    {
        public string Manufacturer = "Desconhecido";
        public string Model = "Desconhecido";
        public string SerialNumber = "Desconhecido";
        public string OS = "Desconhecido";
        public string CPU = "Desconhecido";
        public string DeviceType = "servidor_windows";
    }

    class Program
    {
        private static string GetConfigPath()
        {
            string exePath = AppDomain.CurrentDomain.BaseDirectory;
            return Path.Combine(exePath, "agent_config.json");
        }

        private static string GetSafeString(object val)
        {
            return val == null ? null : val.ToString();
        }

        static void Main(string[] args)
        {
            // Set console output encoding to UTF8
            try
            {
                Console.OutputEncoding = Encoding.UTF8;
            }
            catch { }

            string configPath = GetConfigPath();

            // Simple Argument Parsing
            bool install = false;
            bool uninstall = false;
            bool reset = false;
            string urlParam = null;
            string tokenParam = null;

            for (int i = 0; i < args.Length; i++)
            {
                if (args[i] == "--install" || args[i] == "-i") install = true;
                else if (args[i] == "--uninstall" || args[i] == "-u") uninstall = true;
                else if (args[i] == "--reset" || args[i] == "-r") reset = true;
                else if (args[i] == "--url" && i + 1 < args.Length) urlParam = args[++i];
                else if (args[i] == "--token" && i + 1 < args.Length) tokenParam = args[++i];
            }

            // Reset Config
            if (reset)
            {
                if (File.Exists(configPath))
                {
                    File.Delete(configPath);
                    Console.ForegroundColor = ConsoleColor.Yellow;
                    Console.WriteLine("Arquivo de configuração 'agent_config.json' removido.");
                    Console.ResetColor();
                }
            }

            // Install as Scheduled Task
            if (install)
            {
                InstallTask();
                return;
            }

            // Uninstall Task
            if (uninstall)
            {
                UninstallTask();
                return;
            }

            // Set Config via Parameters
            if (!string.IsNullOrEmpty(urlParam))
            {
                string cleanUrl = urlParam;
                if (!cleanUrl.StartsWith("http://") && !cleanUrl.StartsWith("https://"))
                {
                    cleanUrl = "http://" + cleanUrl;
                }
                if (!cleanUrl.EndsWith("receber_agente.php"))
                {
                    cleanUrl = cleanUrl.TrimEnd('/') + "/api/receber_agente.php";
                }

                AgentConfig config = new AgentConfig();
                config.ApiUrl = cleanUrl;
                config.AuthToken = tokenParam ?? "QUALQUER_TOKEN";
                config.Intervalo = 60;

                SaveConfig(configPath, config);
                Console.ForegroundColor = ConsoleColor.Green;
                Console.WriteLine(string.Format("Configuração salva em: {0}", configPath));
                Console.ResetColor();
            }

            // Interactive Setup if config doesn't exist
            if (!File.Exists(configPath))
            {
                SetupInteractive(configPath);
            }

            // Load Config
            AgentConfig agentConfig = LoadConfig(configPath);
            if (agentConfig == null || string.IsNullOrEmpty(agentConfig.ApiUrl))
            {
                Console.ForegroundColor = ConsoleColor.Red;
                Console.WriteLine("ERRO: Configuração inválida. Por favor, configure o agente corretamente.");
                Console.ResetColor();
                return;
            }

            Console.ForegroundColor = ConsoleColor.Green;
            Console.WriteLine("InfraVision Agent iniciado com sucesso!");
            Console.ForegroundColor = ConsoleColor.Cyan;
            Console.WriteLine(string.Format("URL de Destino: {0}", agentConfig.ApiUrl));
            Console.WriteLine(string.Format("Intervalo: {0} segundos", agentConfig.Intervalo));
            Console.ForegroundColor = ConsoleColor.Yellow;
            Console.WriteLine("Pressione Ctrl+C para encerrar.");
            Console.ResetColor();
            Console.WriteLine();

            // Set SecurityProtocol for TLS 1.2
            try
            {
                ServicePointManager.SecurityProtocol = (SecurityProtocolType)3072; // TLS 1.2
            }
            catch { }

            while (true)
            {
                try
                {
                    Dictionary<string, object> payload = CollectMetrics(agentConfig);
                    string jsonPayload = SimpleJson.Serialize(payload);

                    using (WebClient client = new WebClient())
                    {
                        client.Headers[HttpRequestHeader.ContentType] = "application/json";
                        client.Headers[HttpRequestHeader.Authorization] = "Bearer " + agentConfig.AuthToken;
                        client.Encoding = Encoding.UTF8;

                        string response = client.UploadString(agentConfig.ApiUrl, "POST", jsonPayload);

                        Console.ForegroundColor = ConsoleColor.Cyan;
                        object cpuVal = payload.ContainsKey("cpu_load") ? payload["cpu_load"] : 0;
                        object ramFreeVal = payload.ContainsKey("ram_livre_mb") ? payload["ram_livre_mb"] : 0;
                        Console.WriteLine(string.Format("OK [{0:HH:mm:ss}] CPU: {1}% RAM Livre: {2} MB", DateTime.Now, cpuVal, ramFreeVal));
                        Console.ResetColor();
                    }
                }
                catch (Exception ex)
                {
                    Console.ForegroundColor = ConsoleColor.Red;
                    Console.WriteLine(string.Format("ERRO: {0}", ex.Message));
                    Console.ResetColor();
                }

                Thread.Sleep(agentConfig.Intervalo * 1000);
            }
        }

        private static void SaveConfig(string path, AgentConfig config)
        {
            try
            {
                string json = string.Format("{{\n  \"ApiUrl\": \"{0}\",\n  \"AuthToken\": \"{1}\",\n  \"Intervalo\": {2},\n  \"MonitorNobreak\": {3}\n}}", 
                    config.ApiUrl.Replace("\\", "\\\\").Replace("\"", "\\\""), 
                    config.AuthToken.Replace("\\", "\\\\").Replace("\"", "\\\""), 
                    config.Intervalo,
                    config.MonitorNobreak ? "true" : "false");
                File.WriteAllText(path, json, Encoding.UTF8);
            }
            catch (Exception ex)
            {
                Console.WriteLine("Erro ao salvar configuração: " + ex.Message);
            }
        }

        private static AgentConfig LoadConfig(string path)
        {
            try
            {
                string content = File.ReadAllText(path, Encoding.UTF8);
                AgentConfig config = new AgentConfig();
                
                // Simple parsing without external library
                string url = ExtractJsonValue(content, "ApiUrl");
                string token = ExtractJsonValue(content, "AuthToken");
                string intervalStr = ExtractJsonValue(content, "Intervalo");
                string monitorNbStr = ExtractJsonValue(content, "MonitorNobreak");

                if (!string.IsNullOrEmpty(url)) config.ApiUrl = url;
                if (!string.IsNullOrEmpty(token)) config.AuthToken = token;
                if (!string.IsNullOrEmpty(intervalStr))
                {
                    int interval;
                    if (int.TryParse(intervalStr, out interval))
                    {
                        config.Intervalo = interval;
                    }
                }
                if (!string.IsNullOrEmpty(monitorNbStr))
                {
                    config.MonitorNobreak = monitorNbStr.Trim().Equals("true", StringComparison.OrdinalIgnoreCase);
                }

                return config;
            }
            catch
            {
                return null;
            }
        }

        private static string ExtractJsonValue(string json, string key)
        {
            string search = string.Format("\"{0}\"", key);
            int pos = json.IndexOf(search);
            if (pos == -1) return string.Empty;

            int colonPos = json.IndexOf(":", pos + search.Length);
            if (colonPos == -1) return string.Empty;

            // Find the value start
            int valStart = colonPos + 1;
            while (valStart < json.Length && (char.IsWhiteSpace(json[valStart]) || json[valStart] == '"'))
            {
                valStart++;
            }

            // Find the value end
            int valEnd = valStart;
            if (json.Substring(colonPos + 1).Contains("\"") || json.Substring(pos).IndexOf("\"") != -1)
            {
                // Check if string (starts with quote in value)
                bool isString = json.Substring(colonPos, valStart - colonPos).Contains("\"");
                if (isString)
                {
                    while (valEnd < json.Length && json[valEnd] != '"')
                    {
                        if (json[valEnd] == '\\') valEnd++; // skip escaped
                        valEnd++;
                    }
                    return json.Substring(valStart, valEnd - valStart).Replace("\\\\", "\\").Replace("\\\"", "\"");
                }
            }

            // Numeric or boolean value
            while (valEnd < json.Length && json[valEnd] != ',' && json[valEnd] != '}' && json[valEnd] != '\n' && json[valEnd] != '\r')
            {
                valEnd++;
            }
            return json.Substring(valStart, valEnd - valStart).Trim();
        }

        private static void SetupInteractive(string path)
        {
            Console.ForegroundColor = ConsoleColor.Cyan;
            Console.WriteLine("=============================================");
            Console.WriteLine("     BEM-VINDO AO INFRAVISION AGENT (C#)");
            Console.WriteLine("=============================================");
            Console.ForegroundColor = ConsoleColor.White;
            Console.WriteLine("Nenhuma configuração encontrada. Vamos configurar agora.");
            Console.WriteLine();

            Console.Write("Digite a URL ou IP do InfraVision (Ex: localhost/infravision ou seu-noc.onrender.com): ");
            string inputUrl = Console.ReadLine();
            if (string.IsNullOrEmpty(inputUrl))
            {
                inputUrl = "localhost/infravision";
            }

            if (!inputUrl.StartsWith("http://") && !inputUrl.StartsWith("https://"))
            {
                inputUrl = "http://" + inputUrl;
            }
            if (!inputUrl.EndsWith("receber_agente.php"))
            {
                inputUrl = inputUrl.TrimEnd('/') + "/api/receber_agente.php";
            }

            Console.Write("Digite o token de autenticação [Deixe em branco para o padrão]: ");
            string inputToken = Console.ReadLine();
            if (string.IsNullOrEmpty(inputToken))
            {
                inputToken = "QUALQUER_TOKEN";
            }

            AgentConfig config = new AgentConfig();
            config.ApiUrl = inputUrl;
            config.AuthToken = inputToken;
            config.Intervalo = 60;
            config.MonitorNobreak = false;

            SaveConfig(path, config);
            Console.ForegroundColor = ConsoleColor.Green;
            Console.WriteLine();
            Console.WriteLine(string.Format("Configuração salva em: {0}", path));
            Console.ForegroundColor = ConsoleColor.Gray;
            Console.WriteLine("DICA: Para instalar como tarefa de inicialização em segundo plano, rode como Admin:");
            Console.WriteLine("      InfraVisionAgent.exe --install");
            Console.ForegroundColor = ConsoleColor.Cyan;
            Console.WriteLine("=============================================");
            Console.ResetColor();
            Console.WriteLine();
        }

        private static bool IsUserAdmin()
        {
            WindowsIdentity identity = WindowsIdentity.GetCurrent();
            WindowsPrincipal principal = new WindowsPrincipal(identity);
            return principal.IsInRole(WindowsBuiltInRole.Administrator);
        }

        private static void InstallTask()
        {
            if (!IsUserAdmin())
            {
                Console.ForegroundColor = ConsoleColor.Red;
                Console.WriteLine("ERRO: Você precisa executar como Administrador para instalar o agente como serviço!");
                Console.ResetColor();
                return;
            }

            string exePath = Process.GetCurrentProcess().MainModule.FileName;
            if (string.IsNullOrEmpty(exePath))
            {
                Console.ForegroundColor = ConsoleColor.Red;
                Console.WriteLine("ERRO: Não foi possível obter o caminho do executável.");
                Console.ResetColor();
                return;
            }

            string taskName = "InfraVisionAgent";
            string command = string.Format("schtasks.exe /Create /TN \"{0}\" /TR \"\\\"{1}\\\"\" /SC ONSTART /RU \"SYSTEM\" /F", taskName, exePath);

            try
            {
                ProcessStartInfo startInfo = new ProcessStartInfo();
                startInfo.FileName = "cmd.exe";
                startInfo.Arguments = "/c " + command;
                startInfo.CreateNoWindow = true;
                startInfo.UseShellExecute = false;
                startInfo.RedirectStandardOutput = true;
                startInfo.RedirectStandardError = true;

                using (Process process = Process.Start(startInfo))
                {
                    process.WaitForExit();
                    int exitCode = process.ExitCode;

                    if (exitCode == 0)
                    {
                        Console.ForegroundColor = ConsoleColor.Green;
                        Console.WriteLine("==================================================");
                        Console.WriteLine("   AGENTE INSTALADO COM SUCESSO NO SISTEMA!");
                        Console.WriteLine("==================================================");
                        Console.ForegroundColor = ConsoleColor.White;
                        Console.WriteLine("O agente InfraVision rodará em segundo plano (como SYSTEM)");
                        Console.WriteLine("toda vez que o Windows iniciar.");
                        Console.WriteLine();
                        Console.ForegroundColor = ConsoleColor.Cyan;
                        Console.WriteLine("Para iniciar o agente agora sem reiniciar a máquina, rode:");
                        Console.WriteLine(string.Format("  schtasks.exe /Run /TN \"{0}\"", taskName));
                        Console.ResetColor();
                    }
                    else
                    {
                        string err = process.StandardError.ReadToEnd();
                        Console.ForegroundColor = ConsoleColor.Red;
                        Console.WriteLine(string.Format("Erro ao cadastrar agente no Agendador de Tarefas: {0}", err));
                        Console.ResetColor();
                    }
                }
            }
            catch (Exception ex)
            {
                Console.ForegroundColor = ConsoleColor.Red;
                Console.WriteLine(string.Format("Erro ao rodar instalador: {0}", ex.Message));
                Console.ResetColor();
            }
        }

        private static void UninstallTask()
        {
            if (!IsUserAdmin())
            {
                Console.ForegroundColor = ConsoleColor.Red;
                Console.WriteLine("ERRO: Você precisa executar como Administrador para desinstalar o agente!");
                Console.ResetColor();
                return;
            }

            string taskName = "InfraVisionAgent";
            string command = string.Format("schtasks.exe /Delete /TN \"{0}\" /F", taskName);

            try
            {
                ProcessStartInfo startInfo = new ProcessStartInfo();
                startInfo.FileName = "cmd.exe";
                startInfo.Arguments = "/c " + command;
                startInfo.CreateNoWindow = true;
                startInfo.UseShellExecute = false;
                startInfo.RedirectStandardOutput = true;
                startInfo.RedirectStandardError = true;

                using (Process process = Process.Start(startInfo))
                {
                    process.WaitForExit();
                    int exitCode = process.ExitCode;

                    if (exitCode == 0)
                    {
                        Console.ForegroundColor = ConsoleColor.Yellow;
                        Console.WriteLine("Agente desinstalado com sucesso do Agendador de Tarefas.");
                        Console.ResetColor();
                    }
                    else
                    {
                        string err = process.StandardError.ReadToEnd();
                        Console.ForegroundColor = ConsoleColor.Red;
                        Console.WriteLine(string.Format("Erro ao desinstalar agente: {0}", err));
                        Console.ResetColor();
                    }
                }
            }
            catch (Exception ex)
            {
                Console.ForegroundColor = ConsoleColor.Red;
                Console.WriteLine(string.Format("Erro ao rodar desinstalador: {0}", ex.Message));
                Console.ResetColor();
            }
        }

        private static Dictionary<string, object> CollectMetrics(AgentConfig config)
        {
            Dictionary<string, object> metrics = new Dictionary<string, object>();

            // 1. Hostname
            string hostname = Environment.MachineName;
            metrics["hostname"] = hostname;

            // 2. IP Address
            string ipAddress = GetLocalIpAddress();
            metrics["ip"] = ipAddress;

            // 3. CPU Load
            double cpuLoad = GetCpuLoad();
            metrics["cpu_load"] = Math.Round(cpuLoad, 2);

            // 4. RAM
            double ramTotal = 0;
            double ramFree = 0;
            GetRamInfo(out ramTotal, out ramFree);
            metrics["ram_total_mb"] = ramTotal;
            metrics["ram_livre_mb"] = ramFree;

            // 5. Disks
            List<Dictionary<string, object>> disks = GetDiskInfo();
            metrics["discos"] = disks;

            // 6. Services
            List<Dictionary<string, object>> services = GetServicesInfo();
            metrics["servicos"] = services;

            // 7. Connections
            List<Dictionary<string, object>> connections = GetActiveConnections(hostname, ipAddress);
            metrics["conexoes"] = connections;

            // 8. Hardware details & OS
            SystemDetails systemInfo = GetSystemInfo();
            metrics["fabricante"] = systemInfo.Manufacturer;
            metrics["modelo"] = systemInfo.Model;
            metrics["numero_serie"] = systemInfo.SerialNumber;
            metrics["sistema_operacional"] = systemInfo.OS;
            metrics["processador"] = systemInfo.CPU;
            metrics["tipo"] = systemInfo.DeviceType;
            metrics["usuario_logado"] = GetLoggedUser();

            // 9. Timestamp
            metrics["timestamp"] = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");

            // 10. Nobreak/UPS apenas se habilitado (bateria de notebook NAO e nobreak)
            metrics["monitor_nobreak"] = config.MonitorNobreak;
            if (config.MonitorNobreak)
            {
                Dictionary<string, object> batteryInfo = GetBatteryInfo(ipAddress, hostname);
                if (batteryInfo != null)
                {
                    metrics["nobreak"] = batteryInfo;
                }
            }

            return metrics;
        }

        private static string GetLocalIpAddress()
        {
            try
            {
                NetworkInterface[] interfaces = NetworkInterface.GetAllNetworkInterfaces();
                foreach (NetworkInterface ni in interfaces)
                {
                    if (ni.OperationalStatus == OperationalStatus.Up && 
                        ni.NetworkInterfaceType != NetworkInterfaceType.Loopback && 
                        ni.NetworkInterfaceType != NetworkInterfaceType.Tunnel)
                    {
                        IPInterfaceProperties ipProps = ni.GetIPProperties();
                        foreach (UnicastIPAddressInformation ip in ipProps.UnicastAddresses)
                        {
                            if (ip.Address.AddressFamily == AddressFamily.InterNetwork)
                            {
                                return ip.Address.ToString();
                            }
                        }
                    }
                }
            }
            catch { }

            return Environment.MachineName; // Fallback
        }

        private static double GetCpuLoad()
        {
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT LoadPercentage FROM Win32_Processor"))
                {
                    double sum = 0;
                    int count = 0;
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        object pct = obj["LoadPercentage"];
                        if (pct != null)
                        {
                            sum += Convert.ToDouble(pct);
                            count++;
                        }
                    }
                    return count > 0 ? (sum / count) : 0.0;
                }
            }
            catch
            {
                return 0.0;
            }
        }

        private static void GetRamInfo(out double totalMB, out double freeMB)
        {
            totalMB = 0;
            freeMB = 0;
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT TotalVisibleMemorySize, FreePhysicalMemory FROM Win32_OperatingSystem"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        object total = obj["TotalVisibleMemorySize"];
                        object free = obj["FreePhysicalMemory"];
                        if (total != null && free != null)
                        {
                            totalMB = Math.Round(Convert.ToDouble(total) / 1024.0, 0);
                            freeMB = Math.Round(Convert.ToDouble(free) / 1024.0, 0);
                            return;
                        }
                    }
                }
            }
            catch { }
        }

        private static List<Dictionary<string, object>> GetDiskInfo()
        {
            List<Dictionary<string, object>> list = new List<Dictionary<string, object>>();
            try
            {
                foreach (DriveInfo drive in DriveInfo.GetDrives())
                {
                    if (drive.IsReady && drive.DriveType == DriveType.Fixed)
                    {
                        Dictionary<string, object> disk = new Dictionary<string, object>();
                        disk["letra"] = drive.Name.TrimEnd('\\');
                        disk["tamanho_gb"] = Math.Round(drive.TotalSize / 1073741824.0, 2);
                        disk["livre_gb"] = Math.Round(drive.AvailableFreeSpace / 1073741824.0, 2);
                        list.Add(disk);
                    }
                }
            }
            catch { }
            return list;
        }

        private static List<Dictionary<string, object>> GetServicesInfo()
        {
            List<Dictionary<string, object>> list = new List<Dictionary<string, object>>();
            string[] monitored = new string[] { "Spooler", "LanmanServer" };

            try
            {
                foreach (string s in monitored)
                {
                    using (ManagementObjectSearcher searcher = new ManagementObjectSearcher(string.Format("SELECT Name, State FROM Win32_Service WHERE Name='{0}'", s)))
                    {
                        foreach (ManagementObject obj in searcher.Get())
                        {
                            Dictionary<string, object> srv = new Dictionary<string, object>();
                            srv["nome"] = GetSafeString(obj["Name"]);
                            if (srv["nome"] == null) srv["nome"] = s;
                            srv["status"] = GetSafeString(obj["State"]);
                            if (srv["status"] == null) srv["status"] = "Stopped";
                            list.Add(srv);
                        }
                    }
                }
            }
            catch { }

            return list;
        }

        private static List<Dictionary<string, object>> GetActiveConnections(string hostname, string ipAddress)
        {
            List<Dictionary<string, object>> list = new List<Dictionary<string, object>>();
            try
            {
                IPGlobalProperties properties = IPGlobalProperties.GetIPGlobalProperties();
                TcpConnectionInformation[] connections = properties.GetActiveTcpConnections();
                int count = 0;

                foreach (TcpConnectionInformation conn in connections)
                {
                    if (conn.State == TcpState.Established)
                    {
                        string remoteIp = conn.RemoteEndPoint.Address.ToString();
                        if (remoteIp == "127.0.0.1" || remoteIp == "::1") continue;

                        int port = conn.RemoteEndPoint.Port;
                        string service = string.Format("Porta {0}", port);
                        if (port == 80) service = "HTTP (80)";
                        else if (port == 443) service = "HTTPS (443)";
                        else if (port == 445) service = "SMB (445)";
                        else if (port == 3306) service = "MySQL (3306)";
                        else if (port == 22) service = "SSH (22)";
                        else if (port == 3389) service = "RDP (3389)";

                        Dictionary<string, object> connDict = new Dictionary<string, object>();
                        connDict["origem"] = hostname;
                        connDict["ip_origem"] = ipAddress;
                        connDict["destino"] = remoteIp;
                        connDict["servico"] = service;
                        connDict["latencia"] = 0;
                        connDict["carga"] = 0;
                        list.Add(connDict);

                        count++;
                        if (count >= 5) break;
                    }
                }
            }
            catch { }
            return list;
        }

        private static string GetLoggedUser()
        {
            string loggedUser = string.Empty;
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT ProcessId FROM Win32_Process WHERE Name='explorer.exe'"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        object outParamsObj = obj.InvokeMethod("GetOwner", null, null);
                        ManagementBaseObject outParams = outParamsObj as ManagementBaseObject;
                        if (outParams != null && outParams["User"] != null)
                        {
                            string user = GetSafeString(outParams["User"]);
                            string domain = GetSafeString(outParams["Domain"]);
                            loggedUser = !string.IsNullOrEmpty(domain) ? string.Format("{0}\\{1}", domain, user) : user;
                            break;
                        }
                    }
                }
            }
            catch { }

            if (string.IsNullOrEmpty(loggedUser))
            {
                try
                {
                    using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT UserName FROM Win32_ComputerSystem"))
                    {
                        foreach (ManagementObject obj in searcher.Get())
                        {
                            loggedUser = GetSafeString(obj["UserName"]);
                            break;
                        }
                    }
                }
                catch { }
            }

            if (string.IsNullOrEmpty(loggedUser))
            {
                loggedUser = string.Format("{0}\\{1}", Environment.UserDomainName, Environment.UserName);
            }

            return loggedUser;
        }

        private static SystemDetails GetSystemInfo()
        {
            SystemDetails info = new SystemDetails();

            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Manufacturer, Model FROM Win32_ComputerSystem"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        info.Manufacturer = GetSafeString(obj["Manufacturer"]);
                        if (info.Manufacturer == null) info.Manufacturer = "Desconhecido";
                        info.Model = GetSafeString(obj["Model"]);
                        if (info.Model == null) info.Model = "Desconhecido";
                        break;
                    }
                }
            }
            catch { }

            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT SerialNumber FROM Win32_Bios"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        info.SerialNumber = GetSafeString(obj["SerialNumber"]);
                        if (info.SerialNumber == null) info.SerialNumber = "Desconhecido";
                        break;
                    }
                }
            }
            catch { }

            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Caption, OSArchitecture, ProductType FROM Win32_OperatingSystem"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        string caption = GetSafeString(obj["Caption"]);
                        if (caption == null) caption = "Desconhecido";
                        string arch = GetSafeString(obj["OSArchitecture"]);
                        if (arch == null) arch = string.Empty;
                        info.OS = string.Format("{0} ({1})", caption, arch);

                        // ProductType: 1 = Workstation, 2 = Domain Controller, 3 = Server
                        object prodType = obj["ProductType"];
                        if (prodType != null && Convert.ToInt32(prodType) == 1)
                        {
                            info.DeviceType = "computador";
                        }
                        break;
                    }
                }
            }
            catch { }

            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Name FROM Win32_Processor"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        info.CPU = GetSafeString(obj["Name"]);
                        if (info.CPU == null) info.CPU = "Desconhecido";
                        break;
                    }
                }
            }
            catch { }

            return info;
        }

        private static int? NormalizeBatteryRunTimeMinutes(object batteryRuntime)
        {
            if (batteryRuntime == null) return null;
            int minutes = Convert.ToInt32(batteryRuntime);
            // WMI: 0, 65535 e ~71582788 = desconhecido (comum na tomada AC)
            if (minutes <= 0 || minutes >= 65535 || minutes >= 71582700 || minutes > 10080)
            {
                return null;
            }
            return minutes;
        }

        private static string NormalizeBatteryName(string batteryName, string hostname)
        {
            if (string.IsNullOrWhiteSpace(batteryName)) return string.Format("Nobreak USB - {0}", hostname);
            string trimmed = batteryName.Trim();
            bool onlyDigits = true;
            foreach (char c in trimmed)
            {
                if (!char.IsDigit(c)) { onlyDigits = false; break; }
            }
            if (onlyDigits) return string.Format("Nobreak USB - {0}", hostname);
            return trimmed;
        }

        private static Dictionary<string, object> GetBatteryInfo(string ipAddress, string hostname)
        {
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Name, EstimatedChargeRemaining, EstimatedRunTime, BatteryStatus FROM Win32_Battery"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        object batteryPercent = obj["EstimatedChargeRemaining"];
                        object batteryRuntime = obj["EstimatedRunTime"];
                        object batteryStatus = obj["BatteryStatus"];
                        
                        object nameObj = obj["Name"];
                        string batteryName = NormalizeBatteryName(nameObj != null ? nameObj.ToString() : null, hostname);

                        string statusStr = "online";
                        if (batteryStatus != null)
                        {
                            ushort statusVal = Convert.ToUInt16(batteryStatus);
                            if (statusVal == 1 || statusVal == 8 || statusVal == 9 || statusVal == 4 || statusVal == 5)
                            {
                                statusStr = "alerta";
                            }
                        }

                        Dictionary<string, object> battery = new Dictionary<string, object>();
                        battery["nome"] = batteryName;
                        battery["ip"] = ipAddress;
                        if (batteryPercent != null)
                        {
                            battery["bateria"] = Math.Min(100, Math.Max(0, Convert.ToInt32(batteryPercent)));
                        }
                        int? runMinutes = NormalizeBatteryRunTimeMinutes(batteryRuntime);
                        if (runMinutes.HasValue)
                        {
                            battery["autonomia"] = runMinutes.Value;
                        }
                        battery["status"] = statusStr;
                        return battery;
                    }
                }
            }
            catch { }

            return null;
        }
    }
}
