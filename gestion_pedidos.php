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
            <p class='lead'>Solo los vendedores pueden gestionar pedidos.</p>
            <a href='index.php' class='btn btn-primary mt-2'>Volver al Inicio</a>
        </div>
    </div>";
    include 'includes/footer.php';
    exit;
}

require_once 'config/db.php';

$mensaje = '';

// --- 2. ACCIÓN DE CAMBIAR ESTADO DEL PEDIDO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id'], $_POST['accion'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $nuevo_estado = $_POST['accion'] === 'Aceptar' ? 'Aceptado' : ($_POST['accion'] === 'Rechazar' ? 'Rechazado' : 'Pendiente');
    $vendedor_nombre = $_SESSION['user_nombre']; // Asigna el Vendedor que atendió

    $stmt_update = $pdo->prepare("UPDATE pedidos SET estado = ?, nombre_vendedor = ? WHERE id = ?");
    if ($stmt_update->execute([$nuevo_estado, $vendedor_nombre, $pedido_id])) {
        $mensaje = "El pedido #{$pedido_id} fue actualizado a Estado: '{$nuevo_estado}' por {$vendedor_nombre}.";
    }
}

// OBTENER TODOS LOS PEDIDOS CON SUS DETALLES
$stmt_pedidos = $pdo->query("SELECT p.*, u.email as cliente_email FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.fecha DESC");
$pedidos = $stmt_pedidos->fetchAll();

include 'includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-receipt"></i> Gestión de Pedidos (Vendedor)</h2>

<?php if ($mensaje): ?>
    <div class="alert alert-info alert-dismissible fade show"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th># Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Vendedor Asignado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pedidos) > 0): ?>
                        <?php foreach ($pedidos as $ped): ?>
                            <tr>
                                <td><strong>#<?= $ped['id'] ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($ped['nombre']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($ped['cliente_email']) ?></small>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($ped['fecha'])) ?></td>
                                <td>
                                    <?php
                                        $badge = 'bg-warning text-dark';
                                        if ($ped['estado'] === 'Aceptado') $badge = 'bg-success';
                                        if ($ped['estado'] === 'Rechazado') $badge = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= $ped['estado'] ?></span>
                                </td>
                                <td><?= htmlspecialchars($ped['nombre_vendedor']) ?></td>
                                <td>
                                    <?php if ($ped['estado'] === 'Pendiente'): ?>
                                        <form method="POST" action="gestion_pedidos.php" class="d-inline">
                                            <input type="hidden" name="pedido_id" value="<?= $ped['id'] ?>">
                                            <button type="submit" name="accion" value="Aceptar" class="btn btn-success btn-sm me-1">
                                                <i class="bi bi-check-circle"></i> Aceptar
                                            </button>
                                            <button type="submit" name="accion" value="Rechazar" class="btn btn-danger btn-sm" onclick="return confirm('¿Rechazar este pedido?')">
                                                <i class="bi bi-x-circle"></i> Rechazar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Procesado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No existen pedidos registrados en el sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>