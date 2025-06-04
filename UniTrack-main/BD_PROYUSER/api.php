<?php
// Cabeceras CORS para TODAS las respuestas
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejo de solicitud OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}


// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php'; // Dotenv
include_once 'config.php'; // Configuración de base de datos



// Función para enviar correo con PHPMailer
function enviarCorreo($correoDestino, $asunto, $cuerpo)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mixie.brighit01@gmail.com';
        $mail->Password = 'rnfi ybfp dzou xsgb'; // Asegúrate de usar una contraseña de aplicación, no la de tu cuenta
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('mixie.brighit01@gmail.com', 'Somos X');
        $mail->addAddress($correoDestino);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Función para obtener historial
function historial($data)
{
    if (empty($data) || !isset($data['idUsuario'])) {
        http_response_code(400);
        return json_encode(['error' => 'ID de usuario no proporcionado']);
    }
    $idUsuario = $data['idUsuario'];

    global $conn;

    $sql = "SELECT u.idUsuario, r.fecha, r.hora, r.nombre, r.email, r.modo
            FROM reportes r
            JOIN usuario u ON r.user_id = u.idUsuario 
            WHERE u.idUsuario = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    if ($data) {
        header('Content-Type: application/json');
        return json_encode($data);
    } else {
        return json_encode(['error' => 'No se encontraron reportes']);
    }
}


// Obtener usuario por correo
function CurrentUser($data)
{
    if (empty($data) || !isset($data['correo'])) {
        http_response_code(400);
        return json_encode(['error' => 'Correo no proporcionado']);
    }
    $correo = $data['correo'];
    global $conn;

    $sql = "SELECT idUsuario, nombres, apellidos, correo, codigo_estudiante, correoA, carrera, ciclo, edad, sexo 
            FROM usuario WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return json_encode($result->fetch_assoc());
    } else {
        return json_encode(["error" => "Usuario no encontrado"]);
    }
}

// Crear usuario
function createUser($data)
{
    if(empty($data) || !isset($data['nombres']) || !isset($data['apellidos']) || !isset($data['correo']) || !isset($data['codigo_estudiante']) || !isset($data['contrasena']) || !isset($data['correoA']) || !isset($data['carrera']) || !isset($data['ciclo']) || !isset($data['edad']) || !isset($data['sexo'])) {
        http_response_code(400);
        return json_encode(['error' => 'Todos los campos son obligatorios']);
    }
    $nombres = $data['nombres'];
    $apellidos = $data['apellidos'];
    $correo = $data['correo'];
    $codigo_estudiante = $data['codigo_estudiante'];
    $contrasena = $data['contrasena'];
    $correoA = $data['correoA'];
    $carrera = $data['carrera'];
    $ciclo = $data['ciclo'];
    $edad = $data['edad'];
    $sexo = $data['sexo'];
    global $conn;

    $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);

    $sql = "INSERT INTO usuario (nombres, apellidos, correo, codigo_estudiante, contrasena, correoA, carrera, ciclo, edad, sexo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $nombres, $apellidos, $correo, $codigo_estudiante, $hashedPassword, $correoA, $carrera, $ciclo, $edad, $sexo);

    if ($stmt->execute()) {
        return json_encode(["message" => "Usuario creado correctamente"]);
    } else {
        return json_encode(["error" => "Error al crear usuario"]);
    }
}

function checkSession()
{
    
    return json_encode(["active" => true]);
}
// Login usuario
function loginUser($data)
{
    if (empty($data) || !isset($data['correo']) || !isset($data['contrasena'])) {
        http_response_code(400);
        return json_encode(['error' => 'Correo y contraseña son obligatorios']);
    }
    $correo = $data['correo'];
    $contrasena = $data['contrasena'];
    global $conn;

    $sql = "SELECT idUsuario, nombres, apellidos, correo, codigo_estudiante, contrasena, correoA, carrera, ciclo, edad, sexo
            FROM usuario WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($contrasena, $user['contrasena'])) {
            unset($user['contrasena']);
            return json_encode(["success" => true, "user" => $user]);
        } else {
            return json_encode(["error" => "Contraseña incorrecta"]);
        }
    } else {
        return json_encode(["error" => "Usuario no encontrado"]);
    }
}

// Actualizar usuario
function updateUser($data)
{
    validateSession();
    if (empty($data) || !isset($data['id']) || !isset($data['nombres']) || !isset($data['apellidos']) || !isset($data['correo']) || !isset($data['codigo_estudiante'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID, nombres, apellidos, correo y código de estudiante son obligatorios']);
        exit();
    }
    $id = $data['id'];
    $nombres = $data['nombres'];
    $apellidos = $data['apellidos'];
    $correo = $data['correo'];
    $codigo_estudiante = $data['codigo_estudiante'];
    global $conn;

    $sql = "UPDATE usuario SET nombres = ?, apellidos = ?, correo = ?, codigo_estudiante = ? WHERE idusuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nombres, $apellidos, $correo, $codigo_estudiante, $id);

    if ($stmt->execute()) {
        return json_encode(["message" => "Usuario actualizado correctamente"]);
    } else {
        return json_encode(["error" => "Error al actualizar usuario"]);
    }
}

// Eliminar usuario
function deleteUser($data)
{
    validateSession();
    if (empty($data) || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID es obligatorio']);
        exit();
    }
    $id = $data['id'];
    global $conn;

    $sql = "DELETE FROM usuario WHERE idusuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        return json_encode(["message" => "Usuario eliminado correctamente"]);
    } else {
        return json_encode(["error" => "Error al eliminar usuario"]);
    }
}

// Enviar token de recuperación
function sendToken($correo)
{
    global $conn;

    $token = bin2hex(random_bytes(16)); // genera token de 32 caracteres

    $stmt = $conn->prepare("UPDATE usuario SET token_recuperacion = ? WHERE correo = ?");
    $stmt->bind_param("ss", $token, $correo);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $asunto = "Recuperación de contraseña";
        $cuerpo = "<p>Hola,</p><p>Tu token de recuperación es: <strong>$token</strong></p>";

        if (enviarCorreo($correo, $asunto, $cuerpo)) {
            echo json_encode(["message" => "Token enviado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo enviar el correo"]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Correo no encontrado"]);
    }
}

 