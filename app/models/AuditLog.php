<?php

class AuditLog {
    /**
     * Registra um evento de auditoria no banco de dados de forma silenciosa e segura.
     *
     * @param PDO|null $db Conexão com o banco de dados
     * @param int|null $usuario_id ID do usuário logado (ou null para ações do sistema/agente)
     * @param string $acao Título ou descrição da ação
     * @param string|null $detalhes Informações detalhadas da ação
     * @return bool Retorna true em caso de sucesso, false caso contrário
     */
    public static function write($db, $usuario_id, $acao, $detalhes = null) {
        if (!$db) {
            return false;
        }

        try {
            $ip_origem = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            
            $query = "INSERT INTO logs (usuario_id, acao, detalhes, ip_origem) 
                      VALUES (:usuario_id, :acao, :detalhes, :ip_origem)";
            $stmt = $db->prepare($query);
            
            // Permitir usuario_id ser nulo na inserção (para ações do sistema ou agentes)
            if ($usuario_id === null) {
                $stmt->bindValue(':usuario_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':usuario_id', (int)$usuario_id, PDO::PARAM_INT);
            }
            
            $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
            
            if ($detalhes === null) {
                $stmt->bindValue(':detalhes', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':detalhes', $detalhes, PDO::PARAM_STR);
            }
            
            $stmt->bindValue(':ip_origem', $ip_origem, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            // Falha silenciosa para garantir que erros de log não interrompam o fluxo principal
            return false;
        }
    }
}
