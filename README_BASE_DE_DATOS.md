# Base de Datos y Backend PHP — MiSalónOnline

Este proyecto implementa la base de datos relacional en MySQL y la capa de servicios en PHP para la plataforma web **MiSalónOnline**, diseñada y estructurada en base a la documentación oficial del archivo **`Alquiler de Salon Manual de usuario.docx`**.

---

## 👥 Datos Académicos
* **Materia:** Construcción de Software
* **Curso:** 6°2 — **Grupo:** 602
* **Profesor:** Rodríguez Lucas
* **Alumnos:** Montoya José y Corsich Julián

---

## 🏛️ 1. Arquitectura de la Base de Datos (`misalononline`)

La base de datos modela fielmente las entidades, atributos y relaciones definidos en el **Diagrama de Clases** y el **Diagrama de Entidad-Relación (DER)** del manual:

```
[personas] ──< (Herencia) >── [clientes] ────────────┐
    │                                                ▼
    └───< (Herencia) >── [empleados]             [reservas] ◄── [salones] ── [salon_imagenes]
                             │                       │   ▲
                             ▼                       │   │
                   [reserva_empleados]               │   └── [resenas]
                                                     ▼
                                       [detalle_reserva_servicios]
                                                     ▲
                                                     │
                                           [servicios_adicionales]
```

### Tablas Implementadas:

1. **`personas` (Clase Base Persona):**
   * `id_persona` (INT PK AI): Identificador único.
   * `nombre`, `apellido`, `dni` (UNIQUE), `telefono`, `email` (UNIQUE).
   * `password` (VARCHAR 255): Hash criptográfico seguro con Bcrypt.
   * `rol`: ENUM('ADMINISTRADOR', 'CLIENTE', 'EMPLEADO').
   * `fecha_registro` (DATETIME).

2. **`clientes` (Especialización de Persona):**
   * `id_cliente` (INT PK FK `personas.id_persona`).
   * `puntos_fidelidad` (INT).
   * `es_vip` (BOOLEAN).

3. **`empleados` (Especialización de Persona):**
   * `id_empleado` (INT PK FK `personas.id_persona`).
   * `legajo` (VARCHAR UNIQUE): Código identificador de empleado.
   * `cargo` (VARCHAR): Puesto (Coordinador, Mozo, Cocinero, DJ, Sonidista, Seguridad).
   * `sueldo` (DECIMAL 10,2).
   * `fecha_ingreso` (DATE).

4. **`salones` (Clase Salon):**
   * `id_salon` (INT PK AI).
   * `nombre` (VARCHAR UNIQUE): Ej. Salón Imperial, Salón Toscana, Salón Victoria, Salón París, Salón Sunset.
   * `direccion` (VARCHAR): Ubicación física.
   * `capacidad_maxima` (INT): Límite de invitados.
   * `cantidad_mesas` (INT): Capacidad de mesas.
   * `precio_por_hora` (DECIMAL 10,2): Tarifa horaria.
   * `precio_base` (DECIMAL 10,2): Tarifa base de alquiler por evento.
   * `descripcion` (TEXT), `imagen_principal` (VARCHAR).
   * `estado`: ENUM('DISPONIBLE', 'MANTENIMIENTO', 'INACTIVO').

5. **`salon_imagenes` (Galería de Salones):**
   * `id_imagen` (INT PK AI), `id_salon` (FK), `url_imagen` (VARCHAR), `orden` (INT).

6. **`servicios_adicionales` (Clase Servicio Extra & DER Proveedor/Servicio):**
   * `id_servicio` (INT PK AI).
   * `nombre`, `tipo` (Gastronomía, Ambientación, Técnica, Audiovisual, Entretenimiento, Personal).
   * `descripcion` (TEXT), `precio` (DECIMAL 10,2).
   * `proveedor_nombre`, `proveedor_rubro`, `proveedor_contacto`, `proveedor_telefono`, `proveedor_email`.
   * `activo` (BOOLEAN).

7. **`reservas` (Entidad Reserva):**
   * `id_reserva` (INT PK AI).
   * `id_cliente` (FK `clientes`), `id_salon` (FK `salones`).
   * `tipo_evento` (VARCHAR): Boda, Cumpleaños de 15, Corporativo, Aniversario, etc.
   * `fecha` (DATE), `hora_inicio` (TIME), `hora_fin` (TIME).
   * `cantidad_invitados` (INT), `cantidad_mesas` (INT).
   * `monto_total` (DECIMAL 10,2): Cálculo automático (Salón + Servicios adicionales).
   * `estado`: ENUM('PENDIENTE', 'CONFIRMADA', 'EN_CURSO', 'FINALIZADA', 'CANCELADA').
   * `motivo_cancelacion` (TEXT), `notas` (TEXT).

