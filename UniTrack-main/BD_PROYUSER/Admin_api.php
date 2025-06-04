<?php
// Configura la cookie de sesión para desarrollo local
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
// Iniciar sesión en cada petición
session_start();

// Incluir el archivo de configuración de la conexión a la base de datos

function reportes()
{
    validateSession();
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
    return json_encode($data);
}
function reportesSalida()
{
    validateSession();
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
function createAdmin($data)
{ 
    if (empty($data) || !isset($data['nombres']) || !isset($data['apellidos']) || !isset($data['correo']) || !isset($data['codigo_admin']) || !isset($data['contrasena']) || !isset($data['edad']) || !isset($data['sexo'])) {
        http_response_code(400);
        return json_encode(['error' => 'Todos los campos son obligatorios']);
    }
    if (!preg_match('/@ucvvirtual\.edu\.pe$/', $data['correo'])) {
        http_response_code(400);
        return json_encode(['error' => 'El correo debe ser de la universidad']);
    }
    if (strlen($data['contrasena']) < 6) {
        http_response_code(400);
        return json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
    }
    $nombres = $data['nombres'];
    $apellidos = $data['apellidos'];
    $correo = $data['correo'];
    $codigo_admin = $data['codigo_admin'];
    $contrasena = $data['contrasena'];
    $edad = $data['edad'];
    $sexo = $data['sexo'];
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

        // Validar longitud máxima
        if (strlen($nombres) > 50 || strlen($apellidos) > 50) {
            http_response_code(400);
            return json_encode(["error" => "Nombre y apellido no deben superar 50 caracteres"]);
        }
        if (strlen($codigo_admin) > 20) {
            http_response_code(400);
            return json_encode(["error" => "El código de administrador no debe superar 20 caracteres"]);
        }
        if (strlen($correo) > 100) {
            http_response_code(400);
            return json_encode(["error" => "El correo no debe superar 100 caracteres"]);
        }
        if (strlen($contrasena) > 50) {
            http_response_code(400);
            return json_encode(["error" => "La contraseña no debe superar 50 caracteres"]);
        }
        if ($edad === false || $edad < 0 || $edad > 120) {
            http_response_code(400);
            return json_encode(["error" => "Edad no válida"]);
        }

        // Validar duplicados
        $sqlCheck = "SELECT idAdmin FROM administrador WHERE correo = ? OR codigo_admin = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ss", $correo, $codigo_admin);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            http_response_code(409);
            return json_encode(["error" => "El correo o código de administrador ya existe"]);
        }

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
        http_response_code(500);
        return json_encode(array("error" => $e->getMessage()));
    }
}


function updateAdmin($data)
{
    validateSession();
    if(empty($data) || !isset($data['id']) || !isset($data['nombres']) || !isset($data['apellidos']) || !isset($data['correo']) || !isset($data['codigo_admin'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID, nombres, apellidos, correo y código de administrador son obligatorios']);
        exit();
    }
    $id = $data['id'];
    $nombres = $data['nombres'];
    $apellidos = $data['apellidos'];
    $correo = $data['correo'];
    $codigo_estudiante = $data['codigo_estudiante'];
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
function deleteAdmin($data)
{
    validateSession();
    if(empty($data) || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID es obligatorio']);
        exit();
    }
    $id = $data['id'];
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
function getUserById($data)
{
    validateSession();
    
    if (!isset($data["id"])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario no proporcionado']);
        exit();
    }
    $id = $data["id"];
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
function loginAdmin($data)
{
    if(empty($data) || !isset($data['correo']) || !isset($data['contrasena'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Correo y contraseña son obligatorios']);
        exit();
    }
    $correo = $data['correo'];
    $contrasena = $data['contrasena'];
    global $conn;

    try {
        $correo = $data['correo'];
        $contrasena = $data['contrasena'];
        $sql = "SELECT idAdmin, nombres, apellidos, correo, codigo_admin, contrasena, edad, sexo FROM administrador WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($contrasena, $user['contrasena'])) {
                $_SESSION['idAdmin'] = $user['idAdmin']; // Guardar id en sesión
                unset($user['contrasena']);
                return json_encode($user);
            } else {
                return json_encode(array("error" => "Contraseña incorrecta"));
            }
        } else {
            return json_encode(array("error" => "Usuario no encontrado"));
        }
    } catch (Exception $e) {
        return json_encode(array("error" => $e->getMessage()));
    }
}

// Endpoint para cerrar sesión
function logoutUser()
{
    session_unset();
    session_destroy();
    echo json_encode(['message' => 'Sesión cerrada']);
    exit();
}
