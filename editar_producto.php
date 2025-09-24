<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $producto_id = $_GET['id'];

    // Conexión a la base de datos
    function conectarBD() {
        $host = "localhost";
        $dbuser = "root";
        $dbpass = "";
        $dbname = "tienda_producto"; // Usar la base de datos creada
        $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        } 
        return $conn;
    }

    // Obtener el producto a editar
    $conn = conectarBD();
    $sql = "SELECT * FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Si el producto no existe, redirigir al registro de productos
    if (!$producto) {
        header("Location: registro_producto.php");
        exit;
    }

    // Procesar la actualización
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_producto'])) {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $cantidad = $_POST['cantidad'];

        // Actualizar el producto
        $conn = conectarBD();
        $sql_update = "UPDATE productos SET nombre=?, descripcion=?, precio=?, cantidad=? WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssdis", $nombre, $descripcion, $precio, $cantidad, $producto_id);
        
        if ($stmt_update->execute()) {
            header("Location: registro_producto.php");
            exit;
        } else {
            echo "Error al actualizar el producto.";
        }
        $stmt_update->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="styles.css"> <!-- Vincula el archivo CSS aquí -->
</head>
<body>
    <h2>Editar Producto</h2>

    <form action="editar_producto.php?id=<?php echo $producto['id']; ?>" method="post">
        <label for="nombre">Nombre del producto</label><br>
        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required><br><br>

        <label for="descripcion">Descripción</label><br>
        <textarea name="descripcion" id="descripcion" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea><br><br>

        <label for="precio">Precio</label><br>
        <input type="number" name="precio" id="precio" value="<?php echo htmlspecialchars($producto['precio']); ?>" required><br><br>

        <label for="cantidad">Cantidad</label><br>
        <input type="number" name="cantidad" id="cantidad" value="<?php echo htmlspecialchars($producto['cantidad']); ?>" required><br><br>

        <input type="submit" name="editar_producto" value="Actualizar Producto">
    </form>
</body>
</html>
