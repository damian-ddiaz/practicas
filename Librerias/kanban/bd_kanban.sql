-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-07-2024 a las 20:53:33
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_kanban`
--
CREATE DATABASE IF NOT EXISTS `bd_kanban` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `bd_kanban`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `board`
--

CREATE TABLE `board` (
  `brdid` int(11) UNSIGNED NOT NULL,
  `brdnombre` varchar(255) DEFAULT NULL,
  `brdorden` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `board`
--

INSERT INTO `board` (`brdid`, `brdnombre`, `brdorden`) VALUES
(1, 'Contacto', 1),
(2, 'Cotización', 2),
(3, 'Negociación', 3),
(4, 'Finalizado', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `board_item`
--

CREATE TABLE `board_item` (
  `briid` int(11) NOT NULL,
  `bri_brdid` int(10) UNSIGNED NOT NULL,
  `brititulo` varchar(255) DEFAULT NULL,
  `bricliente` varchar(255) DEFAULT NULL,
  `brivendedor` varchar(255) DEFAULT NULL,
  `brivalor` float DEFAULT NULL,
  `briinicio` date DEFAULT NULL,
  `brifinal` date DEFAULT NULL,
  `briposicion` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `board_item`
--

INSERT INTO `board_item` (`briid`, `bri_brdid`, `brititulo`, `bricliente`, `brivendedor`, `brivalor`, `briinicio`, `brifinal`, `briposicion`) VALUES
(1, 2, 'Sistema de Salud ad', 'Salud Ltda', 'Bruno', 38946, '2024-05-18', NULL, 0),
(2, 3, 'Sistema GTD', 'Productiva de Servicios', 'Bruno', 66784.1, '2024-03-18', NULL, 0),
(3, 2, '65465465', 'Cliente Prueba', 'Vendedor Prueba', 30000, '2024-05-31', '2024-06-10', 1),
(4, 1, 'ITEM EDITADO l', 'Cliente Prueba', 'Vendedor Prueba', 2500, '2024-05-31', '2024-06-10', 0),
(5, 4, 'prueba insert', 'Cliente Prueba', 'Vendedor Prueba', 20, '2024-05-31', '2024-06-10', 0),
(7, 4, 'prueba insert', 'Cliente Prueba', 'Vendedor Prueba', 20, '2024-05-31', '2024-06-10', 4),
(9, 4, 'prueba insert', 'Cliente Prueba', 'Vendedor Prueba', 10, '2024-05-31', '2024-06-10', 5),
(11, 4, 'prueba insert', 'Cliente Prueba', 'Vendedor Prueba', 0, '2024-05-31', '2024-06-10', 1),
(13, 4, 'prueba insert', 'Cliente Prueba', 'Vendedor Prueba', 0, '2024-05-31', '2024-06-10', 2),
(14, 4, 'prueba insert', 'Cliente Prueba', 'Vendedor Prueba', 0, '2024-05-31', '2024-06-10', 3),
(25, 1, 'insert prueba', 'Cliente Prueba', 'Vendedor Prueba', 0, '2024-07-03', '2024-07-13', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `board`
--
ALTER TABLE `board`
  ADD PRIMARY KEY (`brdid`);

--
-- Indices de la tabla `board_item`
--
ALTER TABLE `board_item`
  ADD PRIMARY KEY (`briid`),
  ADD KEY `fk_bri_brd1_idx` (`bri_brdid`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `board`
--
ALTER TABLE `board`
  MODIFY `brdid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `board_item`
--
ALTER TABLE `board_item`
  MODIFY `briid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `board_item`
--
ALTER TABLE `board_item`
  ADD CONSTRAINT `fk_bri_brd1` FOREIGN KEY (`bri_brdid`) REFERENCES `board` (`brdid`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
