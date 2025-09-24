<?php
session_start();

// Mostrar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Establece la conexión a la base de datos
function conectarBD() {
    $host = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "tienda_producto";
    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    return $conn;
}

// Función para auditar el login
function auditar_login($session_id, $username) {
    $conn = conectarBD();
    $sql = "INSERT INTO log_sistema (session_id, username) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $session_id, $username);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Procesar el formulario de login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Por favor ingrese usuario y contraseña";
    } else {
        $conn = conectarBD();
        $sql = "SELECT id, username, password FROM login_user WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verificar la contraseña
            if ($password === $user['password']) {
                // Obtener la última fecha de conexión ANTES de registrar la nueva
                $sql_last_login = "SELECT MAX(login_time) AS last_login FROM log_sistema WHERE username = ?";
                $stmt_last_login = $conn->prepare($sql_last_login);
                $stmt_last_login->bind_param("s", $user['username']);
                $stmt_last_login->execute();
                $result_last_login = $stmt_last_login->get_result()->fetch_assoc();
                $_SESSION['last_login_time'] = $result_last_login['last_login'];
                $stmt_last_login->close();

                // Establecer las variables de sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['loggedin'] = true;

                // Auditar el NUEVO inicio de sesión
                $session_id = bin2hex(random_bytes(16));
                auditar_login($session_id, $user['username']);
                $_SESSION['session_id'] = $session_id;

                // Redirigir al panel de administración
                header("Location: registro_producto.php");
                exit;
            } else {
                $error = "Usuario o contraseña incorrecta";
            }
        } else {
            $error = "Usuario o contraseña incorrecta";
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="index.php" method="post">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" name="login" value="Iniciar Sesión" class="btn">
        </form>

        <p style="text-align: center; margin-top: 1.5rem;">
            Si no tienes cuenta... <a href="registro_usuario.php">Regístrate aquí</a>
        </p>
    </div>
</body>
</html>