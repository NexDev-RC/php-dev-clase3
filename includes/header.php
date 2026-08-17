<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Emprendimiento - Sprint 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">TechStore</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="nosotros.php">Sobre Nosotros</a></li>
        <li class="nav-item"><a class="nav-link" href="catalogo.php">Catálogo de Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="contacto.php">Contacto</a></li>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="mis_pedidos.php">Mis Pedidos</a></li>
            <!-- Validación por Rol: Solo el vendedor gestiona productos y pedidos -->
            <?php if ($_SESSION['user_rol'] === 'Vendedor'): ?>
                <li class="nav-item"><a class="nav-link text-warning" href="gestion_productos.php">CRUD Productos</a></li>
                <li class="nav-item"><a class="nav-link text-info" href="gestion_pedidos.php">Gestión Pedidos</a></li>
            <?php endif; ?>
        <?php endif; ?>
      </ul>
      
      <ul class="navbar-nav">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item me-2">
                <span class="navbar-text text-light me-2">
                   Hola, <?= htmlspecialchars($_SESSION['user_nombre']) ?> (<?= $_SESSION['user_rol'] ?>)
                </span>
            </li>
            <li class="nav-item">
                <a class="btn btn-outline-success btn-sm me-2" href="ver_carrito.php">
                   🛒 Carrito (<?= isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0 ?>)
                </a>
            </li>
            <li class="nav-item"><a class="btn btn-outline-danger btn-sm" href="logout.php">Salir</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="login.php">Iniciar Sesión</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container my-4">