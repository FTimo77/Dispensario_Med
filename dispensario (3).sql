-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2025 at 04:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dispensario`
--

-- --------------------------------------------------------

--
-- Table structure for table `bodega`
--

CREATE TABLE `bodega` (
  `CODIGO_BODEGA` int(11) NOT NULL,
  `DESCRIPCION` char(50) NOT NULL,
  `ESTADO_BODEGA` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bodega`
--

INSERT INTO `bodega` (`CODIGO_BODEGA`, `DESCRIPCION`, `ESTADO_BODEGA`) VALUES
(1, 'Quito Norte', '1'),
(2, 'Quito Sur', '1');

-- --------------------------------------------------------

--
-- Table structure for table `cabecera`
--

CREATE TABLE `cabecera` (
  `COD_TRANSAC` int(11) NOT NULL,
  `FECHA_TRANSC` datetime NOT NULL,
  `MOTIVO` char(50) NOT NULL,
  `PACIENTE` varchar(50) DEFAULT NULL,
  `TIPO_TRANSAC` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cabecera`
--

INSERT INTO `cabecera` (`COD_TRANSAC`, `FECHA_TRANSC`, `MOTIVO`, `PACIENTE`, `TIPO_TRANSAC`) VALUES
(1, '2025-06-23 03:52:54', 'Faltate en bodega', '', 'INGRESO'),
(2, '2025-06-23 04:19:30', 'Stock  casi vacio', '', 'INGRESO'),
(3, '2025-06-23 04:21:32', 'Paciente Juan Perez, dolor cabeza', '', 'EGRESO'),
(4, '2025-06-23 21:44:18', 'Ingreso pr falta de  stock', '', 'INGRESO'),
(5, '2025-06-24 21:46:06', 'asdf', ' ', 'INGRESO'),
(7, '2025-06-24 21:53:22', 'Prueba', ' ', 'INGRESO'),
(8, '2025-06-24 22:37:42', 'motivo tempra', NULL, 'INGRESO'),
(9, '2025-06-25 15:40:44', '', 'Proveedor', 'INGRESO'),
(10, '2025-06-25 15:43:31', '', 'referencia de nebulizador', 'INGRESO');

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE `categoria` (
  `ID_CATEGORIA` int(11) NOT NULL,
  `NOMBRE_CAT` char(50) NOT NULL,
  `ESTADO_CAT` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categoria`
--

INSERT INTO `categoria` (`ID_CATEGORIA`, `NOMBRE_CAT`, `ESTADO_CAT`) VALUES
(1, 'Medicamento', 1),
(2, 'Insumo', 1);

-- --------------------------------------------------------

--
-- Table structure for table `kardex`
--

CREATE TABLE `kardex` (
  `ID_KARDEX` int(11) NOT NULL,
  `ID_PROODUCTO` int(11) DEFAULT NULL,
  `COD_TRANSAC` int(11) DEFAULT NULL,
  `ID_USUARIO` int(11) DEFAULT NULL,
  `CANTIDAD` decimal(5,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kardex`
--

INSERT INTO `kardex` (`ID_KARDEX`, `ID_PROODUCTO`, `COD_TRANSAC`, `ID_USUARIO`, `CANTIDAD`) VALUES
(1, 1, 1, 2, 60),
(2, 2, 2, 2, 36),
(3, 2, 3, 2, 10),
(4, 5, 4, 2, 50),
(5, 1, 5, 2, 32),
(6, 2, 7, 2, 60),
(7, 2, 8, 2, 82),
(8, 2, 9, 2, 120),
(9, 4, 10, 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `COD_LOG` int(11) NOT NULL,
  `ID_USUARIO` int(11) DEFAULT NULL,
  `FECHA_LOG` datetime NOT NULL,
  `ACCION_USUARIO` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lote`
--

CREATE TABLE `lote` (
  `NUM_LOTE` char(20) NOT NULL,
  `ID_PROODUCTO` int(11) DEFAULT NULL,
  `FECH_VENC` date NOT NULL,
  `FECH_FABRI` date NOT NULL,
  `FECHA_ING` date NOT NULL,
  `CANTIDAD_LOTE` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lote`
--

INSERT INTO `lote` (`NUM_LOTE`, `ID_PROODUCTO`, `FECH_VENC`, `FECH_FABRI`, `FECHA_ING`, `CANTIDAD_LOTE`) VALUES
('1234', 1, '2027-10-01', '2024-06-01', '2025-06-24', 0),
('L002', 1, '2027-07-01', '2024-02-01', '2025-06-23', 0),
('L450246', 1, '2026-01-01', '2025-05-01', '2025-06-18', 0),
('Linecciones456', 5, '2028-06-01', '2024-02-01', '2025-06-23', 0),
('LoteParacetamol', 2, '2027-10-01', '2024-02-01', '2025-06-24', 0),
('LT001', 2, '2027-06-01', '2024-02-01', '2025-06-24', 0),
('Ltempra123', 2, '2027-07-01', '2023-11-01', '2025-06-23', 0),
('NB777', 4, '2027-10-01', '2024-08-01', '2025-06-25', 0),
('T005', 2, '2027-06-01', '2024-03-01', '2025-06-25', 0);

-- --------------------------------------------------------

--
-- Table structure for table `producto`
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
-- Dumping data for table `producto`
--

INSERT INTO `producto` (`ID_PROODUCTO`, `ID_CATEGORIA`, `CODIGO_BODEGA`, `PRESENTACION_PROD`, `NOM_PROD`, `STOCK_ACT_PROD`, `STOCK_MIN_PROD`, `ESTADO_PROD`) VALUES
(1, 1, 1, 'Caja 6 unidades', 'Paracetamol', 92, 1, 1),
(2, 1, 2, '18 unidades', 'Tempra', 288, 1, 1),
(3, 1, 2, 'Caja 36 unidades', 'Umbral', 0, 20, 1),
(4, 2, 1, '3 mascarillas', 'Nebulizador', 3, 2, 1),
(5, 2, 2, 'Caja de 12 unidades', 'Inyecciones 20ml', 50, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `rol_usuario`
--

CREATE TABLE `rol_usuario` (
  `COD_ROL` int(11) NOT NULL,
  `NOMBRE_ROL` char(20) NOT NULL,
  `ESTADO_ROL` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rol_usuario`
--

INSERT INTO `rol_usuario` (`COD_ROL`, `NOMBRE_ROL`, `ESTADO_ROL`) VALUES
(1, 'admin', 'A'),
(2, 'doctor', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `ID_USUARIO` int(11) NOT NULL,
  `COD_ROL` int(11) DEFAULT NULL,
  `NOMBRE_USUARIO` char(20) NOT NULL,
  `PASS_USUARIO` char(16) NOT NULL,
  `ESTADO_USUARIO` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`ID_USUARIO`, `COD_ROL`, `NOMBRE_USUARIO`, `PASS_USUARIO`, `ESTADO_USUARIO`) VALUES
(2, 2, 'aLagla', '1234', '1'),
(15, 1, 'mMeza', 'aaaa', '1'),
(16, 1, 'aaaa', 'aaaa', '0');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bodega`
--
ALTER TABLE `bodega`
  ADD PRIMARY KEY (`CODIGO_BODEGA`);

--
-- Indexes for table `cabecera`
--
ALTER TABLE `cabecera`
  ADD PRIMARY KEY (`COD_TRANSAC`);

--
-- Indexes for table `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`ID_CATEGORIA`);

--
-- Indexes for table `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`ID_KARDEX`),
  ADD KEY `FK_RELATIONSHIP_2` (`ID_PROODUCTO`),
  ADD KEY `FK_RELATIONSHIP_4` (`COD_TRANSAC`),
  ADD KEY `FK_RELATIONSHIP_7` (`ID_USUARIO`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`COD_LOG`),
  ADD KEY `FK_RELATIONSHIP_8` (`ID_USUARIO`);

--
-- Indexes for table `lote`
--
ALTER TABLE `lote`
  ADD PRIMARY KEY (`NUM_LOTE`),
  ADD KEY `FK_RELATIONSHIP_3` (`ID_PROODUCTO`);

--
-- Indexes for table `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`ID_PROODUCTO`),
  ADD KEY `FK_RELATIONSHIP_10` (`CODIGO_BODEGA`),
  ADD KEY `FK_RELATIONSHIP_5` (`ID_CATEGORIA`);

--
-- Indexes for table `rol_usuario`
--
ALTER TABLE `rol_usuario`
  ADD PRIMARY KEY (`COD_ROL`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_USUARIO`),
  ADD UNIQUE KEY `NOMBRE_USUARIO_UNIQUE` (`NOMBRE_USUARIO`),
  ADD KEY `FK_RELATIONSHIP_9` (`COD_ROL`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bodega`
--
ALTER TABLE `bodega`
  MODIFY `CODIGO_BODEGA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cabecera`
--
ALTER TABLE `cabecera`
  MODIFY `COD_TRANSAC` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categoria`
--
ALTER TABLE `categoria`
  MODIFY `ID_CATEGORIA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kardex`
--
ALTER TABLE `kardex`
  MODIFY `ID_KARDEX` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `COD_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `producto`
--
ALTER TABLE `producto`
  MODIFY `ID_PROODUCTO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rol_usuario`
--
ALTER TABLE `rol_usuario`
  MODIFY `COD_ROL` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_USUARIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `FK_RELATIONSHIP_2` FOREIGN KEY (`ID_PROODUCTO`) REFERENCES `producto` (`ID_PROODUCTO`),
  ADD CONSTRAINT `FK_RELATIONSHIP_4` FOREIGN KEY (`COD_TRANSAC`) REFERENCES `cabecera` (`COD_TRANSAC`),
  ADD CONSTRAINT `FK_RELATIONSHIP_7` FOREIGN KEY (`ID_USUARIO`) REFERENCES `usuario` (`ID_USUARIO`);

--
-- Constraints for table `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `FK_RELATIONSHIP_8` FOREIGN KEY (`ID_USUARIO`) REFERENCES `usuario` (`ID_USUARIO`);

--
-- Constraints for table `lote`
--
ALTER TABLE `lote`
  ADD CONSTRAINT `FK_RELATIONSHIP_3` FOREIGN KEY (`ID_PROODUCTO`) REFERENCES `producto` (`ID_PROODUCTO`);

--
-- Constraints for table `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `FK_RELATIONSHIP_10` FOREIGN KEY (`CODIGO_BODEGA`) REFERENCES `bodega` (`CODIGO_BODEGA`),
  ADD CONSTRAINT `FK_RELATIONSHIP_5` FOREIGN KEY (`ID_CATEGORIA`) REFERENCES `categoria` (`ID_CATEGORIA`);

--
-- Constraints for table `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_RELATIONSHIP_9` FOREIGN KEY (`COD_ROL`) REFERENCES `rol_usuario` (`COD_ROL`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