8. **`detalle_reserva_servicios` (Relación M:N DetalleReserva):**
   * `id_detalle` (INT PK AI), `id_reserva` (FK), `id_servicio` (FK), `cantidad` (INT), `precio_unitario`, `subtotal`.

9. **`reserva_empleados` (Asignación de Personal para Eventos):**
   * `id_asignacion` (INT PK AI), `id_reserva` (FK), `id_empleado` (FK), `tarea_asignada` (VARCHAR).

10. **`pagos` (Clase Pago):**
    * `id_pago` (INT PK AI), `id_reserva` (FK).
    * `monto` (DECIMAL 10,2), `fecha_pago` (DATETIME).
    * `metodo_pago`: ENUM('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'MERCADOPAGO').
    * `tipo_pago`: ENUM('SEÑA', 'PAGO_TOTAL', 'SALDO').
    * `estado`: ENUM('PENDIENTE', 'PAGADO', 'PARCIAL', 'REEMBOLSADO').
    * `comprobante` (VARCHAR).

11. **`resenas` (Comentarios y Calificaciones):**
    * `id_resena` (INT PK AI), `id_salon` (FK), `id_cliente` (FK), `id_reserva` (FK opcional).
    * `puntuacion` (TINYINT 1 a 5 estrellas).
    * `comentario` (TEXT), `fecha` (DATETIME).

12. **`recuperacion_contrasenas`:**
    * `id_recuperacion` (INT PK AI), `id_persona` (FK), `token` (VARCHAR UNIQUE), `expira_en`, `usado`.

---

## ⚡ 2. Vistas y Procedimientos Almacenados en SQL

* **`vista_reporte_reservas`:** Genera un balance completo cruzando clientes, salón, monto contratado, suma de pagos efectuados y saldo adeudado.
* **`vista_estadisticas_salones`:** Reporte estadístico por salón con cantidad de eventos, cancelaciones, facturación y puntuación promedio de clientes.
* **`sp_verificar_disponibilidad`:** Stored Procedure que recibe `(p_id_salon, p_fecha, p_hora_inicio, p_hora_fin)` y calcula si hay colisión de reservas existentes para asegurar que no se produzcan dobles reservas en el mismo horario.

---

## 🚀 3. Cómo Instalar y Ejecutar la Base de Datos

### Opción A: Instalador Web Automático en PHP (Recomendado)
1. Coloca la carpeta del proyecto en `htdocs` de XAMPP (ejemplo: `C:\xampp\htdocs\Proyecto.Taller.JJ`).
2. Inicia Apache y MySQL desde el panel de control de XAMPP.
3. Abre tu navegador web y entra a:
   ```
   http://localhost/Proyecto.Taller.JJ/php/instalar_db.php
   ```
4. Presiona el botón verde **"Crear Base de Datos e Importar Datos Iniciales"**.
5. ¡Listo! El script ejecutará el DDL, las tablas relacionales y todos los datos de prueba iniciales con confirmación visual.

### Opción B: Desde phpMyAdmin
1. Abre `http://localhost/phpmyadmin/`.
2. Ve a la pestaña **Importar**.
3. Selecciona el archivo:
   `sql/database.sql`
4. Presiona **Continuar / Importar**.

### Opción C: Desde la Consola / Terminal
```bash
mysql -u root -p < sql/database.sql
```

---

## 🔑 4. Usuarios de Prueba Precargados

| Rol | Nombre | Correo Electrónico | Contraseña |
|---|---|---|---|
| **Administrador** | Administrador General | `admin@misalononline.com` | `123456` |
| **Cliente VIP** | Julián Corsich | `julian.corsich@email.com` | `123456` |
| **Cliente** | José Montoya | `jose.montoya@email.com` | `123456` |
| **Cliente** | Florencia López | `florencia.lopez@email.com` | `123456` |
| **Empleado** | Carlos Gómez (Coordinador) | `carlos.gomez@misalononline.com` | `123456` |

---

## 📡 5. Capa de Servicios Backend en PHP (`/php/`)

* **`php/config/conexion.php`**: Conexión a MySQL mediante **PDO** con codificación `utf8mb4`, manejo de transacciones seguras y patrón Singleton.
* **`php/api/auth.php`**: Gestión de autenticación (registro, login, logout, verificación de sesión y restablecimiento de contraseña).
* **`php/api/salones.php`**: Catálogo de salones, imágenes de galería, cálculo de puntuación y publicación de reseñas.
* **`php/api/reservas.php`**: Creación de reservas con verificación automática de disponibilidad y solapamiento, cálculo de costo total con servicios extras, historial del cliente y cancelación con motivo.
* **`php/api/pagos.php`**: Registro de pagos (seña o total), verificación de saldo y actualización automática de estado.
* **`php/api/servicios.php`**: Lista de servicios adicionales y asignación de personal.
* **`php/api/reportes.php`**: Endpoint para el panel de administración con estadísticas y reportes contables.
