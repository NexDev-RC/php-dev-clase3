<?php
session_start();

// --- 1. VALIDACIÓN DE FUNCIONES POR ROL ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'Vendedor') {
    http_response_code(403);
    include 'includes/header.php';
    echo "
    <div class='container my-5 text-center'>
        <div class='alert alert-danger shadow p-4'>
            <i class='bi bi-shield-lock-fill display-1 text-danger'></i>
            <h2 class='mt-3'>403 - Acceso Denegado</h2>
            <p class='lead'>No tienes permisos de <strong>Vendedor</strong> para gestionar los productos.</p>
            <a href='index.php' class='btn btn-primary mt-2'>Volver al Inicio</a>
        </div>
    </div>";
    include 'includes/footer.php';
    exit;
}

require_once 'config/db.php';

$mensaje = '';
$tipo_alerta = 'danger';

// --- 2. PROCESAMIENTO DE ACCIONES (CREAR, EDITAR, ELIMINAR) ---

// A. ELIMINAR PRODUCTO
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    $stmt_del = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    if ($stmt_del->execute([$id_eliminar])) {
        header("Location: gestion_productos.php?msg=eliminado");
        exit;
    }
}

// B. GUARDAR / EDITAR PRODUCTO (VALIDACIÓN DE FORMULARIO CRUD)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = trim($_POST['nombre']);
    $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
    $descripcion = trim($_POST['descripcion']);
    $categoria_id = (int)$_POST['categoria_id'];

    // Validaciones en servidor
    if (empty($nombre) || strlen($nombre) < 3) {
        $mensaje = "El nombre del producto debe tener al menos 3 caracteres.";
    } elseif ($precio === false || $precio <= 0) {
        $mensaje = "Ingrese un precio válido mayor a 0.";
    } elseif ($categoria_id <= 0) {
        $mensaje = "Seleccione una categoría válida.";
    } else {
        // Manejo de Imagen (Si se sube una nueva)
        $nombre_imagen = 'default.jpg';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ext, $extensiones_permitidas)) {
                $nombre_imagen = uniqid('prod_') . '.' . $ext;
                move_uploaded_file($_FILES['imagen']['tmp_name'], 'uploads/' . $nombre_imagen);
            } else {
                $mensaje = "Formato de imagen no permitido. Solo JPG, PNG y WEBP.";
            }
        } elseif ($id > 0) {
            // Mantener imagen existente si estamos editando
            $stmt_img = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
            $stmt_img->execute([$id]);
            $prod_existente = $stmt_img->fetch();
            $nombre_imagen = $prod_existente['imagen'] ?? 'default.jpg';
        }

        if (empty($mensaje)) {
            if ($id > 0) {
                // UPDATE
                $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, precio = ?, descripcion = ?, categoria_id = ?, imagen = ? WHERE id = ?");
                $stmt->execute([$nombre, $precio, $descripcion, $categoria_id, $nombre_imagen, $id]);
                $mensaje = "Producto actualizado correctamente.";
                $tipo_alerta = "success";
            } else {
                // INSERT
                $stmt = $pdo->prepare("INSERT INTO productos (nombre, precio, descripcion, categoria_id, imagen) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $precio, $descripcion, $categoria_id, $nombre_imagen]);
                $mensaje = "Producto registrado con éxito.";
                $tipo_alerta = "success";
            }
        }
    }
}

// C. OBTENER PRODUCTO A EDITAR (Si viene id por GET)
$producto_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = (int)$_GET['editar'];
    $stmt_edit = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt_edit->execute([$id_editar]);
    $producto_editar = $stmt_edit->fetch();
}

// OBTENER LISTAS DE LA BASE DE DATOS
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();
$productos = $pdo->query("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC")->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Gestión de Productos (Vista Vendedor)</h2>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
    <div class="alert alert-success alert-dismissible fade show">Producto eliminado correctamente.</div>
<?php endif; ?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="row">
    <!-- FORMULARIO CRUD (CREAR / EDITAR) -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><?= $producto_editar ? 'Editar Producto' : 'Nuevo Producto' ?></h5>
            </div>
            <div class="card-body">
                <form action="gestion_productos.php" method="POST" enctype="multipart/form-data">
                    <?php if ($producto_editar): ?>
                        <input type="hidden" name="id" value="<?= $producto_editar['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nombre del Producto *</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($producto_editar['nombre'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Precio ($) *</label>
                        <input type="number" step="0.01" min="0.1" name="precio" class="form-control" required value="<?= htmlspecialchars($producto_editar['precio'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoría *</label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($producto_editar['categoria_id']) && $producto_editar['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($producto_editar['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagen</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> <?= $producto_editar ? 'Actualizar' : 'Guardar Producto' ?>
                    </button>
                    <?php if ($producto_editar): ?>
                        <a href="gestion_productos.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- TABLA DE PRODUCTOS REGISTRADOS -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Lista de Productos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($productos) > 0): ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr>
                                        <td><?= $prod['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($prod['nombre']) ?></strong></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría') ?></span></td>
                                        <td>$<?= number_format($prod['precio'], 2) ?></td>
                                        <td>
                                            <a href="gestion_productos.php?editar=<?= $prod['id'] ?>" class="btn btn-warning btn-sm me-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="gestion_productos.php?eliminar=<?= $prod['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este producto?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay productos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>