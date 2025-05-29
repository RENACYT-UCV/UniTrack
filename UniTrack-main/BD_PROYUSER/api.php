<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type,  Authorization");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir el archivo de configuración de la conexión a la base de datos
include_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';



// Función para enviar correos electrónicos
function enviarCorreo($correoDestino, $asunto, $cuerpo)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mixie.brighit01@gmail.com';
        $mail->Password = 'rnfi ybfp dzou xsgb';
        $mail->SMTPSecure =  PHPMailer::ENCRYPTION_STARTTLS;
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


function historial($idUsuario)
{
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

    header('Content-Type: application/json');
    echo json_encode($data);
}


// Función para obtener todos los usuarios
function getAllUsers()
{
    global $conn;

    try {
        // Preparar la consulta SQL para obtener todos los usuarios
        $sql = "SELECT idusuario, nombres, apellidos, correo, codigo_estudiante FROM usuario";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Retornar los usuarios en formato JSON
            return json_encode($result->fetch_all(MYSQLI_ASSOC));
        } else {
            // Retornar un mensaje de error si no hay usuarios
            return json_encode(array("error" => "No se encontraron usuarios"));
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir
        return json_encode(array("error" => $e->getMessage()));
    }
}

// Función para obtener un usuario por ID
function CurrentUser($correo)
{
    global $conn;

    try {
        // Preparar la consulta SQL para obtener el usuario por correo
        $sql = "SELECT idUsuario, nombres, apellidos, correo, codigo_estudiante, correoA, carrera, ciclo, edad, sexo FROM usuario WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Devolver los datos del usuario
            $user = $result->fetch_assoc();
            return json_encode($user);
        } else {
            return json_encode(array("error" => "Usuario no encontrado"));
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir
        return json_encode(array("error" => $e->getMessage()));
    }
}

// Función para crear un nuevo usuario
function createUser($nombres, $apellidos, $correo, $codigo_estudiante, $contrasena, $correoA, $carrera, $ciclo, $edad, $sexo)
{
    global $conn;

    try {
        // Validar y limpiar los datos de entrada
        $nombres = filter_var($nombres, FILTER_SANITIZE_STRING);
        $apellidos = filter_var($apellidos, FILTER_SANITIZE_STRING);
        $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);
        $codigo_estudiante = filter_var($codigo_estudiante, FILTER_SANITIZE_STRING);
        $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);
        $correoA = filter_var($correoA, FILTER_SANITIZE_STRING);
        $carrera = filter_var($carrera, FILTER_SANITIZE_STRING);
        $ciclo = filter_var($ciclo, FILTER_SANITIZE_STRING);
        $edad = filter_var($edad, FILTER_SANITIZE_STRING);
        $sexo = filter_var($sexo, FILTER_SANITIZE_STRING);



        // Preparar la consulta SQL para crear un nuevo usuario
        $sql = "INSERT INTO usuario (nombres, apellidos, correo, codigo_estudiante, contrasena, correoA, carrera, ciclo, edad, sexo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $nombres, $apellidos, $correo, $codigo_estudiante, $hashedPassword, $correoA, $carrera, $ciclo, $edad, $sexo);

        // Ejecutar la consulta y retornar el resultado
        if ($stmt->execute()) {
            return json_encode(array("message" => "Usuario creado correctamente"));
        } else {
            return json_encode(array("error" => "Error al crear usuario"));
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir
        return json_encode(array("error" => $e->getMessage()));
    }
}

// Función para actualizar un usuario por ID
function updateUser($id, $nombres, $apellidos, $correo, $codigo_estudiante)
{
    global $conn;

    try {
        // Validar y limpiar los datos de entrada
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $nombres = filter_var($nombres, FILTER_SANITIZE_STRING);
        $apellidos = filter_var($apellidos, FILTER_SANITIZE_STRING);
        $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);
        $codigo_estudiante = filter_var($codigo_estudiante, FILTER_SANITIZE_STRING);

        // Preparar la consulta SQL para actualizar un usuario por ID
        $sql = "UPDATE usuario SET nombres = ?, apellidos = ?, correo = ?, codigo_estudiante = ? WHERE idusuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nombres, $apellidos, $correo, $codigo_estudiante, $id);

        // Ejecutar la consulta y retornar el resultado
        if ($stmt->execute()) {
            return json_encode(array("message" => "Usuario actualizado correctamente"));
        } else {
            return json_encode(array("error" => "Error al actualizar usuario"));
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir
        return json_encode(array("error" => $e->getMessage()));
    }
}

