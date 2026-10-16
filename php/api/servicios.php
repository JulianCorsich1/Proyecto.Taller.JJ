<?php
/**
 * API de Servicios Adicionales y Empleados (PHP / MySQL)
 * Basado en Requisitos 7, 8, 20 y DER (Proveedor / Servicio)
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/conexion.php';

$accion = $_REQUEST['accion'] ?? 'listar';

try {
    $db = db();

    switch ($accion) {
        case 'listar':
            $stmt = $db->query("
                SELECT id_servicio, nombre, tipo, descripcion, precio, proveedor_nombre, proveedor_rubro 
                FROM servicios_adicionales 
                WHERE activo = 1 
                ORDER BY id_servicio ASC
            ");
            $servicios = $stmt->fetchAll();
            echo json_encode(['exito' => true, 'servicios' => $servicios]);
            break;

        case 'empleados':
            // Lista personal disponible para asignación de servicios adicionales
            $stmt = $db->query("
                SELECT e.id_empleado, p.nombre, p.apellido, e.legajo, e.cargo
                FROM empleados e
                JOIN personas p ON e.id_empleado = p.id_persona
                WHERE e.activo = 1
                ORDER BY e.cargo ASC
            ");
            $empleados = $stmt->fetchAll();
            echo json_encode(['exito' => true, 'empleados' => $empleados]);
            break;

        default:
            echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
