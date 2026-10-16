<?php
/**
 * Instalador Automático de la Base de Datos MiSalónOnline
 * Ejecutable vía navegador web o CLI de PHP.
 */

require_once __DIR__ . '/config/conexion.php';

$mensaje = '';
$exito = false;
$detalles = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (php_sapi_name() === 'cli')) {
    try {
        $pdo = Conexion::getConexionServidor();

        $sqlFile = dirname(__DIR__) . '/sql/database.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("El archivo SQL no se encontró en: " . $sqlFile);
        }

        $sqlContent = file_get_contents($sqlFile);

        // Remover comentarios de delimitador que PHP PDO no soporta directamente
        // Reemplazar DELIMITER por scripts directos si es necesario
        // O ejecutar bloques
        
        // Ejecución de comandos DDL y DML
        $pdo->exec("DROP DATABASE IF EXISTS `misalononline`;");
        $pdo->exec("CREATE DATABASE `misalononline` CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;");
        $pdo->exec("USE `misalononline`;");

        // Dividir sentencias ignorando los bloques DELIMITER del procedimiento almacenado
        // Ejecutamos primero la creación de tablas e inserts
        $sqlClean = preg_replace('/DELIMITER \$\$[\s\S]*?DELIMITER ;/m', '', $sqlContent);
        
        // Separar consultas por punto y coma al final de línea
        $queries = array_filter(
            array_map('trim', preg_split('/;\s*[\r\n]+/m', $sqlClean)),
            fn($q) => !empty($q) && !str_starts_with($q, '--')
        );

        foreach ($queries as $query) {
            if (trim($query) !== '') {
                $pdo->exec($query);
            }
        }

        // Crear el procedimiento almacenado
        $spSql = "
        CREATE PROCEDURE `sp_verificar_disponibilidad`(
            IN `p_id_salon` INT,
            IN `p_fecha` DATE,
            IN `p_hora_inicio` TIME,
            IN `p_hora_fin` TIME,
            OUT `p_disponible` TINYINT(1)
        )
        BEGIN
            DECLARE v_solapados INT;
            SELECT COUNT(*) INTO v_solapados
            FROM `reservas`
            WHERE `id_salon` = p_id_salon
              AND `fecha` = p_fecha
              AND `estado` NOT IN ('CANCELADA')
              AND (
                  (p_hora_inicio >= `hora_inicio` AND p_hora_inicio < `hora_fin`)
                  OR (p_hora_fin > `hora_inicio` AND p_hora_fin <= `hora_fin`)
                  OR (p_hora_inicio <= `hora_inicio` AND p_hora_fin >= `hora_fin`)
              );
            IF v_solapados = 0 THEN
                SET p_disponible = 1;
            ELSE
                SET p_disponible = 0;
            END IF;
        END;";
        $pdo->exec("DROP PROCEDURE IF EXISTS `sp_verificar_disponibilidad`;");
        $pdo->exec($spSql);

        $exito = true;
        $mensaje = "¡Base de datos 'misalononline' instalada y configurada con éxito!";

        // Obtener resumen de tablas instaladas
        $stmt = $pdo->query("SHOW TABLES FROM `misalononline`");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tablas as $tabla) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `misalononline`.`$tabla`");
            $cantidad = $countStmt->fetchColumn();
            $detalles[] = "Tabla: <strong>$tabla</strong> — $cantidad registros iniciales.";
        }

    } catch (Exception $e) {
        $exito = false;
        $mensaje = "Error al instalar la base de datos: " . $e->getMessage();
    }

    if (php_sapi_name() === 'cli') {
        echo ($exito ? "[EXITO] " : "[ERROR] ") . $mensaje . PHP_EOL;
        foreach ($detalles as $d) {
            echo strip_tags($d) . PHP_EOL;
        }
        exit($exito ? 0 : 1);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instalador de Base de Datos - MiSalónOnline</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../CSS/VerSalon.css">
  <style>
    .install-box {
      max-width: 650px;
      margin: 40px auto;
      background: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-light">

<nav class="navbar">
  <div class="nav-brand">MiSalónOnline</div>
  <div class="nav-links">
    <a href="../Index.html">Volver al Inicio</a>
  </div>
</nav>

<div class="container">
  <div class="install-box">
    <h2 class="text-center mb-3 text-success">Instalación de Base de Datos SQL</h2>
    <p class="text-muted text-center">
      Este módulo crea la base de datos <code>misalononline</code>, todas las tablas relacionales del Diagrama de Clases y DER, e inserta los datos de salones y servicios de acuerdo con el <strong>Manual de Usuario</strong>.
    </p>

    <?php if ($mensaje): ?>
      <div class="alert <?= $exito ? 'alert-success' : 'alert-danger' ?>" role="alert">
        <?= htmlspecialchars($mensaje) ?>
      </div>

      <?php if (!empty($detalles)): ?>
        <h5 class="mt-4">Resumen de tablas creadas:</h5>
        <ul class="list-group mb-4">
          <?php foreach ($detalles as $det): ?>
            <li class="list-group-item"><?= $det ?></li>
          <?php endforeach; ?>
        </ul>
        <div class="d-grid gap-2">
          <a href="../Index.html" class="btn btn-success">Ir a la Página Principal</a>
          <a href="../Paginas/VerSalones.html" class="btn btn-outline-secondary">Ver Catálogo de Salones</a>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="card-title fw-bold">Parámetros actuales de conexión:</h6>
          <ul class="mb-0">
            <li>Host: <code><?= DB_HOST ?></code></li>
            <li>Puerto: <code><?= DB_PORT ?></code></li>
            <li>Usuario: <code><?= DB_USER ?></code></li>
            <li>Base de datos a crear: <code><?= DB_NAME ?></code></li>
          </ul>
        </div>
      </div>

      <form method="POST">
        <div class="d-grid">
          <button type="submit" class="btn btn-esmeralda btn-lg">
            Crear Base de Datos e Importar Datos Iniciales
          </button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
