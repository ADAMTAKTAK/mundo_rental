-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 10-04-2026 a las 13:11:16
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `reservacarros`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_Nueva_Reserva` (IN `p_ID_Cliente` INT, IN `p_ID_Vehiculo` INT, IN `p_Fecha_Salida` DATETIME, IN `p_Fecha_Devolucion_Prevista` DATETIME, IN `p_Monto_Alquiler` DECIMAL(10,2), IN `p_Servicios` BOOLEAN, IN `p_Accesorios` BOOLEAN)   BEGIN
    -- Insertamos los datos asegurando las reglas fijas de la agencia
    INSERT INTO alquileres (
        ID_Cliente, 
        ID_Vehiculo, 
        Fecha_Salida, 
        Fecha_Devolucion_Prevista, 
        Monto_Total, 
        Deposito_Garantia,   -- <-- REGLA FIJA
        Estado_Deposito,     -- <-- REGLA FIJA
        Estado, 
        Tiene_Servicios, 
        Tiene_Accesorios
    ) VALUES (
        p_ID_Cliente, 
        p_ID_Vehiculo, 
        p_Fecha_Salida, 
        p_Fecha_Devolucion_Prevista, 
        p_Monto_Alquiler, 
        100.00,              -- Siempre será $100.00 al crear
        'Retenido',          -- El depósito inicia como retenido
        'Reservado',         -- Estado por defecto
        p_Servicios, 
        p_Accesorios
    );
END$$

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `FN_Calcular_Extras_Contrato` (`p_ID_Alquiler` INT) RETURNS DECIMAL(10,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE v_total_servicios DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_total_accesorios DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_total_general DECIMAL(10,2) DEFAULT 0.00;

    -- Sumamos lo cobrado por servicios
    SELECT COALESCE(SUM(Precio_Cobrado), 0) INTO v_total_servicios 
    FROM alquiler_servicios 
    WHERE ID_Alquiler = p_ID_Alquiler;

    -- Sumamos lo cobrado por accesorios
    SELECT COALESCE(SUM(Precio_Cobrado), 0) INTO v_total_accesorios 
    FROM alquiler_accesorios 
    WHERE ID_Alquiler = p_ID_Alquiler;

    -- Suma total
    SET v_total_general = v_total_servicios + v_total_accesorios;

    RETURN v_total_general;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accesorios`
--

