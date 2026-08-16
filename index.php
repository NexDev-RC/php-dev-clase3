<?php
$host = '127.0.0.1'; // Usamos 127.0.0.1 por la configuración network_mode: service:db
$db   = 'mariadb';
$user = 'mariadb';
$pass = 'mariadb';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $mensaje = "¡Conexión exitosa a MariaDB desde Codespaces!";
} catch (\PDOException $e) {
    $mensaje = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP + MariaDB en Codespaces</title>
    <style>
        body { font-family: sans-serif; display: grid; place-content: center; min-height: 100vh; background: #f4f4f9; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success { color: #2e7d32; }
        .error { color: #c62828; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Proyecto PHP en GitHub Codespaces</h1>
        <p><strong>Estado de Base de Datos:</strong></p>
        <p class="<?php echo strpos($mensaje, 'exitosa') !== false ? 'success' : 'error'; ?>">
            <?php echo $mensaje; ?>
        </p>
    </div>
</body>
</html>
