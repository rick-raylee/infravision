<?php

class NetworkController {

    private static function agruparConexoesPorOrigem(array $conexoes): array {
        $grupos = [];

        foreach ($conexoes as $c) {
            $origem = trim((string)($c['origem'] ?? ''));
            $ip = trim((string)($c['ip_origem'] ?? ''));
            $chave = $origem . '|' . $ip;

            if (!isset($grupos[$chave])) {
                $grupos[$chave] = [
                    'origem' => $origem,
                    'ip_origem' => $ip,
                    'total' => 0,
                    'destinos' => [],
                    'servicos' => [],
                    'latencia_max' => 0,
                ];
            }

            $grupos[$chave]['total']++;
            $dest = trim((string)($c['destino'] ?? ''));
            if ($dest !== '' && !in_array($dest, $grupos[$chave]['destinos'], true)) {
                $grupos[$chave]['destinos'][] = $dest;
            }
            $svc = trim((string)($c['servico'] ?? ''));
            if ($svc !== '' && !in_array($svc, $grupos[$chave]['servicos'], true)) {
                $grupos[$chave]['servicos'][] = $svc;
            }
            $grupos[$chave]['latencia_max'] = max($grupos[$chave]['latencia_max'], (int)($c['latencia'] ?? 0));
        }

        $lista = array_values($grupos);
        usort($lista, fn($a, $b) => $b['total'] <=> $a['total']);
        return $lista;
    }
    
    public function index() {
        $base_path = getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision');

        require_once 'config/database.php';
        $hosts_rede = [];
        $dispositivos_rede = [];
        $alertas_ips = [];
        $servico_stats = [];

        $db = (new Database())->getConnection();
        if ($db) {
            $stmt = $db->prepare("SELECT origem, ip_origem, destino, servico, latencia, carga
                                  FROM conexoes ORDER BY id DESC LIMIT 200");
            $stmt->execute();
            $hosts_rede = self::agruparConexoesPorOrigem($stmt->fetchAll(PDO::FETCH_ASSOC));

            $stmtDev = $db->prepare("SELECT nome, ip, tipo, status, ultimo_check
                                     FROM dispositivos
                                     WHERE tipo IN ('switch', 'roteador', 'firewall')
                                     ORDER BY nome");
            $stmtDev->execute();
            $dispositivos_rede = $stmtDev->fetchAll(PDO::FETCH_ASSOC);

            $stmtAlert = $db->prepare("SELECT a.mensagem, a.severidade, d.nome, d.ip
                                       FROM alertas a
                                       JOIN dispositivos d ON d.id = a.dispositivo_id
                                       WHERE a.status = 'ativo'
                                       ORDER BY a.criado_em DESC LIMIT 5");
            $stmtAlert->execute();
            $alertas_ips = $stmtAlert->fetchAll(PDO::FETCH_ASSOC);

            $stmtSvc = $db->query("SELECT servico, COUNT(*) AS total FROM conexoes GROUP BY servico ORDER BY total DESC LIMIT 4");
            $servico_stats = $stmtSvc->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require 'app/views/layout/header.php';
        require 'app/views/network/index.php';
        require 'app/views/layout/footer.php';
    }
}
