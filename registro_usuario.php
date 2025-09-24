<?php
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

// Registrar un nuevo usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_usuario'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Por favor ingrese un nombre de usuario y una contraseña.";
    } else {
        $conn = conectarBD();
        $sql_check = "SELECT id FROM login_user WHERE username = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "El nombre de usuario ya está registrado. Por favor elige otro.";
        } else {
            // Insertar el nuevo usuario
            $sql = "INSERT INTO login_user (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);
            if ($stmt->execute()) {
                header("Location: index.php");
                exit();
            } else {
                $error = "Error al registrar el usuario.";
            }
            $stmt->close();
        }
        $stmt_check->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Registrar Nuevo Usuario</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="registro_usuario.php" method="post">
            <label for="username">Nombre de Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" name="registrar_usuario" value="Registrar Usuario" class="btn">
        </form>

        <p style="text-align: center; margin-top: 1.5rem;">
            ¿Ya tienes cuenta? <a href="index.php">Iniciar sesión</a>
        </p>
    </div>
</body>
</html>