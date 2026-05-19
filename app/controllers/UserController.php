<?php

class UserController {
    
    public function index() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: /infravision/dashboard");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, nome, email, nivel, criado_em FROM usuarios ORDER BY nome ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $base_path = '/infravision';
        $current_path = '/users';
        require 'app/views/layout/header.php';
        require 'app/views/user/index.php';
        require 'app/views/layout/footer.php';
    }

    public function create() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: /infravision/dashboard");
            exit;
        }

        $base_path = '/infravision';
        $current_path = '/users';
        require 'app/views/layout/header.php';
        require 'app/views/user/create.php';
        require 'app/views/layout/footer.php';
    }

    public function store() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = password_hash($_POST['senha'] ?? '', PASSWORD_DEFAULT);
        $nivel = $_POST['nivel'] ?? 'visitante';

        $query = "INSERT INTO usuarios (nome, email, senha, nivel) VALUES (:nome, :email, :senha, :nivel)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':nivel', $nivel);

        if ($stmt->execute()) {
            header("Location: /infravision/users");
        } else {
            echo "Erro ao cadastrar usuário.";
        }
    }

    public function edit() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            header("Location: /infravision/dashboard");
            exit;
        }

        $id = $_GET['id'] ?? 0;
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, nome, email, nivel FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            header("Location: /infravision/users");
            exit;
        }

        $base_path = '/infravision';
        $current_path = '/users';
        require 'app/views/layout/header.php';
        require 'app/views/user/edit.php';
        require 'app/views/layout/footer.php';
    }

    public function update() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $id = $_POST['id'] ?? 0;
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $nivel = $_POST['nivel'] ?? 'visitante';

        if (!empty($_POST['senha'])) {
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, nivel = :nivel WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':senha', $senha);
        } else {
            $query = "UPDATE usuarios SET nome = :nome, email = :email, nivel = :nivel WHERE id = :id";
            $stmt = $db->prepare($query);
        }

        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':nivel', $nivel);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: /infravision/users");
        } else {
            echo "Erro ao atualizar usuário.";
        }
    }

    public function delete() {
        if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
            exit("Acesso negado");
        }

        $id = $_GET['id'] ?? 0;

        // Impedir que o usuário delete a si mesmo
        if ($id == ($_SESSION['usuario_id'] ?? 0)) {
            echo "Você não pode excluir a sua própria conta.";
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: /infravision/users");
        } else {
            echo "Erro ao excluir usuário.";
        }
    }
}