CREATE TABLE `accesorios` (
  `ID_Accesorio` int NOT NULL,
  `Nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Precio_Diario` decimal(10,2) NOT NULL,
  `Stock_Total` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `accesorios`
--

INSERT INTO `accesorios` (`ID_Accesorio`, `Nombre`, `Precio_Diario`, `Stock_Total`) VALUES
(1, 'Asiento de bebe', 10.00, 5),
(2, 'Base de bicicleta', 15.00, 3),
(3, 'Cava', 5.00, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquileres`
--

CREATE TABLE `alquileres` (
  `ID_Alquiler` int NOT NULL,
  `ID_Cliente` int DEFAULT NULL,
  `ID_Vehiculo` int DEFAULT NULL,
  `Fecha_Salida` datetime NOT NULL,
  `Fecha_Devolucion_Prevista` datetime NOT NULL,
  `Fecha_Devolucion_Real` datetime DEFAULT NULL,
  `Dias_Cobrados` int DEFAULT NULL,
  `Horas_Extra` int DEFAULT '0',
  `Monto_Horas_Extra` decimal(10,2) DEFAULT '0.00',
  `Monto_Total` decimal(10,2) DEFAULT NULL,
  `Deposito_Garantia` decimal(10,2) DEFAULT '100.00',
  `Estado_Deposito` enum('Retenido','Devuelto','Penalizado') COLLATE utf8mb4_general_ci DEFAULT 'Retenido',
  `Estado` enum('Reservado','En Curso','Finalizado','Cancelado') COLLATE utf8mb4_general_ci DEFAULT 'Reservado',
  `Tiene_Servicios` tinyint(1) DEFAULT '0',
  `Tiene_Accesorios` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alquileres`
--

INSERT INTO `alquileres` (`ID_Alquiler`, `ID_Cliente`, `ID_Vehiculo`, `Fecha_Salida`, `Fecha_Devolucion_Prevista`, `Fecha_Devolucion_Real`, `Dias_Cobrados`, `Horas_Extra`, `Monto_Horas_Extra`, `Monto_Total`, `Deposito_Garantia`, `Estado_Deposito`, `Estado`, `Tiene_Servicios`, `Tiene_Accesorios`) VALUES
(1, 2, 3, '2026-03-01 00:00:00', '2026-03-05 00:00:00', '2026-03-05 00:00:00', NULL, 0, 0.00, 550.00, 100.00, 'Retenido', 'Finalizado', 1, 1),
(2, 5, 2, '2026-03-10 00:00:00', '2026-03-15 00:00:00', NULL, NULL, 0, 0.00, 200.00, 100.00, 'Retenido', 'En Curso', 0, 0),
(3, 1, 10, '2026-03-20 00:00:00', '2026-03-23 00:00:00', NULL, NULL, 0, 0.00, 450.00, 100.00, 'Retenido', 'Reservado', 0, 0),
(4, 1, 11, '2026-04-10 04:05:00', '2026-04-13 04:05:00', NULL, NULL, 0, 0.00, 340.00, 100.00, 'Retenido', 'Reservado', 0, 0),
(5, 17, 11, '2026-04-14 04:16:00', '2026-04-17 04:16:00', NULL, NULL, 0, 0.00, 340.00, 100.00, 'Retenido', 'Reservado', 0, 0),
(12, 17, 11, '2026-04-29 18:57:00', '2026-04-30 19:27:00', '2026-04-09 19:12:36', 1, 0, 0.00, 255.00, 100.00, 'Retenido', 'Finalizado', 1, 1),
(13, 17, 11, '2026-05-07 23:25:00', '2026-05-08 23:56:00', '2026-04-09 19:34:42', 1, 0, 0.00, 230.00, 100.00, 'Retenido', 'Finalizado', 1, 0),
(14, 17, 1, '2026-04-20 18:31:00', '2026-04-21 19:01:00', NULL, 1, 0, 0.00, 172.00, 100.00, 'Retenido', 'Reservado', 0, 1),
(15, 17, 4, '2026-04-28 19:34:00', '2026-04-29 20:35:00', NULL, 1, 1, 10.00, 247.00, 100.00, 'Retenido', 'Reservado', 1, 0),
(16, 17, 8, '2026-11-09 19:36:00', '2026-11-10 20:36:00', '2026-04-09 19:37:24', 1, 1, 10.00, 252.00, 100.00, 'Retenido', 'Finalizado', 1, 0);

--
-- Disparadores `alquileres`
--
DELIMITER $$
CREATE TRIGGER `TRG_Calcular_Horas_Extra` BEFORE INSERT ON `alquileres` FOR EACH ROW BEGIN
    DECLARE v_precio_diario DECIMAL(10,2);
    DECLARE v_horas_totales INT;
    DECLARE v_dias INT;
    DECLARE v_horas_extra INT;
    DECLARE v_costo_horas_extra DECIMAL(10,2);

    -- 1. Buscar precio diario de ese vehículo para la fecha de salida
    SELECT Monto_Diario INTO v_precio_diario 
    FROM tarifas 
    WHERE ID_Vehiculo = NEW.ID_Vehiculo 
    AND DATE(NEW.Fecha_Salida) BETWEEN Fecha_Inicio AND Fecha_Fin 
    LIMIT 1;

    -- 2. Calcular horas totales usando tus columnas reales
    SET v_horas_totales = TIMESTAMPDIFF(HOUR, NEW.Fecha_Salida, NEW.Fecha_Devolucion_Prevista);
    
    -- 3. Desglose inicial
    SET v_dias = FLOOR(v_horas_totales / 24);
    SET v_horas_extra = v_horas_totales % 24;

    -- 4. Cobro mínimo de 1 día
    IF v_horas_totales < 24 THEN
        SET v_dias = 1;
        SET v_horas_extra = 0;
    END IF;

    -- 5. Lógica de Tope de Horas Extra ($10 c/u)
    SET v_costo_horas_extra = v_horas_extra * 10.00;
    
    IF v_costo_horas_extra >= v_precio_diario THEN
        SET v_dias = v_dias + 1;
        SET v_horas_extra = 0;
        SET v_costo_horas_extra = 0.00;
    END IF;

    -- 6. Insertar en tus columnas reales
    SET NEW.Dias_Cobrados = v_dias;
    SET NEW.Horas_Extra = v_horas_extra;
    SET NEW.Monto_Horas_Extra = v_costo_horas_extra;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_Liberar_Vehiculo` AFTER UPDATE ON `alquileres` FOR EACH ROW BEGIN
    -- Si el alquiler acaba de pasar a 'Finalizado' o fue 'Cancelado'
    IF (NEW.Estado = 'Finalizado' OR NEW.Estado = 'Cancelado') AND OLD.Estado NOT IN ('Finalizado', 'Cancelado') THEN
        UPDATE vehiculos SET Estado = 'Disponible' WHERE ID_Vehiculo = NEW.ID_Vehiculo;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_Ocupar_Vehiculo` AFTER UPDATE ON `alquileres` FOR EACH ROW BEGIN
    -- Si el alquiler acaba de pasar a 'En Curso'
    IF NEW.Estado = 'En Curso' AND OLD.Estado != 'En Curso' THEN
        UPDATE vehiculos SET Estado = 'Alquilado' WHERE ID_Vehiculo = NEW.ID_Vehiculo;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquiler_accesorios`
--

CREATE TABLE `alquiler_accesorios` (
  `ID_Alquiler` int NOT NULL,
  `ID_Accesorio` int NOT NULL,
  `Cantidad` int NOT NULL DEFAULT '1',
  `Precio_Cobrado` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alquiler_accesorios`
--

INSERT INTO `alquiler_accesorios` (`ID_Alquiler`, `ID_Accesorio`, `Cantidad`, `Precio_Cobrado`) VALUES
(1, 2, 1, 75.00),
(1, 3, 1, 25.00),
(12, 2, 1, 15.00),
(14, 1, 1, 10.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquiler_servicios`
--

CREATE TABLE `alquiler_servicios` (
  `ID_Alquiler` int NOT NULL,
  `ID_Servicio` int NOT NULL,
  `Precio_Cobrado` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alquiler_servicios`
--

INSERT INTO `alquiler_servicios` (`ID_Alquiler`, `ID_Servicio`, `Precio_Cobrado`) VALUES
(1, 2, 50.00),
(12, 2, 50.00),
(13, 2, 50.00),
(15, 2, 50.00),
(16, 2, 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `ID_Categoria` int NOT NULL,
  `Nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`ID_Categoria`, `Nombre`) VALUES
(1, 'Económicos'),
(2, 'Camionetas Grandes'),
(3, 'Gama Alta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `ID_Cliente` int NOT NULL,
  `Tipo_Documento` enum('V','E','J','G','P') COLLATE utf8mb4_general_ci NOT NULL,
  `Numero_Documento` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `Nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Apellido` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Licencia_Conducir` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`ID_Cliente`, `Tipo_Documento`, `Numero_Documento`, `Nombre`, `Apellido`, `Telefono`, `Licencia_Conducir`, `Email`) VALUES
(1, 'V', '28123456', 'Adam', 'Taktak', '0414-1234567', 'LIC-28123456', 'adam.taktak@email.com'),
(2, 'V', '27987654', 'Pablo', 'Guevara', '0424-9876543', 'LIC-27987654', 'pablo.guevara@email.com'),
(3, 'V', '26555444', 'Andres', 'Jimenez', '0412-5554444', 'LIC-26555444', 'andres.jimenez@email.com'),
(4, 'V', '29111222', 'Fabrizio', 'Marchioro', '0414-1112223', 'LIC-29111222', 'fabrizio.m@email.com'),
(5, 'V', '15888999', 'Veronica', 'Cardona', '0424-8889991', 'LIC-15888999', 'vcardona@unimar.edu.ve'),
(6, 'V', '20123123', 'Carlos', 'Perez', '0416-1111111', 'LIC-20123123', 'carlos.p@email.com'),
(7, 'V', '21234234', 'Maria', 'Gomez', '0412-2222222', 'LIC-21234234', 'maria.g@email.com'),
(8, 'E', '19345345', 'Jose', 'Rodriguez', '0424-3333333', 'LIC-19345345', 'jose.r@email.com'),
(9, 'V', '22456456', 'Ana', 'Martinez', '0414-4444444', 'LIC-22456456', 'ana.m@email.com'),
(10, 'V', '23567567', 'Luis', 'Hernandez', '0416-5555555', 'LIC-23567567', 'luis.h@email.com'),
(11, 'E', '24678678', 'Laura', 'Diaz', '0412-6666666', 'LIC-24678678', 'laura.d@email.com'),
(13, 'V', '26890890', 'Sofia', 'Lopez', '0414-8888888', 'LIC-26890890', 'sofia.l@email.com'),
(14, 'V', '27901901', 'Miguel', 'Gonzalez', '0416-9999999', 'LIC-27901901', 'miguel.g@email.com'),
(15, 'V', '28012012', 'Lucia', 'Fernandez', '0412-0000000', 'LIC-28012012', 'lucia.f@email.com'),
(16, 'V', '29123123', 'Jesus', 'Ruiz', '0424-1010101', 'LIC-29123123', 'jesus.r@email.com'),
(17, 'V', '30401549', 'abdl', 'taktak', '0414-7700507', 'LIC-233523', 'taktakabudi245@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `ID_Pago` int NOT NULL,
  `ID_Alquiler` int DEFAULT NULL,
  `Fecha_Pago` datetime NOT NULL,
  `Metodo_Pago` enum('Efectivo','Tarjeta','Transferencia','Pago Movil') COLLATE utf8mb4_general_ci NOT NULL,
  `Monto_Pagado` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`ID_Pago`, `ID_Alquiler`, `Fecha_Pago`, `Metodo_Pago`, `Monto_Pagado`) VALUES
(1, 1, '2026-03-01 09:00:00', 'Pago Movil', 275.00),
(2, 1, '2026-03-05 14:30:00', 'Transferencia', 275.00),
(3, 2, '2026-03-10 10:15:00', 'Tarjeta', 200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `ID_Servicio` int NOT NULL,
  `Nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Precio_Base` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`ID_Servicio`, `Nombre`, `Precio_Base`) VALUES
(1, 'Lavado de carro', 15.00),
(2, 'Chofer', 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `ID_Tarifa` int NOT NULL,
  `ID_Vehiculo` int DEFAULT NULL,
  `Monto_Diario` decimal(10,2) NOT NULL,
  `Fecha_Inicio` date NOT NULL,
  `Fecha_Fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`ID_Tarifa`, `ID_Vehiculo`, `Monto_Diario`, `Fecha_Inicio`, `Fecha_Fin`) VALUES
(1, 1, 55.00, '2026-03-12', '2026-04-05'),
(2, 2, 35.00, '2026-03-12', '2026-04-05'),
(3, 3, 55.00, '2026-03-12', '2026-04-05'),
(4, 4, 80.00, '2026-03-12', '2026-04-05'),
(5, 5, 50.00, '2026-03-12', '2026-04-05'),
(6, 6, 65.00, '2026-03-12', '2026-04-05'),
(7, 7, 40.00, '2026-03-12', '2026-04-05'),
(8, 8, 85.00, '2026-03-12', '2026-04-05'),
(9, 1, 62.00, '2026-04-05', '2026-06-15'),
(10, 2, 42.00, '2026-04-05', '2026-06-15'),
(11, 3, 62.00, '2026-04-05', '2026-06-15'),
(12, 4, 87.00, '2026-04-05', '2026-06-15'),
(13, 5, 57.00, '2026-04-05', '2026-06-15'),
(14, 6, 72.00, '2026-04-05', '2026-06-15'),
(15, 7, 47.00, '2026-04-05', '2026-06-15'),
(16, 8, 92.00, '2026-04-05', '2026-06-15'),
(17, 1, 65.00, '2026-06-16', '2026-07-31'),
(18, 2, 45.00, '2026-06-16', '2026-07-31'),
(19, 3, 65.00, '2026-06-16', '2026-07-31'),
(20, 4, 90.00, '2026-06-16', '2026-07-31'),
(21, 5, 60.00, '2026-06-16', '2026-07-31'),
(22, 6, 75.00, '2026-06-16', '2026-07-31'),
(23, 7, 50.00, '2026-06-16', '2026-07-31'),
(24, 8, 95.00, '2026-06-16', '2026-07-31'),
(25, 1, 50.00, '2026-08-01', '2026-10-25'),
(26, 2, 30.00, '2026-08-01', '2026-10-25'),
(27, 3, 50.00, '2026-08-01', '2026-10-25'),
(28, 4, 75.00, '2026-08-01', '2026-10-25'),
(29, 5, 45.00, '2026-08-01', '2026-10-25'),
(30, 6, 60.00, '2026-08-01', '2026-10-25'),
(31, 7, 35.00, '2026-08-01', '2026-10-25'),
(32, 8, 80.00, '2026-08-01', '2026-10-25'),
(33, 1, 55.00, '2026-10-26', '2026-12-31'),
(34, 2, 35.00, '2026-10-26', '2026-12-31'),
(35, 3, 55.00, '2026-10-26', '2026-12-31'),
(36, 4, 80.00, '2026-10-26', '2026-12-31'),
(37, 5, 50.00, '2026-10-26', '2026-12-31'),
(38, 6, 65.00, '2026-10-26', '2026-12-31'),
(39, 7, 40.00, '2026-10-26', '2026-12-31'),
(40, 8, 86.00, '2026-10-26', '2026-12-31'),
(41, 11, 80.00, '2026-04-08', '2026-05-08'),
(42, 11, 80.00, '2026-04-09', '2026-12-09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `ID_Usuario` int NOT NULL,
  `Username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `Nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Apellido` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Rol` enum('Admin','Cliente') COLLATE utf8mb4_general_ci NOT NULL,
  `ID_Cliente` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`ID_Usuario`, `Username`, `Password`, `Nombre`, `Apellido`, `Email`, `Rol`, `ID_Cliente`) VALUES
(1, 'admin', 'admin123', 'adam', 'taktak', 'adam@gmail.com', 'Admin', 1),
(2, 'alejo', 'flor', 'alejandrooo', 'castro', 'ajcastrocastro@gmail.com', 'Cliente', NULL),
(3, 'abudi', 'flores', 'abdl', 'taktak', 'taktakabudi245@gmail.com', 'Cliente', 17);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `ID_Vehiculo` int NOT NULL,
  `ID_Categoria` int DEFAULT NULL,
  `Placa` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `Marca` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Modelo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Anio` int NOT NULL,
  `Color` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Capacidad` tinyint NOT NULL,
  `Estado` enum('Disponible','Alquilado','Mantenimiento') COLLATE utf8mb4_general_ci DEFAULT 'Disponible',
  `Imagen_URL` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'assets/img/default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`ID_Vehiculo`, `ID_Categoria`, `Placa`, `Marca`, `Modelo`, `Anio`, `Color`, `Capacidad`, `Estado`, `Imagen_URL`) VALUES
(1, 1, 'AB123CD', 'Toyota', 'Yaris', 2022, 'Plata', 5, 'Disponible', 'assets/img/vehiculos/1775824987_TOYOTAYARISmlyqem.webp'),
(2, 1, 'EF456GH', 'Toyota', 'Corolla', 2023, 'Blanco', 5, 'Disponible', 'assets/img/vehiculos/1775825077_P24KNJ2V4VAHXFYOCY35F5G57A.avif'),
(3, 2, 'IJ789KL', 'Toyota', '4Runner', 2023, 'Negro', 7, 'Disponible', 'assets/img/vehiculos/1775825157_2023toyota4runnerpictureseixbhu40usow4hbh.jpg'),
(4, 1, 'MN012OP', 'Honda', 'Civic', 2021, 'Rojo', 5, 'Disponible', 'assets/img/vehiculos/1775824401_1775795944NAZb896903ed5984615b7cc4712755f3cdc.jpg'),
(5, 2, 'QR345ST', 'Honda', 'CR-V', 2022, 'Gris', 5, 'Disponible', 'assets/img/vehiculos/1775824510_311.webp'),
(6, 1, 'UV678WX', 'Hyundai', 'Elantra', 2022, 'Azul', 5, 'Disponible', 'assets/img/vehiculos/1775824603_maxresdefault1.jpg'),
(7, 2, 'YZ901AB', 'Hyundai', 'Tucson', 2023, 'Blanco', 5, 'Disponible', 'assets/img/vehiculos/1775824680_whitehyundaitucson20235354mainb54dd91a0035bc20e9b1c9f0a97ad7e7.jpg'),
(8, 1, 'CD234EF', 'Kia', 'Picanto', 2021, 'Amarillo', 4, 'Disponible', 'assets/img/vehiculos/1775824777_852x568116500x333.webp'),
(9, 2, 'GH567IJ', 'Kia', 'Sportage', 2023, 'Rojo', 5, 'Disponible', 'assets/img/vehiculos/1775824889_KIASPORTAGEpufybq.webp'),
(10, 3, 'KL890MN', 'Toyota', 'Land Cruiser', 2024, 'Blanco', 7, 'Disponible', 'assets/img/vehiculos/1775825250_images5.jpg'),
(11, 3, 'ABI23', 'Ford', 'Mustang', 2025, 'negro', 4, 'Disponible', 'assets/img/vehiculos/1775703432_images4.jpg');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_historial_rentas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_historial_rentas` (
`ID_Alquiler` int
,`Cliente` varchar(101)
,`Placa` varchar(20)
,`Vehiculo` varchar(101)
,`Fecha_Salida` datetime
,`Fecha_Devolucion_Real` datetime
,`Dias_Cobrados` int
,`Horas_Extra` int
,`Monto_Horas_Extra` decimal(10,2)
,`Monto_Total` decimal(10,2)
,`Estado_Deposito` enum('Retenido','Devuelto','Penalizado')
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_vehiculos_en_calle`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_vehiculos_en_calle` (
`ID_Alquiler` int
,`Placa` varchar(20)
,`Vehiculo` varchar(101)
,`Fecha_Salida` datetime
,`Fecha_Actual_Sistema` datetime
,`Dias_Transcurridos` decimal(21,0)
,`Horas_Transcurridas` bigint
);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `accesorios`
--
ALTER TABLE `accesorios`
  ADD PRIMARY KEY (`ID_Accesorio`);

--
-- Indices de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD PRIMARY KEY (`ID_Alquiler`),
  ADD KEY `ID_Cliente` (`ID_Cliente`),
  ADD KEY `ID_Vehiculo` (`ID_Vehiculo`);

--
-- Indices de la tabla `alquiler_accesorios`
--
ALTER TABLE `alquiler_accesorios`
  ADD PRIMARY KEY (`ID_Alquiler`,`ID_Accesorio`),
  ADD KEY `ID_Accesorio` (`ID_Accesorio`);

--
-- Indices de la tabla `alquiler_servicios`
--
ALTER TABLE `alquiler_servicios`
  ADD PRIMARY KEY (`ID_Alquiler`,`ID_Servicio`),
  ADD KEY `ID_Servicio` (`ID_Servicio`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`ID_Categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`ID_Cliente`),
  ADD UNIQUE KEY `Tipo_Documento` (`Tipo_Documento`,`Numero_Documento`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`ID_Pago`),
  ADD KEY `ID_Alquiler` (`ID_Alquiler`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`ID_Servicio`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`ID_Tarifa`),
  ADD KEY `ID_Vehiculo` (`ID_Vehiculo`),
  ADD KEY `idx_fechas_tarifa` (`Fecha_Inicio`,`Fecha_Fin`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID_Usuario`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `ID_Cliente` (`ID_Cliente`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`ID_Vehiculo`),
  ADD UNIQUE KEY `Placa` (`Placa`),
  ADD KEY `ID_Categoria` (`ID_Categoria`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `accesorios`
--
ALTER TABLE `accesorios`
  MODIFY `ID_Accesorio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  MODIFY `ID_Alquiler` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `ID_Categoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `ID_Cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `ID_Pago` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `ID_Servicio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `ID_Tarifa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID_Usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `ID_Vehiculo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_historial_rentas`
--
DROP TABLE IF EXISTS `vista_historial_rentas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_historial_rentas`  AS SELECT `a`.`ID_Alquiler` AS `ID_Alquiler`, concat(`c`.`Nombre`,' ',`c`.`Apellido`) AS `Cliente`, `v`.`Placa` AS `Placa`, concat(`v`.`Marca`,' ',`v`.`Modelo`) AS `Vehiculo`, `a`.`Fecha_Salida` AS `Fecha_Salida`, `a`.`Fecha_Devolucion_Real` AS `Fecha_Devolucion_Real`, `a`.`Dias_Cobrados` AS `Dias_Cobrados`, `a`.`Horas_Extra` AS `Horas_Extra`, `a`.`Monto_Horas_Extra` AS `Monto_Horas_Extra`, `a`.`Monto_Total` AS `Monto_Total`, `a`.`Estado_Deposito` AS `Estado_Deposito` FROM ((`alquileres` `a` join `clientes` `c` on((`a`.`ID_Cliente` = `c`.`ID_Cliente`))) join `vehiculos` `v` on((`a`.`ID_Vehiculo` = `v`.`ID_Vehiculo`))) WHERE (`a`.`Estado` = 'Finalizado') ORDER BY `a`.`Fecha_Devolucion_Real` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_vehiculos_en_calle`
--
DROP TABLE IF EXISTS `vista_vehiculos_en_calle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_vehiculos_en_calle`  AS SELECT `a`.`ID_Alquiler` AS `ID_Alquiler`, `v`.`Placa` AS `Placa`, concat(`v`.`Marca`,' ',`v`.`Modelo`) AS `Vehiculo`, `a`.`Fecha_Salida` AS `Fecha_Salida`, now() AS `Fecha_Actual_Sistema`, floor((timestampdiff(HOUR,`a`.`Fecha_Salida`,now()) / 24)) AS `Dias_Transcurridos`, (timestampdiff(HOUR,`a`.`Fecha_Salida`,now()) % 24) AS `Horas_Transcurridas` FROM (`alquileres` `a` join `vehiculos` `v` on((`a`.`ID_Vehiculo` = `v`.`ID_Vehiculo`))) WHERE (`a`.`Estado` = 'En Curso') ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD CONSTRAINT `alquileres_ibfk_1` FOREIGN KEY (`ID_Cliente`) REFERENCES `clientes` (`ID_Cliente`),
  ADD CONSTRAINT `alquileres_ibfk_2` FOREIGN KEY (`ID_Vehiculo`) REFERENCES `vehiculos` (`ID_Vehiculo`);

--
-- Filtros para la tabla `alquiler_accesorios`
--
ALTER TABLE `alquiler_accesorios`
  ADD CONSTRAINT `alquiler_accesorios_ibfk_1` FOREIGN KEY (`ID_Alquiler`) REFERENCES `alquileres` (`ID_Alquiler`),
  ADD CONSTRAINT `alquiler_accesorios_ibfk_2` FOREIGN KEY (`ID_Accesorio`) REFERENCES `accesorios` (`ID_Accesorio`);

--
-- Filtros para la tabla `alquiler_servicios`
--
ALTER TABLE `alquiler_servicios`
  ADD CONSTRAINT `alquiler_servicios_ibfk_1` FOREIGN KEY (`ID_Alquiler`) REFERENCES `alquileres` (`ID_Alquiler`),
  ADD CONSTRAINT `alquiler_servicios_ibfk_2` FOREIGN KEY (`ID_Servicio`) REFERENCES `servicios` (`ID_Servicio`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`ID_Alquiler`) REFERENCES `alquileres` (`ID_Alquiler`);

--
-- Filtros para la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD CONSTRAINT `tarifas_ibfk_1` FOREIGN KEY (`ID_Vehiculo`) REFERENCES `vehiculos` (`ID_Vehiculo`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`ID_Cliente`) REFERENCES `clientes` (`ID_Cliente`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`ID_Categoria`) REFERENCES `categorias` (`ID_Categoria`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
