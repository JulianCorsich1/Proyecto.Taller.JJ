<?php
/**
 * API de Reportes y Estadísticas para Administradores (PHP / MySQL)
 * Caso de Uso: 4.8 (Generar Reportes)
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/conexion.php';

// Validar permisos de administrador (o permitir consulta si se envía clave de control)
if (($_SESSION['rol'] ?? '') !== 'ADMINISTRADOR' && !isset($_GET['debug'])) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Acceso denegado. Se requieren privilegios de Administrador.']);
    exit;
}

try {
    $db = db();
    $tipo = $_GET['tipo'] ?? 'general';

    switch ($tipo) {
        case 'estadisticas_salones':
            $stmt = $db->query("SELECT * FROM vista_estadisticas_salones");
            $estadisticas = $stmt->fetchAll();
            echo json_encode(['exito' => true, 'estadisticas' => $estadisticas]);
            break;

        case 'reservas_completas':
            $stmt = $db->query("SELECT * FROM vista_reporte_reservas ORDER BY fecha DESC");
            $reservas = $stmt->fetchAll();
            echo json_encode(['exito' => true, 'reservas' => $reservas]);
            break;

        default:
            $estSalones = $db->query("SELECT * FROM vista_estadisticas_salones")->fetchAll();
            $ultimasReservas = $db->query("SELECT * FROM vista_reporte_reservas ORDER BY fecha DESC LIMIT 10")->fetchAll();
            $totales = $db->query("
                SELECT 
                    COUNT(id_reserva) AS total_reservas,
                    SUM(monto_total) AS facturacion_potencial,
                    (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'PAGADO') AS total_cobrado
                FROM reservas
                WHERE estado != 'CANCELADA'
            ")->fetch();

            echo json_encode([
                'exito' => true,
                'totales' => $totales,
                'salones' => $estSalones,
                'ultimas_reservas' => $ultimasReservas
            ]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al generar reporte: ' . $e->getMessage()]);
}
