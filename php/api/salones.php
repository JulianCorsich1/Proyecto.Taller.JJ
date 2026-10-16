<?php
/**
 * API de Salones y Reseñas (PHP / MySQL)
 * Soporta la visualización de salones, reseñas y calificaciones
 * Casos de Uso: 4.6 (Comentar y Puntuar Salón), 4.7 (Gestionar Salones)
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/conexion.php';

$accion = $_REQUEST['accion'] ?? 'listar';

try {
    $db = db();

    switch ($accion) {
        case 'listar':
            // Listar salones con promedio de calificación y cantidad de reseñas
            $sql = "
                SELECT 
                    s.id_salon,
                    s.nombre,
                    s.direccion,
                    s.capacidad_maxima,
                    s.cantidad_mesas,
                    s.precio_por_hora,
                    s.precio_base,
                    s.descripcion,
                    s.imagen_principal,
                    s.estado,
                    COALESCE(ROUND(AVG(r.puntuacion), 1), 5.0) AS promedio_estrellas,
                    COUNT(r.id_resena) AS total_resenas
                FROM salones s
                LEFT JOIN resenas r ON s.id_salon = r.id_salon
                WHERE s.estado = 'DISPONIBLE'
                GROUP BY s.id_salon
                ORDER BY s.id_salon ASC
            ";
            $stmt = $db->query($sql);
            $salones = $stmt->fetchAll();

            // Adjuntar galería de fotos a cada salón
            foreach ($salones as &$salon) {
                $galStmt = $db->prepare("SELECT url_imagen FROM salon_imagenes WHERE id_salon = ? ORDER BY orden ASC");
                $galStmt->execute([$salon['id_salon']]);
                $imagenes = $galStmt->fetchAll(PDO::FETCH_COLUMN);
                $salon['galeria'] = !empty($imagenes) ? $imagenes : [$salon['imagen_principal']];
            }

            echo json_encode(['exito' => true, 'salones' => $salones]);
            break;

        case 'detalle':
            $id = (int)($_REQUEST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['exito' => false, 'mensaje' => 'ID de salón inválido.']);
                exit;
            }

            $stmt = $db->prepare("SELECT * FROM salones WHERE id_salon = ?");
            $stmt->execute([$id]);
            $salon = $stmt->fetch();

            if (!$salon) {
                echo json_encode(['exito' => false, 'mensaje' => 'Salón no encontrado.']);
                exit;
            }

            // Galería
            $galStmt = $db->prepare("SELECT url_imagen FROM salon_imagenes WHERE id_salon = ? ORDER BY orden ASC");
            $galStmt->execute([$id]);
            $salon['galeria'] = $galStmt->fetchAll(PDO::FETCH_COLUMN);

            // Reseñas
            $resStmt = $db->prepare("
                SELECT r.id_resena, r.puntuacion, r.comentario, r.fecha, p.nombre, p.apellido
                FROM resenas r
                JOIN clientes c ON r.id_cliente = c.id_cliente
                JOIN personas p ON c.id_cliente = p.id_persona
                WHERE r.id_salon = ?
                ORDER BY r.fecha DESC
            ");
            $resStmt->execute([$id]);
            $salon['resenas'] = $resStmt->fetchAll();

            echo json_encode(['exito' => true, 'salon' => $salon]);
            break;

        case 'guardar_resena':
            // 4.6 Caso de Uso: Comentar y Puntuar Salón
            if (!isset($_SESSION['id_usuario'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debe iniciar sesión para calificar un salón.']);
                exit;
            }

            $idSalon = (int)($_POST['id_salon'] ?? 0);
            $puntuacion = (int)($_POST['puntuacion'] ?? 5);
            $comentario = trim($_POST['comentario'] ?? '');

            if ($idSalon <= 0 || empty($comentario)) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debe ingresar un comentario y seleccionar un salón.']);
                exit;
            }

            if ($puntuacion < 1 || $puntuacion > 5) {
                echo json_encode(['exito' => false, 'mensaje' => 'La puntuación debe ser de 1 a 5 estrellas.']);
                exit;
            }

            $stmt = $db->prepare("
                INSERT INTO resenas (id_salon, id_cliente, puntuacion, comentario, fecha)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$idSalon, $_SESSION['id_usuario'], $puntuacion, $comentario]);

            echo json_encode(['exito' => true, 'mensaje' => '¡Comentario y calificación registrados con éxito!']);
            break;

        default:
            echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
