<?php

class Alert {
    private $conn;
    private $table_name = "alertas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function acknowledge($id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'reconhecido' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT a.*, d.nome as dispositivo_nome 
                  FROM " . $this->table_name . " a 
                  JOIN dispositivos d ON a.dispositivo_id = d.id 
                  ORDER BY a.criado_em DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
