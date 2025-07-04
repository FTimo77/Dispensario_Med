
-- phpMyAdmin SQL Dump Limpio
-- Versión ajustada para reiniciar base sin perder estructura ni triggers

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Estructura de tabla para `lote`
DROP TABLE IF EXISTS `lote`;
CREATE TABLE `lote` (
  `NUM_LOTE` char(20) NOT NULL,
  `ID_PROODUCTO` int(11) DEFAULT NULL,
  `FECH_VENC` date NOT NULL,
  `FECH_FABRI` date NOT NULL,
  `FECHA_ING` date NOT NULL,
  `CANTIDAD_LOTE` int(11) NOT NULL,
  `estado_lote` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Triggers para `lote`
DELIMITER $$
CREATE TRIGGER `lote_set_estado_al_actualizar` BEFORE UPDATE ON `lote`
FOR EACH ROW BEGIN
    IF NEW.CANTIDAD_LOTE <= 0 OR NEW.FECH_VENC < CURDATE() THEN
        SET NEW.estado_lote = 0;
    END IF;
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `lote_set_estado_al_insertar` BEFORE INSERT ON `lote`
FOR EACH ROW BEGIN
    SET NEW.estado_lote = 1;
END
$$
DELIMITER ;

-- Índices para `lote`
ALTER TABLE `lote`
  ADD PRIMARY KEY (`NUM_LOTE`),
  ADD KEY `FK_RELATIONSHIP_3` (`ID_PROODUCTO`);

-- Estructura de tabla para `producto`
DROP TABLE IF EXISTS `producto`;
CREATE TABLE `producto` (
  `ID_PROODUCTO` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_PRODUCTO` varchar(100) NOT NULL,
  `DESCRIPCION` text,
  PRIMARY KEY (`ID_PROODUCTO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `bodega`
DROP TABLE IF EXISTS `bodega`;
CREATE TABLE `bodega` (
  `ID_BODEGA` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIPCION` varchar(100) NOT NULL,
  `ESTADO` varchar(1) DEFAULT '1',
  PRIMARY KEY (`ID_BODEGA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserta datos esenciales para bodega
INSERT INTO `bodega` (`ID_BODEGA`, `DESCRIPCION`, `ESTADO`) VALUES
(1, 'Quito Norte', '1');

-- Estructura de tabla para `usuario`
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `ID_USUARIO` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE_USUARIO` varchar(100) NOT NULL,
  `ROL` varchar(10) DEFAULT '1',
  PRIMARY KEY (`ID_USUARIO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserta datos esenciales para usuario
INSERT INTO `usuario` (`ID_USUARIO`, `NOMBRE_USUARIO`, `ROL`) VALUES
(1, 'admin', '1');

-- Clave foránea para `lote`
ALTER TABLE `lote`
  ADD CONSTRAINT `FK_RELATIONSHIP_3` FOREIGN KEY (`ID_PROODUCTO`) REFERENCES `producto` (`ID_PROODUCTO`);

COMMIT;
