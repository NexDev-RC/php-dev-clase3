<?php
session_start();
require_once 'config/db.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validación básica en servidor
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Por favor, ingrese un correo válido.';
    } elseif (empty($password)) {
        $mensaje = 'Ingrese su contraseña.';
    } else {
        $stmt = $pdo->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_rol'] = $usuario['rol_nombre'];

            header('Location: index.php');
            exit;
        } else {
            $mensaje = 'Credenciales incorrectas.';
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Iniciar Sesión</h4>
            </div>
            <div class="card-body">
                <?php if ($mensaje): ?>
                    <div class="alert alert-danger"><?= $mensaje ?></div>
                <?php endif; ?>
                <form method="POST" action="login.php" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" required placeholder="admin@tienda.com o cliente@tienda.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required placeholder="password123">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>