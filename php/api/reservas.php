<?php
/**
 * API de Gestión de Reservas (PHP / MySQL)
 * Casos de Uso: 4.3 (Reservar Salón), 4.5 (Cancelar Reserva), Requisitos 6, 9, 11, 13
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/conexion.php';

$accion = $_REQUEST['accion'] ?? '';

try {
    $db = db();

    switch ($accion) {
        case 'verificar_disponibilidad':
            // Verifica disponibilidad de un salón en una fecha y rango de horas
            $idSalon = (int)($_POST['id_salon'] ?? 0);
            $fecha = $_POST['fecha'] ?? '';
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';

            if (!$idSalon || !$fecha || !$horaInicio || !$horaFin) {
                echo json_encode(['exito' => false, 'mensaje' => 'Parámetros incompletos para verificar disponibilidad.']);
                exit;
            }

            // Comprobación de solapamiento
            $sql = "
                SELECT COUNT(*) FROM reservas 
                WHERE id_salon = ? 
                  AND fecha = ? 
                  AND estado NOT IN ('CANCELADA')
                  AND (
                      (? >= hora_inicio AND ? < hora_fin)
                      OR (? > hora_inicio AND ? <= hora_fin)
                      OR (? <= hora_inicio AND ? >= hora_fin)
                  )
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idSalon, $fecha, $horaInicio, $horaInicio, $horaFin, $horaFin, $horaInicio, $horaFin]);
            $ocupado = (int)$stmt->fetchColumn() > 0;

            echo json_encode([
                'exito' => true,
                'disponible' => !$ocupado,
                'mensaje' => $ocupado ? 'El salón ya cuenta con una reserva en ese horario.' : 'El salón está disponible.'
            ]);
            break;

        case 'crear':
            // 4.3 Caso de Uso: Reservar Salón
            if (!isset($_SESSION['id_usuario'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debe iniciar sesión para realizar una reserva.']);
                exit;
            }

            $idCliente = $_SESSION['id_usuario'];
            $idSalon = (int)($_POST['id_salon'] ?? 0);
            $tipoEvento = trim($_POST['tipo_evento'] ?? 'Social / Celebración');
            $fecha = $_POST['fecha'] ?? '';
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';
            $invitados = (int)($_POST['cantidad_invitados'] ?? 50);
            $mesas = (int)($_POST['cantidad_mesas'] ?? 10);
            $notas = trim($_POST['notas'] ?? '');
            $serviciosSeleccionados = $_POST['servicios'] ?? []; // Array de IDs de servicios seleccionados

            if ($idSalon <= 0 || empty($fecha) || empty($horaInicio) || empty($horaFin)) {
                echo json_encode(['exito' => false, 'mensaje' => 'Complete todos los campos obligatorios de la reserva.']);
                exit;
            }

            // Validar capacidad máxima del salón
            $stmtSalon = $db->prepare("SELECT precio_base, precio_por_hora, capacidad_maxima FROM salones WHERE id_salon = ?");
            $stmtSalon->execute([$idSalon]);
            $salon = $stmtSalon->fetch();

            if (!$salon) {
                echo json_encode(['exito' => false, 'mensaje' => 'El salón seleccionado no existe.']);
                exit;
            }

            if ($invitados > $salon['capacidad_maxima']) {
                echo json_encode(['exito' => false, 'mensaje' => "La cantidad de invitados supera la capacidad máxima ({$salon['capacidad_maxima']} personas)."]);
                exit;
            }

            // Validar que no haya choque de reservas (Requisito del sistema 4)
            $sqlCheck = "
                SELECT COUNT(*) FROM reservas 
                WHERE id_salon = ? 
                  AND fecha = ? 
                  AND estado NOT IN ('CANCELADA')
                  AND (
                      (? >= hora_inicio AND ? < hora_fin)
                      OR (? > hora_inicio AND ? <= hora_fin)
                      OR (? <= hora_inicio AND ? >= hora_fin)
                  )
            ";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->execute([$idSalon, $fecha, $horaInicio, $horaInicio, $horaFin, $horaFin, $horaInicio, $horaFin]);
            if ((int)$stmtCheck->fetchColumn() > 0) {
                echo json_encode(['exito' => false, 'mensaje' => 'El salón ya se encuentra reservado en el horario elegido.']);
                exit;
            }

            // Calcular costo total
            $inicioTs = strtotime($horaInicio);
            $finTs = strtotime($horaFin);
            $horas = max(1, round(($finTs - $inicioTs) / 3600, 2));

            $montoTotal = (float)$salon['precio_base'];

            // Sumar costo de servicios adicionales
            $detallesServicios = [];
            if (!empty($serviciosSeleccionados) && is_array($serviciosSeleccionados)) {
                $placeholders = implode(',', array_fill(0, count($serviciosSeleccionados), '?'));
                $stmtServ = $db->prepare("SELECT id_servicio, precio FROM servicios_adicionales WHERE id_servicio IN ($placeholders)");
                $stmtServ->execute(array_values($serviciosSeleccionados));
                $serviciosDB = $stmtServ->fetchAll();

                foreach ($serviciosDB as $s) {
                    $montoTotal += (float)$s['precio'];
                    $detallesServicios[] = [
                        'id_servicio' => $s['id_servicio'],
                        'precio' => (float)$s['precio']
                    ];
                }
            }

            // Transacción de inserción
            $db->beginTransaction();

            $stmtIns = $db->prepare("
                INSERT INTO reservas (id_cliente, id_salon, tipo_evento, fecha, hora_inicio, hora_fin, cantidad_invitados, cantidad_mesas, monto_total, estado, notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', ?)
            ");
            $stmtIns->execute([$idCliente, $idSalon, $tipoEvento, $fecha, $horaInicio, $horaFin, $invitados, $mesas, $montoTotal, $notas]);
            $idReserva = (int)$db->lastInsertId();

            // Insertar detalles de servicios
            if (!empty($detallesServicios)) {
                $stmtDet = $db->prepare("
                    INSERT INTO detalle_reserva_servicios (id_reserva, id_servicio, cantidad, precio_unitario, subtotal)
                    VALUES (?, ?, 1, ?, ?)
                ");
                foreach ($detallesServicios as $det) {
                    $stmtDet->execute([$idReserva, $det['id_servicio'], $det['precio'], $det['precio']]);
                }
            }

            $db->commit();

            echo json_encode([
                'exito' => true,
                'mensaje' => '¡Reserva registrada con éxito! Puedes proceder a efectuar el pago.',
                'id_reserva' => $idReserva,
                'monto_total' => $montoTotal
            ]);
            break;

        case 'historial':
            // 4.3 Consultar historial de reservas del usuario en sesión
            if (!isset($_SESSION['id_usuario'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'No autorizado.']);
                exit;
            }

            $idCliente = $_SESSION['id_usuario'];
            $sql = "
                SELECT 
                    r.id_reserva,
                    r.fecha,
                    r.hora_inicio,
                    r.hora_fin,
                    r.tipo_evento,
                    r.cantidad_invitados,
                    r.cantidad_mesas,
                    r.monto_total,
                    r.estado,
                    s.nombre AS salon_nombre,
                    s.direccion AS salon_direccion,
                    COALESCE(SUM(p.monto), 0.00) AS pagado
                FROM reservas r
                JOIN salones s ON r.id_salon = s.id_salon
                LEFT JOIN pagos p ON r.id_reserva = p.id_reserva AND p.estado = 'PAGADO'
                WHERE r.id_cliente = ?
                GROUP BY r.id_reserva
                ORDER BY r.fecha DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCliente]);
            $reservas = $stmt->fetchAll();

            echo json_encode(['exito' => true, 'reservas' => $reservas]);
            break;

        case 'cancelar':
            // 4.5 Caso de Uso: Cancelar Reserva
            if (!isset($_SESSION['id_usuario'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'No autorizado.']);
                exit;
            }

            $idReserva = (int)($_POST['id_reserva'] ?? 0);
            $motivo = trim($_POST['motivo'] ?? 'Cancelación solicitada por el usuario');

            $stmt = $db->prepare("SELECT id_cliente, estado FROM reservas WHERE id_reserva = ?");
            $stmt->execute([$idReserva]);
            $reserva = $stmt->fetch();

            if (!$reserva) {
                echo json_encode(['exito' => false, 'mensaje' => 'Reserva no encontrada.']);
                exit;
            }

            // Validar que pertenezca al usuario (a menos que sea Administrador)
            if ($reserva['id_cliente'] != $_SESSION['id_usuario'] && ($_SESSION['rol'] ?? '') !== 'ADMINISTRADOR') {
                echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para modificar esta reserva.']);
                exit;
            }

            if ($reserva['estado'] === 'CANCELADA') {
                echo json_encode(['exito' => false, 'mensaje' => 'Esta reserva ya se encuentra cancelada.']);
                exit;
            }

            $upd = $db->prepare("UPDATE reservas SET estado = 'CANCELADA', motivo_cancelacion = ? WHERE id_reserva = ?");
            $upd->execute([$motivo, $idReserva]);

            echo json_encode(['exito' => true, 'mensaje' => 'La reserva ha sido cancelada satisfactoriamente.']);
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