// Función para eliminar un usuario por ID
function deleteUser($id)
{
    global $conn;

    try {
        // Validar y limpiar los datos de entrada
        $id = filter_var($id, FILTER_VALIDATE_INT);

        // Preparar la consulta SQL para eliminar un usuario por ID
        $sql = "DELETE FROM usuario WHERE idusuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        // Ejecutar la consulta y retornar el resultado
        if ($stmt->execute()) {
            return json_encode(array("message" => "Usuario eliminado correctamente"));
        } else {
            return json_encode(array("error" => "Error al eliminar usuario"));
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir
        return json_encode(array("error" => $e->getMessage()));
    }
}




// Función para verificar usuario y contraseña
function loginUser($correo, $contrasena)
{
    global $conn;

    try {
        // Preparar la consulta SQL para obtener el usuario por correo
        $sql = "SELECT idUsuario, nombres, apellidos, correo, codigo_estudiante, contrasena, correoA, carrera, ciclo, edad, sexo FROM usuario WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($contrasena, $user['contrasena'])) {
                unset($user['contrasena']);
                return json_encode(array("success" => true, "user" => $user));
            } else {
                return json_encode(array("error" => "Contraseña incorrecta"));
            }
        } else {
            return json_encode(array("error" => "Usuario no encontrado"));
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir
        return json_encode(array("error" => $e->getMessage()));
    }
    global $user;
}
// Función para verificar usuario y contraseña admin


try {
    // Definir los endpoints de la API
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['action']) && $_GET['action'] === 'currentUser' && isset($_GET['correo'])) {
            // Obtener los datos del usuario actual por correo
            echo CurrentUser($_GET['correo']);
        } elseif (isset($_GET['action']) && $_GET['action'] === 'historial' && isset($_GET['idUsuario'])) {
            historial($_GET['idUsuario']);
        } else {
            // Obtener todos los usuarios
            echo getAllUsers();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['action']) && $data['action'] === 'login') {
            // Login de usuario
            echo loginUser($data['correo'], $data['contrasena']);
        } else {
            // Validaciones básicas para registro de usuario
            if (
                empty($data['nombres']) ||
                empty($data['apellidos']) ||
                empty($data['correo']) ||
                empty($data['codigo_estudiante']) ||
                empty($data['contrasena']) ||
                empty($data['correoA']) ||
                empty($data['carrera']) ||
                empty($data['ciclo']) ||
                empty($data['edad']) ||
                empty($data['sexo'])
            ) {
                http_response_code(400);
                echo json_encode(['error' => 'Todos los campos son obligatorios']);
                exit();
            }
            if (!preg_match('/@ucvvirtual\.edu\.pe$/', $data['correo'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El correo debe ser de la universidad']);
                exit();
            }
            if (strlen($data['contrasena']) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
                exit();
            }
            // Crear un nuevo usuario
            echo createUser(
                $data['nombres'],
                $data['apellidos'],
                $data['correo'],
                $data['codigo_estudiante'],
                $data['contrasena'],
                $data['correoA'],
                $data['carrera'],
                $data['ciclo'],
                $data['edad'],
                $data['sexo']
            );
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        // Actualizar un usuario
        if (isset($data['id'])) {
            echo updateUser($data['id'], $data['nombres'], $data['apellidos'], $data['correo'], $data['codigo_estudiante']);
        } else {
            echo json_encode(array("error" => "ID de usuario no especificado para actualizar"));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        // Eliminar un usuario
        if (isset($data['id'])) {
            echo deleteUser($data['id']);
        } else {
            echo json_encode(array("error" => "ID de usuario no especificado para eliminar"));
        }
    }
} catch (Exception $e) {
    // Manejar la excepción y retornar un mensaje de error en formato JSON
    http_response_code(500);
    echo json_encode(array("error" => $e->getMessage()));
}
