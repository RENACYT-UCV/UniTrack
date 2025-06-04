<?php
require_once __DIR__ . '/vendor/autoload.php'; // Dotenv
include_once 'config.php'; // Configuración de base de datos

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Verificar conexión
if (!$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "No hay conexión a la base de datos"]);
    exit();
}

// Verificar variables SMTP
if (empty($_ENV['SMTP_HOST']) || empty($_ENV['SMTP_USER']) || empty($_ENV['SMTP_PASS'])) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Variables SMTP no cargadas"]);
    exit();
}

// Obtener ID del usuario por correo
function getUserIdByEmail($email) {
    global $conn;
    $email = trim(strtolower($email));
    $sql = "SELECT idUsuario FROM usuario WHERE LOWER(correo) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['idUsuario'];
    }
    return null;
}

// Enviar código
function sendUserCode($data) {

    if (empty($data) || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Correo no proporcionado']);
        exit();
    }
    $email = $data['email'];
    global $conn;

    $userId = getUserIdByEmail($email);
    echo $userId;
    if (!$userId) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Correo no registrado"]);
        return;
    }

    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO verificacion_codigo (id_usuario, codigo, intentos, usado) VALUES (?, ?, 0, FALSE)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $code);

    if ($stmt->execute()) {
        // Enviar correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['SMTP_USER'], 'Soporte - Tu App');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Código de verificación';
            $mail->Body = "<p>Tu código de verificación es: <strong>$code</strong></p><p>Este código es válido por 15 minutos.</p>";

            $mail->send();
            echo json_encode(["success" => true, "message" => "Código enviado al correo"]);
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Error al enviar el correo"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al guardar el código"]);
        return;
    }
}

// Verificar código
function verifyUserCode($data) {

    if (empty($data) || !isset($data['code'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Código no proporcionado']);
        exit();
    }
    $code = $data['code'];
    global $conn;
    $sql = "SELECT * FROM verificacion_codigo WHERE codigo = ? AND usado = 0 AND fecha_creacion >= (NOW() - INTERVAL 15 MINUTE)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['intentos'] >= 5) {
            http_response_code(429);
            echo json_encode(["success" => false, "error" => "Demasiados intentos. Solicita un nuevo código."]);
            return;
        }

        // Resetear intentos
        $sqlReset = "UPDATE verificacion_codigo SET intentos = 0 WHERE codigo = ?";
        $stmtReset = $conn->prepare($sqlReset);
        $stmtReset->bind_param("s", $code);
        $stmtReset->execute();

        echo json_encode(["success" => true, "id_user" => $row['id_usuario']]);
        return;
    } else {
        // Incrementar intentos
        $sqlUpdate = "UPDATE verificacion_codigo SET intentos = intentos + 1 WHERE codigo = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("s", $code);
        $stmtUpdate->execute();

        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Código inválido o expirado"]);
        return;
    }
}

// Restablecer contraseña
function resetUserPassword($data) {
    if(empty($data) || !isset($data['code']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Código y contraseña son obligatorios']);
        exit();
    }
    global $conn;
    $code = $data['code'];
    $password = $data['password'];
    $sql = "SELECT id_usuario FROM verificacion_codigo WHERE codigo = ? AND usado = 0 AND fecha_creacion >= (NOW() - INTERVAL 15 MINUTE)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $userId = $row['id_usuario'];
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sqlUpdate = "UPDATE usuario SET contrasena = ? WHERE idUsuario = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $hash, $userId);
        $stmtUpdate->execute();

        if ($stmtUpdate->affected_rows > 0) {
            $sqlMark = "UPDATE verificacion_codigo SET usado = 1 WHERE codigo = ?";
            $stmtMark = $conn->prepare($sqlMark);
            $stmtMark->bind_param("s", $code);
            $stmtMark->execute();

            echo json_encode(["success" => true, "message" => "Contraseña actualizada"]);
            return;
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "No se pudo actualizar la contraseña"]);
            return;
        }
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Código inválido o expirado"]);
        return;
    }
}