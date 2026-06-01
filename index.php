<?php
session_start();

// Carregar Configurações
require_once 'config/database.php';
$database = new Database(); // Carrega variáveis de ambiente globais do .env


// Roteador super simples para o MVC
$request = $_SERVER['REQUEST_URI'];
define('BASE_PATH', getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision'));
$base_path = BASE_PATH;
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

    case '/ups':
        require 'app/controllers/UpsController.php';
        $controller = new UpsController();
        $controller->index();
        break;

    case '/computers':
        require 'app/controllers/ComputerController.php';
        $controller = new ComputerController();
        $controller->index();
        break;

    case '/computer/update-peripherals':
        require 'app/controllers/ComputerController.php';
        $controller = new ComputerController();
        $controller->updatePeripherals();
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

    case '/device/edit':
        require 'app/controllers/DeviceController.php';
        $controller = new DeviceController();
        $controller->edit();
        break;

    case '/device/update':
        require 'app/controllers/DeviceController.php';
        $controller = new DeviceController();
        $controller->update();
        break;

    case '/device/delete':
        require 'app/controllers/DeviceController.php';
        $controller = new DeviceController();
        $controller->delete();
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->save();
        } else {
            $controller->index();
        }
        break;

    case '/logcenter':
        require 'app/controllers/LogCenterController.php';
        $controller = new LogCenterController();
        $controller->index();
        break;

    case '/ai-analyst':
        require 'app/controllers/AiAnalystController.php';
        $controller = new AiAnalystController();
        $controller->index();
        break;

    case '/ai-analyst/chat':
        require 'app/controllers/AiAnalystController.php';
        $controller = new AiAnalystController();
        $controller->chat();
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

    case '/services/store':
        require 'app/controllers/ServiceMonitorController.php';
        $controller = new ServiceMonitorController();
        $controller->store();
        break;

    case '/services/delete':
        require 'app/controllers/ServiceMonitorController.php';
        $controller = new ServiceMonitorController();
        $controller->delete();
        break;

    case '/services/update':
        require 'app/controllers/ServiceMonitorController.php';
        $controller = new ServiceMonitorController();
        $controller->update();
        break;

    case '/services/email/store':
        require 'app/controllers/ServiceMonitorController.php';
        $controller = new ServiceMonitorController();
        $controller->email_store();
        break;

    case '/services/email/delete':
        require 'app/controllers/ServiceMonitorController.php';
        $controller = new ServiceMonitorController();
        $controller->email_delete();
        break;

    case '/services/email/update':
        require 'app/controllers/ServiceMonitorController.php';
        $controller = new ServiceMonitorController();
        $controller->email_update();
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

    case '/alert-contact/edit':
        require 'app/controllers/AlertContactController.php';
        $controller = new AlertContactController();
        $controller->edit();
        break;

    case '/alert-contact/update':
        require 'app/controllers/AlertContactController.php';
        $controller = new AlertContactController();
        $controller->update();
        break;

    case '/alert-contact/delete':
        require 'app/controllers/AlertContactController.php';
        $controller = new AlertContactController();
        $controller->delete();
        break;


    default:
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        break;
}
