<?php
session_start();
require_once 'config/db.php';

// Obtener categorías
$cat_stmt = $pdo->query("SELECT * FROM categorias");
$categorias = $cat_stmt->fetchAll();

// Filtrar por categoría si existe
$categoria_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
if ($categoria_id > 0) {
    $prod_stmt = $pdo->prepare("SELECT * FROM productos WHERE categoria_id = ?");
    $prod_stmt->execute([$categoria_id]);
} else {
    $prod_stmt = $pdo->query("SELECT * FROM productos");
}
$productos = $prod_stmt->fetchAll();

include 'includes/header.php';
?>

<h2>Catálogo de Productos</h2>

<!-- Submenús de Filtro por Categoría -->
<div class="mb-4">
    <a href="catalogo.php" class="btn btn-outline-dark btn-sm me-1">Todos</a>
    <?php foreach ($categorias as $cat): ?>
        <a href="catalogo.php?cat=<?= $cat['id'] ?>" class="btn btn-outline-primary btn-sm me-1">
            <?= htmlspecialchars($cat['nombre']) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="row">
    <?php foreach ($productos as $prod): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($prod['nombre']) ?></h5>
                    <p class="card-text text-muted"><?= htmlspecialchars($prod['descripcion']) ?></p>
                    <h6 class="text-success fw-bold">$<?= number_format($prod['precio'], 2) ?></h6>
                </div>
                <div class="card-footer bg-white border-0">
                    <form action="agregar_carrito.php" method="POST">
                        <input type="hidden" name="producto_id" value="<?= $prod['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-cart-plus"></i> Agregar al Carrito
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>