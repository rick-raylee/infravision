<?php

class Device {
    private $conn;
    private $table_name = "dispositivos";

    public $id;
    public $nome;
    public $ip;
    public $tipo;
    public $status;
    public $ultimo_check;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT id, nome, ip, tipo, status, ultimo_check FROM " . $this->table_name . " ORDER BY criado_em DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function countByStatus($status) {
        $this->atualizarStatusAutomatico();
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getAll() {
        $this->atualizarStatusAutomatico();
        $query = "SELECT id, nome as hostname, ip, tipo, status, ultimo_check FROM " . $this->table_name . " ORDER BY criado_em DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithMetrics() {
        $this->atualizarStatusAutomatico();
        $query = "SELECT d.id, d.nome as hostname, d.ip, d.tipo, d.status, d.ultimo_check,
                         (SELECT l.valor FROM leituras l 
                          JOIN sensores s ON l.sensor_id = s.id 
                          WHERE s.dispositivo_id = d.id AND s.tipo = 'cpu' 
                          ORDER BY l.data_leitura DESC LIMIT 1) as cpu_atual,
                         (SELECT l.valor FROM leituras l 
                          JOIN sensores s ON l.sensor_id = s.id 
                          WHERE s.dispositivo_id = d.id AND s.tipo = 'ram' AND s.nome = 'RAM Livre (MB)' 
                          ORDER BY l.data_leitura DESC LIMIT 1) as ram_livre,
                         (SELECT l.valor FROM leituras l 
                          JOIN sensores s ON l.sensor_id = s.id 
                          WHERE s.dispositivo_id = d.id AND s.tipo = 'ram' AND s.nome = 'RAM Total (MB)' 
                          ORDER BY l.data_leitura DESC LIMIT 1) as ram_total
                  FROM dispositivos d
                  ORDER BY d.criado_em DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $ram_livre = $row['ram_livre'] !== null ? (float)$row['ram_livre'] : null;
            $ram_total = $row['ram_total'] !== null ? (float)$row['ram_total'] : null;

            if ($ram_livre !== null && $ram_total !== null && $ram_total > 0) {
                $row['ram_atual'] = (($ram_total - $ram_livre) / $ram_total) * 100;
            } else {
                $row['ram_atual'] = null;
            }
        }
        return $rows;
    }

    private function atualizarStatusAutomatico() {
        try {
            // Se passar de 2 minutos sem check-in, muda de Online para Inativo
            $queryInativo = "UPDATE " . $this->table_name . " 
                             SET status = 'Inativo' 
                             WHERE ultimo_check < DATE_SUB(NOW(), INTERVAL 2 MINUTE) 
                             AND status = 'Online'";
            $stmt1 = $this->conn->prepare($queryInativo);
            $stmt1->execute();

            // Se passar de 5 minutos sem check-in, muda para Offline (Desligado)
            $queryOffline = "UPDATE " . $this->table_name . " 
                             SET status = 'Offline' 
                             WHERE ultimo_check < DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                             AND status != 'Offline'";
            $stmt2 = $this->conn->prepare($queryOffline);
            $stmt2->execute();
        } catch (Exception $e) {
            // Erros silenciosos para não quebrar a listagem
            error_log("Erro ao atualizar status automatico: " . $e->getMessage());
        }
    }
}

