<?php
session_start();

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

// Si existe un session_id en el log, actualizamos la hora de logout
if (isset($_SESSION['session_id'])) {
    $conn = conectarBD();
    $sql = "UPDATE log_sistema SET logout_time = CURRENT_TIMESTAMP WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['session_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Destruimos la sesión
session_unset();
session_destroy();

// Redirigimos al login
header("Location: index.php");
exit;
?>