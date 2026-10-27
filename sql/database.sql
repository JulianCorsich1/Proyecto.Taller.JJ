-- ====================================================================
-- BASE DE DATOS: misalononline
-- Proyecto: Plataforma Web de Gestión y Reserva de Salones de Eventos
-- Alumnos: Montoya José y Corsich Julián
-- Materia: Construcción de Software | Curso: 6°2 | Grupo: 602
-- Profesor: Rodríguez Lucas
-- ====================================================================

-- 1. CREACIÓN Y SELECCIÓN DE LA BASE DE DATOS
DROP DATABASE IF EXISTS `misalononline`;
CREATE DATABASE `misalononline`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;

USE `misalononline`;

-- ====================================================================
-- 2. TABLAS PRINCIPALES (BASADAS EN DIAGRAMA DE CLASES Y DER)
-- ====================================================================

-- Tabla Base: Personas (Clase Persona)
CREATE TABLE `personas` (
  `id_persona` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `dni` VARCHAR(20) NOT NULL UNIQUE,
  `telefono` VARCHAR(30) NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('ADMINISTRADOR', 'CLIENTE', 'EMPLEADO') NOT NULL DEFAULT 'CLIENTE',
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla: Clientes (Especialización de Persona)
CREATE TABLE `clientes` (
  `id_cliente` INT PRIMARY KEY,
  `puntos_fidelidad` INT NOT NULL DEFAULT 0,
  `es_vip` BOOLEAN NOT NULL DEFAULT FALSE,
  CONSTRAINT `fk_cliente_persona`
    FOREIGN KEY (`id_cliente`) REFERENCES `personas` (`id_persona`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla: Empleados (Especialización de Persona)
CREATE TABLE `empleados` (
  `id_empleado` INT PRIMARY KEY,
  `legajo` VARCHAR(50) NOT NULL UNIQUE,
  `cargo` VARCHAR(100) NOT NULL, -- ej: 'Coordinador', 'Mozo', 'Cocinero', 'Fotógrafo', 'DJ', 'Seguridad'
  `sueldo` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `fecha_ingreso` DATE NOT NULL DEFAULT (CURRENT_DATE),
  `activo` BOOLEAN NOT NULL DEFAULT TRUE,
  CONSTRAINT `fk_empleado_persona`
    FOREIGN KEY (`id_empleado`) REFERENCES `personas` (`id_persona`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla: Salones (Clase Salon)
CREATE TABLE `salones` (
  `id_salon` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(120) NOT NULL UNIQUE,
  `direccion` VARCHAR(255) NOT NULL,
  `capacidad_maxima` INT NOT NULL,
  `cantidad_mesas` INT NOT NULL DEFAULT 20,
  `precio_por_hora` DECIMAL(10,2) NOT NULL,
  `precio_base` DECIMAL(10,2) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `imagen_principal` VARCHAR(255) NOT NULL,
  `estado` ENUM('DISPONIBLE', 'MANTENIMIENTO', 'INACTIVO') NOT NULL DEFAULT 'DISPONIBLE'
) ENGINE=InnoDB;

-- Tabla: Galeria de Salones (Imágenes secundarias para el carrusel de VerSalones)
CREATE TABLE `salon_imagenes` (
  `id_imagen` INT AUTO_INCREMENT PRIMARY KEY,
  `id_salon` INT NOT NULL,
  `url_imagen` VARCHAR(255) NOT NULL,
  `orden` INT NOT NULL DEFAULT 1,
  CONSTRAINT `fk_imagen_salon`
    FOREIGN KEY (`id_salon`) REFERENCES `salones` (`id_salon`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla: Servicios Extras / Adicionales (Clase Servicio Extra & DER Entidad Proveedor-Servicio)
CREATE TABLE `servicios_adicionales` (
  `id_servicio` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(120) NOT NULL,
  `tipo` VARCHAR(60) NOT NULL, -- Catering, Decoración, Entretenimiento, Sonido e Iluminación, Fotografía, etc.
  `descripcion` TEXT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `proveedor_nombre` VARCHAR(100) NULL,
  `proveedor_rubro` VARCHAR(100) NULL,
  `proveedor_contacto` VARCHAR(100) NULL,
  `proveedor_telefono` VARCHAR(30) NULL,
  `proveedor_email` VARCHAR(120) NULL,
  `activo` BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

-- Tabla: Reservas (Entidad Reserva)
CREATE TABLE `reservas` (
  `id_reserva` INT AUTO_INCREMENT PRIMARY KEY,
  `id_cliente` INT NOT NULL,
  `id_salon` INT NOT NULL,
  `tipo_evento` VARCHAR(100) NOT NULL, -- Boda, Cumpleaños de 15, Evento Corporativo, Aniversario, etc.
  `fecha` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME NOT NULL,
  `cantidad_invitados` INT NOT NULL,
  `cantidad_mesas` INT NOT NULL,
  `monto_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `estado` ENUM('PENDIENTE', 'CONFIRMADA', 'EN_CURSO', 'FINALIZADA', 'CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
  `motivo_cancelacion` TEXT NULL,
  `notas` TEXT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reserva_cliente`
    FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_reserva_salon`
    FOREIGN KEY (`id_salon`) REFERENCES `salones` (`id_salon`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla: Detalle de Reserva (Relación M:N Reserva - Servicios Adicionales)
CREATE TABLE `detalle_reserva_servicios` (
  `id_detalle` INT AUTO_INCREMENT PRIMARY KEY,
  `id_reserva` INT NOT NULL,
  `id_servicio` INT NOT NULL,
  `cantidad` INT NOT NULL DEFAULT 1,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  CONSTRAINT `fk_detalle_reserva`
    FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_servicio`
    FOREIGN KEY (`id_servicio`) REFERENCES `servicios_adicionales` (`id_servicio`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla: Asignación de Empleados por Reserva
CREATE TABLE `reserva_empleados` (
  `id_asignacion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_reserva` INT NOT NULL,
  `id_empleado` INT NOT NULL,
  `tarea_asignada` VARCHAR(100) NOT NULL,
  CONSTRAINT `fk_asig_reserva`
    FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_asig_empleado`
    FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla: Pagos (Clase Pago)
CREATE TABLE `pagos` (
  `id_pago` INT AUTO_INCREMENT PRIMARY KEY,
  `id_reserva` INT NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `fecha_pago` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metodo_pago` ENUM('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'MERCADOPAGO') NOT NULL,
  `tipo_pago` ENUM('SEÑA', 'PAGO_TOTAL', 'SALDO') NOT NULL DEFAULT 'PAGO_TOTAL',
  `estado` ENUM('PENDIENTE', 'PAGADO', 'PARCIAL', 'REEMBOLSADO') NOT NULL DEFAULT 'PENDIENTE',
  `comprobante` VARCHAR(255) NULL,
  CONSTRAINT `fk_pago_reserva`
    FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla: Reseñas / Comentarios y Calificaciones (Caso de Uso 4.6)
CREATE TABLE `resenas` (
  `id_resena` INT AUTO_INCREMENT PRIMARY KEY,
  `id_salon` INT NOT NULL,
  `id_cliente` INT NOT NULL,
  `id_reserva` INT NULL,
  `puntuacion` TINYINT NOT NULL CHECK (`puntuacion` BETWEEN 1 AND 5),
  `comentario` TEXT NOT NULL,
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_resena_salon`
    FOREIGN KEY (`id_salon`) REFERENCES `salones` (`id_salon`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resena_cliente`
    FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resena_reserva`
    FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla: Recuperación de Contraseñas (Tokens de restablecimiento)
CREATE TABLE `recuperacion_contrasenas` (
  `id_recuperacion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_persona` INT NOT NULL,
  `token` VARCHAR(100) NOT NULL UNIQUE,
  `expira_en` DATETIME NOT NULL,
  `usado` BOOLEAN NOT NULL DEFAULT FALSE,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_token_persona`
    FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ====================================================================
-- 3. ÍNDICES DE RENDIMIENTO (OPTIMIZACIÓN DE DISPONIBILIDAD)
-- ====================================================================

-- Índice para acelerar la comprobación de solapamiento de fechas y horas
CREATE INDEX `idx_reservas_disponibilidad` 
  ON `reservas` (`id_salon`, `fecha`, `hora_inicio`, `hora_fin`, `estado`);

CREATE INDEX `idx_personas_email` ON `personas` (`email`);
CREATE INDEX `idx_reservas_cliente` ON `reservas` (`id_cliente`);
CREATE INDEX `idx_resenas_salon` ON `resenas` (`id_salon`);

-- ====================================================================
-- 4. VISTAS DEL SISTEMA (REPORTES Y CONSULTAS RÁPIDAS)
-- ====================================================================

-- Vista para el Administrador: Reporte Completo de Reservas y Pagos
CREATE OR REPLACE VIEW `vista_reporte_reservas` AS
SELECT 
    r.id_reserva,
    r.fecha,
    r.hora_inicio,
    r.hora_fin,
    r.tipo_evento,
    r.cantidad_invitados,
    r.cantidad_mesas,
    r.estado AS estado_reserva,
    r.monto_total,
    s.id_salon,
    s.nombre AS nombre_salon,
    s.direccion AS direccion_salon,
    p.nombre AS nombre_cliente,
    p.apellido AS apellido_cliente,
    p.email AS email_cliente,
    p.telefono AS telefono_cliente,
    COALESCE(SUM(pg.monto), 0.00) AS total_pagado,
    (r.monto_total - COALESCE(SUM(pg.monto), 0.00)) AS saldo_pendiente,
    CASE 
        WHEN COALESCE(SUM(pg.monto), 0.00) >= r.monto_total THEN 'PAGADO'
        WHEN COALESCE(SUM(pg.monto), 0.00) > 0 THEN 'PARCIAL'
        ELSE 'PENDIENTE'
    END AS estado_pago_calculado
FROM `reservas` r
INNER JOIN `salones` s ON r.id_salon = s.id_salon
INNER JOIN `clientes` c ON r.id_cliente = c.id_cliente
INNER JOIN `personas` p ON c.id_cliente = p.id_persona
LEFT JOIN `pagos` pg ON r.id_reserva = pg.id_reserva AND pg.estado = 'PAGADO'
GROUP BY r.id_reserva;

-- Vista para Estadísticas de Ocupación y Facturación por Salón
CREATE OR REPLACE VIEW `vista_estadisticas_salones` AS
SELECT 
    s.id_salon,
    s.nombre AS nombre_salon,
    s.capacidad_maxima,
    s.precio_base,
    COUNT(r.id_reserva) AS total_reservas,
    COUNT(CASE WHEN r.estado = 'FINALIZADA' THEN 1 END) AS reservas_completadas,
    COUNT(CASE WHEN r.estado = 'CANCELADA' THEN 1 END) AS reservas_canceladas,
    COALESCE(SUM(CASE WHEN r.estado IN ('CONFIRMADA', 'FINALIZADA') THEN r.monto_total END), 0.00) AS facturacion_total,
    COALESCE(AVG(res.puntuacion), 0.0) AS promedio_calificacion,
    COUNT(res.id_resena) AS cantidad_resenas
FROM `salones` s
LEFT JOIN `reservas` r ON s.id_salon = r.id_salon
LEFT JOIN `resenas` res ON s.id_salon = res.id_salon
GROUP BY s.id_salon;

-- ====================================================================
-- 5. PROCEDIMIENTO ALMACENADO: VALIDACIÓN DE DISPONIBILIDAD
-- ====================================================================
-- Comprueba si un salón está libre para una fecha y rango de horas.
-- Retorna 1 si está disponible, 0 si hay solapamiento.
DELIMITER $$
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
END$$
DELIMITER ;

-- ====================================================================
-- 6. DATOS INICIALES (SEED DATA REALISTA SEGÚN EL MANUAL)
-- ====================================================================

-- 6.1. Usuarios / Personas (Administrador, Clientes y Empleados)
-- Contraseñas hasheadas con bcrypt para 'Admin123!' y '123456'
-- HASH de 'Admin123!': $2y$10$eO0V41bEaN1H8j8b/jD9gONM0Gg/qgYj9N2f3wI0sPqG9d9u4f4vy (ejemplo bcrypt)
-- Para pruebas rápidas en PHP utilizaremos compatibilidad nativa.
INSERT INTO `personas` (`id_persona`, `nombre`, `apellido`, `dni`, `telefono`, `email`, `password`, `rol`) VALUES
(1, 'Administrador', 'General', '00000001', '11-4000-0000', 'admin@misalononline.com', '$2y$10$wzSknZ0gqJkvg2iB95w7.uvYgAkeYwFp7L2nBfv2wD4hWjH3hK4iS', 'ADMINISTRADOR'),
(2, 'José', 'Montoya', '42123456', '11-5555-1111', 'jose.montoya@email.com', '$2y$10$wzSknZ0gqJkvg2iB95w7.uvYgAkeYwFp7L2nBfv2wD4hWjH3hK4iS', 'CLIENTE'),
(3, 'Julián', 'Corsich', '43654321', '11-5555-2222', 'julian.corsich@email.com', '$2y$10$wzSknZ0gqJkvg2iB95w7.uvYgAkeYwFp7L2nBfv2wD4hWjH3hK4iS', 'CLIENTE'),
(4, 'Florencia', 'López', '39876543', '11-5555-3333', 'florencia.lopez@email.com', '$2y$10$wzSknZ0gqJkvg2iB95w7.uvYgAkeYwFp7L2nBfv2wD4hWjH3hK4iS', 'CLIENTE'),
(5, 'Carlos', 'Gómez', '35111222', '11-6666-1001', 'carlos.gomez@misalononline.com', '$2y$10$wzSknZ0gqJkvg2iB95w7.uvYgAkeYwFp7L2nBfv2wD4hWjH3hK4iS', 'EMPLEADO'),
(6, 'Lucía', 'Martínez', '37222333', '11-6666-1002', 'lucia.martinez@misalononline.com', '$2y$10$wzSknZ0gqJkvg2iB95w7.uvYgAkeYwFp7L2nBfv2wD4hWjH3hK4iS', 'EMPLEADO'),
(7, 'Marcos', 'Benítez', '38333444', '11-6666-1003', 'marcos.benitez@misalononline.com', '$2y$10$wzSknZ0gqJkvg2iB95w7.uvYgAkeYwFp7L2nBfv2wD4hWjH3hK4iS', 'EMPLEADO');

-- 6.2. Clientes (Extensión)
INSERT INTO `clientes` (`id_cliente`, `puntos_fidelidad`, `es_vip`) VALUES
(2, 120, FALSE),
(3, 250, TRUE),
(4, 80, FALSE);

-- 6.3. Empleados (Extensión)
INSERT INTO `empleados` (`id_empleado`, `legajo`, `cargo`, `sueldo`, `fecha_ingreso`, `activo`) VALUES
(5, 'EMP-001', 'Coordinador General de Eventos', 850000.00, '2024-03-01', TRUE),
(6, 'EMP-002', 'Jefa de Cocina y Catering', 720000.00, '2024-05-15', TRUE),
(7, 'EMP-003', 'Técnico de Sonido e Iluminación', 680000.00, '2024-06-01', TRUE);

-- 6.4. Los 5 Salones Oficiales (Extraídos de VerSalonesjs.js y el manual)
INSERT INTO `salones` (`id_salon`, `nombre`, `direccion`, `capacidad_maxima`, `cantidad_mesas`, `precio_por_hora`, `precio_base`, `descripcion`, `imagen_principal`, `estado`) VALUES
(1, 'Salón Imperial', 'Palermo, Buenos Aires', 250, 24, 300.00, 1800.00, 
 'Un salón de estilo clásico con detalles en dorado y una propuesta elegante para bodas, recepciones y eventos corporativos. Su gran pista de baile, iluminación ambientada y jardín exterior lo convierten en una opción ideal para celebraciones memorables.', 
 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 'DISPONIBLE'),

(2, 'Salón Toscana', 'Belgrano, Buenos Aires', 180, 18, 250.00, 1500.00, 
 'Toscana combina arquitectura contemporánea con una atmósfera cálida y acogedora, ideal para ceremonias íntimas y celebraciones elegantes. Sus amplios ventanales, jardín privado y zonas de descanso brindan confort y una vista espectacular.', 
 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 'DISPONIBLE'),

(3, 'Salón Victoria', 'Recoleta, Buenos Aires', 220, 22, 280.00, 1700.00, 
 'Victoria ofrece una combinación de lujo, sofisticación y funcionalidad para eventos de alto impacto. Su diseño refinado, iluminación premium y amplios sectores de recepción permiten crear experiencias memorables.', 
 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 'DISPONIBLE'),

(4, 'Salón París', 'Villa Crespo, Buenos Aires', 160, 16, 220.00, 1400.00, 
 'París destaca por su estética refinada y su propuesta versátil para eventos íntimos o de mayor envergadura. Combina ambientes luminosos, decoración elegante y un excelente manejo del espacio para lograr un impacto visual único.', 
 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 'DISPONIBLE'),

(5, 'Salón Sunset', 'Puerto Madero, Buenos Aires', 280, 28, 320.00, 1900.00, 
 'Sunset fusiona elegancia y modernidad con una vista privilegiada y un espacio pensado para eventos de alto nivel. Su diseño amplio, ambiente sofisticado y amenities de primer nivel lo convierten en una elección destacada para grandes celebraciones.', 
 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 'DISPONIBLE');

-- 6.5. Galería de Fotos por Salón
INSERT INTO `salon_imagenes` (`id_salon`, `url_imagen`, `orden`) VALUES
(1, 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 1),
(1, 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80', 2),
(1, 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80', 3),
(1, 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80', 4),

(2, 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 1),
(2, 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80', 2),
(2, 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80', 3),

(3, 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 1),
(3, 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80', 2),
(3, 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80', 3),

(4, 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 1),
(4, 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80', 2),
(4, 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80', 3),

(5, 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80', 1),
(5, 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80', 2),
(5, 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80', 3);

-- 6.6. Servicios Extras (Según DER y Requisitos 7 y 8 del Manual)
INSERT INTO `servicios_adicionales` (`id_servicio`, `nombre`, `tipo`, `descripcion`, `precio`, `proveedor_nombre`, `proveedor_rubro`, `proveedor_contacto`, `proveedor_telefono`, `proveedor_email`) VALUES
(1, 'Catering Gourmet Completo', 'Gastronomía', 'Menú formal de 3 pasos, recepción de bienvenida y mesa dulce.', 450.00, 'Delicias Catering S.A.', 'Gastronomía para Eventos', 'Martín Palmer', '11-4455-8899', 'contacto@deliciascatering.com'),
(2, 'Decoración Integral y Flores', 'Ambientación', 'Ambientación temática, centros de mesa florales, telas y velas led.', 250.00, 'Arte & Estilo Bodas', 'Decoración y Ambientación', 'Sofía Carballo', '11-4455-7766', 'info@arteyestilo.com'),
(3, 'Sonido, Iluminación Láser y Pantalla LED', 'Técnica', 'Sistema de sonido envolvente, consola DMX, iluminación robótica y pantalla LED gigante.', 350.00, 'ProSound Producciones', 'Sonido e Iluminación', 'Esteban Ríos', '11-4455-1234', 'ventas@prosound.com'),
(4, 'Fotografía y Cobertura de Video HD', 'Audiovisual', '2 fotógrafos profesionales durante todo el evento, video editado y galería digital.', 300.00, 'Luz & Sombras Fotografía', 'Audiovisual', 'Laura Valenzuela', '11-4455-9900', 'hola@luzysombras.com'),
(5, 'DJ en Vivo y Animación', 'Entretenimiento', 'DJ profesional con setlist a elección y conductor/animador para tanda de baile.', 200.00, 'Party Beats DJs', 'Entretenimiento Musical', 'Mariano Vega', '11-4455-6655', 'booking@partybeats.com'),
(6, 'Personal de Servicio Adicional (Mozos y Bartender)', 'Personal', 'Dotación extra de mozos uniformados y bartender con barra móvil.', 180.00, 'Staff Premier', 'Recursos Humanos para Eventos', 'Valeria Soto', '11-4455-3344', 'contacto@staffpremier.com');

-- 6.7. Reservas de Prueba
INSERT INTO `reservas` (`id_reserva`, `id_cliente`, `id_salon`, `tipo_evento`, `fecha`, `hora_inicio`, `hora_fin`, `cantidad_invitados`, `cantidad_mesas`, `monto_total`, `estado`, `notas`) VALUES
(1, 2, 1, 'Boda', '2026-11-20', '20:00:00', '04:00:00', 180, 18, 3100.00, 'CONFIRMADA', 'Reserva con catering y sonido.'),
(2, 3, 5, 'Cumpleaños de 15', '2026-12-05', '21:00:00', '05:00:00', 220, 22, 2900.00, 'PENDIENTE', 'Esperando confirmación de seña.'),
(3, 4, 3, 'Cena de Fin de Año Corporativa', '2026-10-15', '19:00:00', '01:00:00', 150, 15, 2300.00, 'FINALIZADA', 'Evento finalizado con éxito.');

-- 6.8. Detalles de Reserva (Servicios incluidos)
INSERT INTO `detalle_reserva_servicios` (`id_reserva`, `id_servicio`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 450.00, 450.00), -- Catering en Boda
(1, 3, 1, 350.00, 350.00), -- Sonido e Iluminación en Boda
(1, 5, 1, 200.00, 200.00), -- DJ en Boda
(2, 2, 1, 250.00, 250.00), -- Decoración en Cumpleaños de 15
(2, 3, 1, 350.00, 350.00), -- Sonido en Cumpleaños de 15
(3, 1, 1, 450.00, 450.00); -- Catering en Corporativo

-- 6.9. Asignación de Empleados a Reservas
INSERT INTO `reserva_empleados` (`id_reserva`, `id_empleado`, `tarea_asignada`) VALUES
(1, 5, 'Coordinación y supervisión general de la boda'),
(1, 6, 'Supervisión de banquete y servicio de catering'),
(1, 7, 'Operación de consola de sonido e iluminación'),
(3, 5, 'Coordinación del evento corporativo'),
(3, 6, 'Servicio de gastronomía para empresas');

-- 6.10. Registro de Pagos (Caso de Uso 4.4)
INSERT INTO `pagos` (`id_pago`, `id_reserva`, `monto`, `fecha_pago`, `metodo_pago`, `tipo_pago`, `estado`, `comprobante`) VALUES
(1, 1, 1500.00, '2026-10-01 14:30:00', 'TRANSFERENCIA', 'SEÑA', 'PAGADO', 'COMP-TRANSF-98741'),
(2, 1, 1600.00, '2026-10-10 11:15:00', 'MERCADOPAGO', 'SALDO', 'PAGADO', 'MP-ORD-11223344'),
(3, 2, 1000.00, '2026-10-12 18:00:00', 'TARJETA', 'SEÑA', 'PENDIENTE', NULL),
(4, 3, 2300.00, '2026-09-28 10:00:00', 'TRANSFERENCIA', 'PAGO_TOTAL', 'PAGADO', 'COMP-TRANSF-54321');

-- 6.11. Calificaciones y Comentarios (Caso de Uso 4.6 y VerSalonesjs.js)
INSERT INTO `resenas` (`id_salon`, `id_cliente`, `id_reserva`, `puntuacion`, `comentario`, `fecha`) VALUES
(4, 2, NULL, 5, 'Muy cálido, con un diseño espectacular y atención personalizada.', '2025-08-08 19:20:00'),
(4, 3, NULL, 4, 'Ideal para eventos con estilo moderno y buena organización.', '2025-07-21 16:45:00'),
(4, 4, NULL, 5, 'Nos encantó la iluminación y la comodidad del lugar.', '2025-07-09 21:10:00'),
(5, 4, 3, 5, 'Un lugar espectacular para celebraciones de gala. Muy recomendable.', '2025-08-15 18:30:00'),
(5, 2, NULL, 5, 'Excelente distribución del espacio y una atención sobresaliente.', '2025-08-04 14:10:00'),
(5, 3, NULL, 4, 'Muy elegante y con todos los detalles que uno espera para un gran evento.', '2025-07-29 20:05:00'),
(1, 2, 1, 5, 'La boda soñada. La atención del personal y la acústica del salón fueron inmejorables.', '2026-10-11 12:00:00');
