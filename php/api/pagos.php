<?php
/**
 * API de Registro y Procesamiento de Pagos (PHP / MySQL)
 * Caso de Uso: 4.4 (Realizar Pago)
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/conexion.php';

$accion = $_REQUEST['accion'] ?? 'registrar';

try {
    $db = db();

    switch ($accion) {
        case 'registrar':
            if (!isset($_SESSION['id_usuario'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debe iniciar sesión para realizar pagos.']);
                exit;
            }

            $idReserva = (int)($_POST['id_reserva'] ?? 0);
            $monto = (float)($_POST['monto'] ?? 0.0);
            $metodoPago = strtoupper(trim($_POST['metodo_pago'] ?? 'TRANSFERENCIA'));
            $tipoPago = strtoupper(trim($_POST['tipo_pago'] ?? 'PAGO_TOTAL'));
            $comprobante = trim($_POST['comprobante'] ?? ('TRANS-' . time()));

            $metodosPermitidos = ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'MERCADOPAGO'];
            if (!in_array($metodoPago, $metodosPermitidos)) {
                echo json_encode(['exito' => false, 'mensaje' => 'Método de pago no válido.']);
                exit;
            }

            // Consultar datos de la reserva
            $stmt = $db->prepare("SELECT id_reserva, id_cliente, monto_total, estado FROM reservas WHERE id_reserva = ?");
            $stmt->execute([$idReserva]);
            $reserva = $stmt->fetch();

            if (!$reserva) {
                echo json_encode(['exito' => false, 'mensaje' => 'Reserva no encontrada.']);
                exit;
            }

            if ($monto <= 0) {
                echo json_encode(['exito' => false, 'mensaje' => 'El importe ingresado debe ser mayor a cero.']);
                exit;
            }

            $db->beginTransaction();

            // Insertar el pago
            $insPago = $db->prepare("
                INSERT INTO pagos (id_reserva, monto, fecha_pago, metodo_pago, tipo_pago, estado, comprobante)
                VALUES (?, ?, NOW(), ?, ?, 'PAGADO', ?)
            ");
            $insPago->execute([$idReserva, $monto, $metodoPago, $tipoPago, $comprobante]);
            $idPago = (int)$db->lastInsertId();

            // Verificar acumulado de pagos de esta reserva
            $totPagadoStmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE id_reserva = ? AND estado = 'PAGADO'");
            $totPagadoStmt->execute([$idReserva]);
            $totalPagado = (float)$totPagadoStmt->fetchColumn();

            // Actualizar estado de la reserva
            $nuevoEstadoReserva = ($totalPagado >= (float)$reserva['monto_total']) ? 'CONFIRMADA' : 'CONFIRMADA';
            $updRes = $db->prepare("UPDATE reservas SET estado = ? WHERE id_reserva = ?");
            $updRes->execute([$nuevoEstadoReserva, $idReserva]);

            $db->commit();

            echo json_encode([
                'exito' => true,
                'mensaje' => '¡Pago registrado exitosamente! Tu reserva se encuentra confirmada.',
                'id_pago' => $idPago,
                'total_pagado' => $totalPagado,
                'saldo_pendiente' => max(0, (float)$reserva['monto_total'] - $totalPagado)
            ]);
            break;

        case 'obtener_pagos_reserva':
            $idReserva = (int)($_GET['id_reserva'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM pagos WHERE id_reserva = ? ORDER BY fecha_pago DESC");
            $stmt->execute([$idReserva]);
            $pagos = $stmt->fetchAll();

            echo json_encode(['exito' => true, 'pagos' => $pagos]);
            break;

        default:
            echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida.']);
            break;
    }

} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
