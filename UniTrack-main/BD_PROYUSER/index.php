<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/vendor/autoload.php'; // Dotenv
include_once 'config.php'; // Configuración de base de datos


use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
include 'correouser.php';
include 'Admin_api.php';
include 'validation.php';
include 'api.php';
// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



// Routing
$data = json_decode(file_get_contents("php://input"), true);
$action = isset($data['action']) ? $data['action'] : '';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        switch ($_GET['action']) {
            case 'currentUser':
                echo CurrentUser($data);
                break;
            case 'logout':
                logoutUser();
                break;
            case 'getUserById':
                echo getUserById($data);
                break;
            case 'reportes':
                echo reportes();
                break;
            case 'salidas':
                reportesSalida();
                break;
            case 'checkSession':
                echo checkSession();
                break;
            default:
                http_response_code(400);
                echo json_encode(["success" => false, "error" => "Acción o datos inválidos"]);
                break;
        }
        break;
    case 'POST':
        switch ($action) {
            case 'historial':
                echo historial($data);
                break;
            case 'register':
                echo createUser($data);
                break;
            case 'register-admin':
                echo createAdmin($data);
                break;
            case 'login':
                echo loginUser($data);
                break;
            case 'login-admin':
                echo loginAdmin($data);
                break;
            case 'send-code':
                sendUserCode($data);
                break;
            case 'verify-code':
                verifyUserCode($data);
                break;
            case 'reset-password':
                resetUserPassword($data);
                break;
            default:
                http_response_code(400);
                echo json_encode(["success" => false, "error" => "Acción o datos inválidos"]);
                break;
        }
        break;
    case "PUT":
        switch ($action) {
            case "update-user":
                echo updateUser($data);
            case "update-admin":
                echo updateAdmin($data);
        }
        break;
    case "DELETE":
        switch ($action) {
            case "delete-user":
                echo deleteUser($data);
            case "delete-admin":
                echo deleteAdmin($data);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["success" => false, "error" => "Método no permitido"]);
        break;
}

$conn->close();