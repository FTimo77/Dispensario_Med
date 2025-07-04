-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-07-2025 a las 17:10:11
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dispensario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bodega`
--

CREATE TABLE `bodega` (
  `CODIGO_BODEGA` int(11) NOT NULL,
  `DESCRIPCION` char(50) NOT NULL,
  `ESTADO_BODEGA` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bodega`
--

INSERT INTO `bodega` (`CODIGO_BODEGA`, `DESCRIPCION`, `ESTADO_BODEGA`) VALUES
(1, 'Quito Norte', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cabecera`
--

CREATE TABLE `cabecera` (
  `COD_TRANSAC` int(11) NOT NULL,
  `FECHA_TRANSC` datetime NOT NULL,
  `MOTIVO` char(50) NOT NULL,
  `TIPO_TRANSAC` char(50) NOT NULL,
  `id_paciente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `ID_CATEGORIA` int(11) NOT NULL,
  `NOMBRE_CAT` char(50) NOT NULL,
  `ESTADO_CAT` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--
-- Estructura de tabla para la tabla `kardex`
--

CREATE TABLE `kardex` (
  `ID_KARDEX` int(11) NOT NULL,
  `ID_PROODUCTO` int(11) DEFAULT NULL,
  `COD_TRANSAC` int(11) DEFAULT NULL,
  `ID_USUARIO` int(11) DEFAULT NULL,
  `CANTIDAD` decimal(5,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE `log` (
  `COD_LOG` int(11) NOT NULL,
  `ID_USUARIO` int(11) DEFAULT NULL,
  `FECHA_LOG` datetime NOT NULL,
  `ACCION_USUARIO` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lote`
--

CREATE TABLE `lote` (
  `NUM_LOTE` char(20) NOT NULL,
  `ID_PROODUCTO` int(11) DEFAULT NULL,
  `FECH_VENC` date NOT NULL,
  `FECH_FABRI` date NOT NULL,
  `FECHA_ING` date NOT NULL,
  `CANTIDAD_LOTE` int(11) NOT NULL,
  `estado_lote` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `lote`
--
DELIMITER $$
CREATE TRIGGER `lote_set_estado_al_actualizar` BEFORE UPDATE ON `lote` FOR EACH ROW BEGIN
    IF NEW.CANTIDAD_LOTE <= 0 OR NEW.FECH_VENC < CURDATE() THEN
        SET NEW.estado_lote = 0;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `lote_set_estado_al_insertar` BEFORE INSERT ON `lote` FOR EACH ROW BEGIN
    SET NEW.estado_lote = 1;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int(11) NOT NULL,
  `nombre_paciente` varchar(50) DEFAULT NULL,
  `apellido_paciente` varchar(50) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `est_paciente` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `ID_PROODUCTO` int(11) NOT NULL,
  `ID_CATEGORIA` int(11) DEFAULT NULL,
  `CODIGO_BODEGA` int(11) DEFAULT NULL,
  `PRESENTACION_PROD` char(20) NOT NULL,
  `NOM_PROD` char(60) NOT NULL,
  `STOCK_ACT_PROD` decimal(3,0) NOT NULL,
  `STOCK_MIN_PROD` decimal(3,0) NOT NULL,
  `ESTADO_PROD` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--
-- Estructura de tabla para la tabla `rol_usuario`
--

CREATE TABLE `rol_usuario` (
  `COD_ROL` int(11) NOT NULL,
  `NOMBRE_ROL` char(20) NOT NULL,
  `ESTADO_ROL` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_usuario`
--

INSERT INTO `rol_usuario` (`COD_ROL`, `NOMBRE_ROL`, `ESTADO_ROL`) VALUES
(1, 'admin', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `ID_USUARIO` int(11) NOT NULL,
  `COD_ROL` int(11) DEFAULT NULL,
  `NOMBRE_USUARIO` char(20) NOT NULL,
  `PASS_USUARIO` char(16) NOT NULL,
  `ESTADO_USUARIO` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`ID_USUARIO`, `COD_ROL`, `NOMBRE_USUARIO`, `PASS_USUARIO`, `ESTADO_USUARIO`) VALUES
(1, 1, 'admin', 'admin', '1');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bodega`
--
ALTER TABLE `bodega`
  ADD PRIMARY KEY (`CODIGO_BODEGA`);

--
-- Indices de la tabla `cabecera`
--
ALTER TABLE `cabecera`
  ADD PRIMARY KEY (`COD_TRANSAC`),
  ADD KEY `fk_paciente` (`id_paciente`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`ID_CATEGORIA`);

--
-- Indices de la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`ID_KARDEX`),
  ADD KEY `FK_RELATIONSHIP_2` (`ID_PROODUCTO`),
  ADD KEY `FK_RELATIONSHIP_4` (`COD_TRANSAC`),
  ADD KEY `FK_RELATIONSHIP_7` (`ID_USUARIO`);

--
-- Indices de la tabla `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`COD_LOG`),
  ADD KEY `FK_RELATIONSHIP_8` (`ID_USUARIO`);

--
-- Indices de la tabla `lote`
--
ALTER TABLE `lote`
  ADD PRIMARY KEY (`NUM_LOTE`),
  ADD KEY `FK_RELATIONSHIP_3` (`ID_PROODUCTO`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`ID_PROODUCTO`),
  ADD KEY `FK_RELATIONSHIP_10` (`CODIGO_BODEGA`),
  ADD KEY `FK_RELATIONSHIP_5` (`ID_CATEGORIA`);

--
-- Indices de la tabla `rol_usuario`
--
ALTER TABLE `rol_usuario`
  ADD PRIMARY KEY (`COD_ROL`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_USUARIO`),
  ADD UNIQUE KEY `NOMBRE_USUARIO_UNIQUE` (`NOMBRE_USUARIO`),
  ADD KEY `FK_RELATIONSHIP_9` (`COD_ROL`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bodega`
--
ALTER TABLE `bodega`
  MODIFY `CODIGO_BODEGA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cabecera`
--
ALTER TABLE `cabecera`
  MODIFY `COD_TRANSAC` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `ID_CATEGORIA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `kardex`
--
ALTER TABLE `kardex`
  MODIFY `ID_KARDEX` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `log`
--
ALTER TABLE `log`
  MODIFY `COD_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `ID_PROODUCTO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `rol_usuario`
--
ALTER TABLE `rol_usuario`
  MODIFY `COD_ROL` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_USUARIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cabecera`
--
ALTER TABLE `cabecera`
  ADD CONSTRAINT `fk_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

--
-- Filtros para la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `FK_RELATIONSHIP_2` FOREIGN KEY (`ID_PROODUCTO`) REFERENCES `producto` (`ID_PROODUCTO`),
  ADD CONSTRAINT `FK_RELATIONSHIP_4` FOREIGN KEY (`COD_TRANSAC`) REFERENCES `cabecera` (`COD_TRANSAC`),
  ADD CONSTRAINT `FK_RELATIONSHIP_7` FOREIGN KEY (`ID_USUARIO`) REFERENCES `usuario` (`ID_USUARIO`);

--
-- Filtros para la tabla `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `FK_RELATIONSHIP_8` FOREIGN KEY (`ID_USUARIO`) REFERENCES `usuario` (`ID_USUARIO`);

--
-- Filtros para la tabla `lote`
--
ALTER TABLE `lote`
  ADD CONSTRAINT `FK_RELATIONSHIP_3` FOREIGN KEY (`ID_PROODUCTO`) REFERENCES `producto` (`ID_PROODUCTO`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `FK_RELATIONSHIP_10` FOREIGN KEY (`CODIGO_BODEGA`) REFERENCES `bodega` (`CODIGO_BODEGA`),
  ADD CONSTRAINT `FK_RELATIONSHIP_5` FOREIGN KEY (`ID_CATEGORIA`) REFERENCES `categoria` (`ID_CATEGORIA`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_RELATIONSHIP_9` FOREIGN KEY (`COD_ROL`) REFERENCES `rol_usuario` (`COD_ROL`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
