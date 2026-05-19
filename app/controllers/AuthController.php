<?php

class AuthController {
    
    public function login() {
        // Exibir a view de login
        require 'app/views/auth/login.php';
    }

    public function authenticate() {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        // Simulação estática para demonstração (na vida real usar DB)
        // O hash gerado em database.sql é '$2y$10$e.w3j3./J.xW0/kE0sB3/e3y9Zg2hA/vI6z9Xo/5fT4o9Xn7M.uWG' que equivale a 'admin123'
        
        // Exemplo prático focado em funcionar independente agora:
        if ($email === 'admin@infravision.local' && $senha === 'admin123') {
            $_SESSION['usuario_id'] = 1;
            $_SESSION['usuario_nome'] = 'Administrador';
            $_SESSION['usuario_nivel'] = 'admin';
            
            $base_path = '/infravision';
            header("Location: $base_path/dashboard");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos!";
            require 'app/views/auth/login.php';
        }
    }

    public function logout() {
        session_destroy();
        $base_path = '/infravision';
        header("Location: $base_path/login");
        exit;
    }
}
