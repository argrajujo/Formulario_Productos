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

// Procesar el formulario de registro de producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_producto'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];
    $imagen_larga = "";
    $imagen_miniatura = "";

    // Subir la imagen miniatura
    if (isset($_FILES['imagen_miniatura']) && $_FILES['imagen_miniatura']['error'] == 0) {
        $upload_dir = __DIR__ . "/uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $target_file = $upload_dir . basename($_FILES["imagen_miniatura"]["name"]);

        if (move_uploaded_file($_FILES["imagen_miniatura"]["tmp_name"], $target_file)) {
            $imagen_miniatura = "uploads/" . basename($_FILES["imagen_miniatura"]["name"]);
        } else {
            $feedback_message = "Error al subir la imagen miniatura.";
            $feedback_class = "alert-danger";
        }
    }

    // Insertar producto en la base de datos
    if (empty($feedback_message)) {
        $conn = conectarBD();
        $sql = "INSERT INTO productos (nombre, descripcion, precio, cantidad, imagen_larga, imagen_miniatura) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiss", $nombre, $descripcion, $precio, $cantidad, $imagen_larga, $imagen_miniatura);
        
        if ($stmt->execute()) {
            $feedback_message = "Producto registrado exitosamente";
            $feedback_class = "alert-success";
        } else {
            $feedback_message = "Error al registrar el producto: " . $conn->error;
        }
        $stmt->close();
        $conn->close();
    }
}

// Eliminar producto
if (isset($_GET['eliminar_id'])) {
    $conn = conectarBD();
    $producto_id = $_GET['eliminar_id'];
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header("Location: registro_producto.php");
    exit();
}

// Obtener productos registrados
function obtenerProductos() {
    $conn = conectarBD();
    $sql = "SELECT * FROM productos ORDER BY id DESC";
    $result = $conn->query($sql);
    $conn->close();
    return $result;
}

$productos = obtenerProductos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
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
                <a href="log_sesiones.php" class="btn" style="width: auto; margin-bottom: 0.5rem;">Ver Sesiones</a>
                <a href="logout.php" class="btn btn-danger" style="width: auto;">Cerrar Sesión</a>
            </div>
        </header>
        
        <h2>Formulario de Registro de Producto</h2>

        <?php if (isset($feedback_message)): ?>
            <div class="alert <?php echo $feedback_class ?? 'alert-info'; ?>"><?php echo $feedback_message; ?></div>
        <?php endif; ?>

        <form action="registro_producto.php" method="post" enctype="multipart/form-data">
            <label for="nombre">Nombre del producto</label>
            <input type="text" name="nombre" id="nombre" required>

            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" required></textarea>

            <label for="precio">Precio</label>
            <input type="number" step="0.01" name="precio" id="precio" required>

            <label for="cantidad">Cantidad</label>
            <input type="number" name="cantidad" id="cantidad" required>

            <label for="imagen_miniatura">Imagen miniatura</label>
            <input type="file" name="imagen_miniatura" id="imagen_miniatura" accept="image/*" required>

            <input type="submit" name="registrar_producto" value="Registrar Producto" class="btn">
        </form>

        <h2 style="margin-top: 3rem;">Productos Registrados</h2>
        <div class="table-container">
            <?php if ($productos->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Miniatura</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($producto = $productos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['id']); ?></td>
                                <td>
                                    <?php if (!empty($producto['imagen_miniatura']) && file_exists($producto['imagen_miniatura'])): ?>
                                        <img src="<?php echo htmlspecialchars($producto['imagen_miniatura']); ?>" alt="Miniatura" class="product-thumbnail">
                                    <?php else: ?>
                                        <span>Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                                <td><?php echo date("d/m/Y H:i", strtotime($producto['fecha_creacion'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn action-btn btn-success">Editar</a>
                                        <a href="?eliminar_id=<?php echo $producto['id']; ?>" class="btn action-btn btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 2rem;">No hay productos registrados.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>