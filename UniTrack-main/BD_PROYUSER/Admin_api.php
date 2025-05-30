<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir el archivo de configuración de la conexión a la base de datos
include_once 'config.php';

function reportes()
{
    global $conn;

    $sql = "SELECT r.idReporte,  r.fecha, r.hora, r.nombre, r.email, r.modo
            FROM reportes r
            JOIN usuario u ON r.user_id = u.idUsuario where modo='entrada'";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
}
function reportesSalida()
{
    global $conn;

    $sql = "SELECT r.idReporte,  r.fecha, r.hora, r.nombre, r.email, r.modo
            FROM reportes r
            JOIN usuario u ON r.user_id = u.idUsuario where modo='salida'";

    $stmt = $conn->prepare($sql);
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




// Función para crear un nuevo admin
function createAdmin($nombres, $apellidos, $correo, $codigo_admin, $contrasena, $edad, $sexo)
{
    global $conn;

    try {
        // Validar y limpiar los datos de entrada
        $nombres = filter_var($nombres, FILTER_SANITIZE_STRING);
        $apellidos = filter_var($apellidos, FILTER_SANITIZE_STRING);
        $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);
        $codigo_admin = filter_var($codigo_admin, FILTER_SANITIZE_STRING);
        $edad = filter_var($edad, FILTER_SANITIZE_STRING);
        $sexo = filter_var($sexo, FILTER_SANITIZE_STRING);
        $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);


        // Preparar la consulta SQL para crear un nuevo usuario
        $sql = "INSERT INTO administrador (nombres, apellidos, correo, codigo_admin, contrasena, edad, sexo) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $nombres, $apellidos, $correo, $codigo_admin, $hashedPassword, $edad, $sexo);

        // Ejecutar la consulta y retornar el resultado
        if ($stmt->execute()) {
            return json_encode(array("message" => "Administrador creado correctamente"));
        } else {
            return json_encode(array("error" => "Error al crear Administrador"));
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir
        return json_encode(array("error" => $e->getMessage()));
    }
}


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

// Función para obtener un usuario por ID
function getUserById($id)
{
    global $conn;

    try {
        // Preparar la consulta SQL para obtener un usuario por ID
        $sql = "SELECT idusuario, nombres, apellidos, correo, codigo_estudiante FROM usuario WHERE idusuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Retornar el usuario en formato JSON
            return json_encode($result->fetch_assoc());
        } else {
            // Retornar un mensaje de error si no se encontró el usuario
            return json_encode(array("error" => "No se encontró el usuario"));
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir
        return json_encode(array("error" => $e->getMessage()));
    }
}

// Función para verificar usuario y contraseña admin
function loginUser($correo, $contrasena)
{
    global $conn;

    try {

        $sql = "SELECT idAdmin, nombres, apellidos, correo, codigo_admin, contrasena, edad, sexo FROM administrador WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Verificar la contraseña
            $user = $result->fetch_assoc();

            if (password_verify($contrasena, $user['contrasena'])) {
                unset($user['contrasena']); // No devolver la contraseña hash en la respuesta
                return json_encode($user);
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
}

// Verificar si la solicitud es un método GET


try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            echo getUserById($_GET['id']);
        } elseif (isset($_GET['action']) && $_GET['action'] === 'reportes') {
            reportes();
        } elseif (isset($_GET['action']) && $_GET['action'] === 'salidas') {
            reportesSalida();
        } else {
            echo getAllUsers();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (isset($data['action']) && $data['action'] === 'login') {
            // Validación solo para login
            if (empty($data['correo']) || empty($data['contrasena'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Correo y contraseña son obligatorios']);
                exit();
            }
            echo loginUser($data['correo'], $data['contrasena']);
        } else {
            // Validación para registro
            if (
                empty($data['nombres']) ||
                empty($data['apellidos']) ||
                empty($data['correo']) ||
                empty($data['codigo_admin']) ||
                empty($data['contrasena']) ||
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
            echo createAdmin($data['nombres'], $data['apellidos'], $data['correo'], $data['codigo_admin'], $data['contrasena'], $data['edad'], $data['sexo']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            echo updateUser($data['id'], $data['nombres'], $data['apellidos'], $data['correo'], $data['codigo_estudiante']);
        } else {
            echo json_encode(array("error" => "ID de usuario no especificado para actualizar"));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            echo deleteUser($data['id']);
        } else {
            echo json_encode(array("error" => "ID de usuario no especificado para eliminar"));
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("error" => $e->getMessage()));
}

$conn->close();
?>