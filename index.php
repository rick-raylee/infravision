<?php
session_start();

// Carregar Configurações
require_once 'config/database.php';

// Roteador super simples para o MVC
$request = $_SERVER['REQUEST_URI'];
$base_path = '/infravision'; // Ajuste conforme o Alias/DocumentRoot do seu servidor
$path = str_replace($base_path, '', $request);
$path = explode('?', $path)[0]; // Remover query string

// Rotas da API
if (strpos($path, '/api/') === 0) {
    $api_route = str_replace('/api/', '', $path);
    $api_file = 'api/' . $api_route . '.php';
    if (file_exists($api_file)) {
        require $api_file;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(["erro" => "Endpoint da API não encontrado"]);
    }
    exit;
}

// Controle de Acesso e Rotas Web
if (!isset($_SESSION['usuario_id']) && $path !== '/login') {
    header("Location: $base_path/login");
    exit;
}

switch ($path) {
    case '':
    case '/':
    case '/dashboard':
        require 'app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;
        
    case '/login':
        require 'app/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->authenticate();
        } else {
            $controller->login();
        }
        break;
        
    case '/report/inventory':
        require 'app/controllers/ReportController.php';
        $controller = new ReportController();
        $controller->inventory();
        break;

    case '/logout':
        require 'app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case '/network':
        require 'app/controllers/NetworkController.php';
        $controller = new NetworkController();
        $controller->index();
        break;

    case '/servers':
        require 'app/controllers/ServerController.php';
        $controller = new ServerController();
        $controller->index();
        break;

    case '/server/details':
        require 'app/controllers/ServerController.php';
        $controller = new ServerController();
        $controller->details();
        break;

    case '/alerts':
        require 'app/controllers/AlertController.php';
        $controller = new AlertController();
        $controller->index();
        break;

    case '/alert/acknowledge':
        require 'app/controllers/AlertController.php';
        $controller = new AlertController();
        $controller->acknowledge();
        break;

    case '/alert/test':
        require 'app/controllers/AlertController.php';
        $controller = new AlertController();
        $controller->generateTest();
        break;

    case '/traffic':
        require 'app/controllers/TrafficController.php';
        $controller = new TrafficController();
        $controller->index();
        break;

    case '/device/create':
        require 'app/controllers/DeviceController.php';
        $controller = new DeviceController();
        $controller->create();
        break;

    case '/device/store':
        require 'app/controllers/DeviceController.php';
        $controller = new DeviceController();
        $controller->store();
        break;

    case '/users':
        require 'app/controllers/UserController.php';
        $controller = new UserController();
        $controller->index();
        break;

    case '/user/create':
        require 'app/controllers/UserController.php';
        $controller = new UserController();
        $controller->create();
        break;

    case '/user/store':
        require 'app/controllers/UserController.php';
        $controller = new UserController();
        $controller->store();
        break;

    case '/user/edit':
        require 'app/controllers/UserController.php';
        $controller = new UserController();
        $controller->edit();
        break;

    case '/user/update':
        require 'app/controllers/UserController.php';
        $controller = new UserController();
        $controller->update();
        break;

    case '/user/delete':
        require 'app/controllers/UserController.php';
        $controller = new UserController();
        $controller->delete();
        break;

    case '/virtualization':
        require 'app/controllers/VirtualizationController.php';
        $controller = new VirtualizationController();
        $controller->index();
        break;

    case '/rules':
        require 'app/controllers/RuleController.php';
        $controller = new RuleController();
        $controller->index();
        break;

    case '/settings':
        require 'app/controllers/SettingsController.php';
        $controller = new SettingsController();
        $controller->index();
        break;

    case '/logcenter':
        require 'app/controllers/LogCenterController.php';
        $controller = new LogCenterController();
        $controller->index();
        break;

    case '/discovery':
        require 'app/controllers/DiscoveryController.php';
        $controller = new DiscoveryController();
        $controller->index();
        break;

    case '/services':
        require 'app/controllers/ServiceMonitorController.php';
        $controller = new ServiceMonitorController();
        $controller->index();
        break;

    case '/alert-contacts':
        require 'app/controllers/AlertContactController.php';
        $controller = new AlertContactController();
        $controller->index();
        break;

    case '/alert-contact/create':
        require 'app/controllers/AlertContactController.php';
        $controller = new AlertContactController();
        $controller->create();
        break;

    case '/alert-contact/store':
        require 'app/controllers/AlertContactController.php';
        $controller = new AlertContactController();
        $controller->store();
        break;

    default:
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        break;
}
