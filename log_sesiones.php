<?php
session_start();

// Verificar si el usuario está autenticado, igual que en las otras páginas
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

// Conexión a la base de datos
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

// Obtener todos los registros de sesiones
function obtenerHistorialSesiones() {
    $conn = conectarBD();
    // Ordenamos por la fecha de login más reciente primero
    $sql = "SELECT username, session_id, login_time, logout_time FROM log_sistema ORDER BY login_time DESC";
    $result = $conn->query($sql);
    $conn->close();
    return $result;
}

$sesiones = obtenerHistorialSesiones();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Sesiones</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header class="welcome-header">
            <div>
                 <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                <p>
                    <?php 
                        if (isset($_SESSION['last_login_time'])) {
                            echo 'Última conexión: ' . date('d/m/Y H:i', strtotime($_SESSION['last_login_time']));
                        } else {
                            echo '¡Esta es tu primera conexión!';
                        }
                    ?>
                </p>
                <p>ID de Sesión: <strong><?php echo htmlspecialchars($_SESSION['session_id']); ?></strong></p>
            </div>
            <div>
                <a href="registro_producto.php" class="btn btn-success" style="width: auto; margin-bottom: 0.5rem;">Volver a Productos</a>
                <a href="logout.php" class="btn btn-danger" style="width: auto;">Cerrar Sesión</a>
            </div>
        </header>

        <h2>Historial de Sesiones de Usuarios</h2>

        <div class="table-container">
            <?php if ($sesiones->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>ID de Sesión</th>
                            <th>Inicio de Sesión</th>
                            <th>Cierre de Sesión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($sesion = $sesiones->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sesion['username']); ?></td>
                                <td><?php echo htmlspecialchars($sesion['session_id']); ?></td>
                                <td><?php echo date("d/m/Y H:i:s", strtotime($sesion['login_time'])); ?></td>
                                <td>
                                    <?php 
                                        // Muestra la fecha de cierre solo si no es nula
                                        if ($sesion['logout_time']) {
                                            echo date("d/m/Y H:i:s", strtotime($sesion['logout_time']));
                                        } else {
                                            echo '<em>Sesión activa</em>';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 2rem;">No hay registros de sesiones.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>