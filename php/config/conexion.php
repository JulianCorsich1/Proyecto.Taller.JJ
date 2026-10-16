<?php
/**
 * Configuración de Conexión a la Base de Datos con PDO (PHP Data Objects)
 * Proyecto: MiSalónOnline
 * Manual de Usuario: "Alquiler de Salon Manual de usuario.docx"
 */

// Parámetros de conexión configurables (valores típicos para XAMPP / Wamp / Servidor Local)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'misalononline');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Conexion {
    private static ?PDO $instancia = null;

    /**
     * Obtiene una instancia única de la conexión PDO (Patrón Singleton)
     * @return PDO
     * @throws PDOException
     */
    public static function getConexion(): PDO {
        if (self::$instancia === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            try {
                self::$instancia = new PDO($dsn, DB_USER, DB_PASS, $opciones);
            } catch (PDOException $e) {
                // Registro y mensaje descriptivo
                error_log("Error de conexión a la BD: " . $e->getMessage());
                throw new PDOException("No se pudo conectar a la base de datos 'misalononline'. Verifique que MySQL esté iniciado y las credenciales sean correctas.");
            }
        }

        return self::$instancia;
    }

    /**
     * Conexión al servidor sin especificar base de datos (usado para instaladores/creación inicial)
     */
    public static function getConexionServidor(): PDO {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $opciones);
    }
}

/**
 * Función auxiliar para obtener conexión PDO de manera sencilla
 */
function db(): PDO {
    return Conexion::getConexion();
}
