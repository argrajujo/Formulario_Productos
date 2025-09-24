<?php
session_start();

// Verificar si el usuario está autenticado
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

if (isset($_GET['id'])) {
    $producto_id = $_GET['id'];
    $conn = conectarBD();

    // Procesar la actualización si el formulario fue enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_producto'])) {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $cantidad = $_POST['cantidad'];

        $sql_update = "UPDATE productos SET nombre=?, descripcion=?, precio=?, cantidad=? WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssdii", $nombre, $descripcion, $precio, $cantidad, $producto_id);
        
        if ($stmt_update->execute()) {
            header("Location: registro_producto.php");
            exit;
        } else {
            echo "Error al actualizar el producto.";
        }
        $stmt_update->close();
    }

    // Obtener el producto a editar
    $sql = "SELECT * FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();
    $stmt->close();
    
    if (!$producto) {
        header("Location: registro_producto.php");
        exit;
    }
    $conn->close();
} else {
    header("Location: registro_producto.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Editar Producto</h2>

        <form action="editar_producto.php?id=<?php echo $producto['id']; ?>" method="post">
            <label for="nombre">Nombre del producto</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>

            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>

            <label for="precio">Precio</label>
            <input type="number" step="0.01" name="precio" id="precio" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>

            <label for="cantidad">Cantidad</label>
            <input type="number" name="cantidad" id="cantidad" value="<?php echo htmlspecialchars($producto['cantidad']); ?>" required>

            <input type="submit" name="editar_producto" value="Actualizar Producto" class="btn">
        </form>
    </div>
</body>
</html>