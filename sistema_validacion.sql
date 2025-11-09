-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-11-2025 a las 04:24:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_validacion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acceso_externo`
--

CREATE TABLE `acceso_externo` (
  `id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `valido_hasta` datetime DEFAULT NULL,
  `usado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `acceso_externo`
--

INSERT INTO `acceso_externo` (`id`, `token`, `valido_hasta`, `usado`) VALUES
(1, '692d51ae73c85dfedc04551860eb63ca', '2025-11-09 23:08:39', 0),
(2, '9b7e31de7f1c9db68ccd2176fef9c2e4', '2025-11-09 23:09:11', 0),
(3, '2209038c06b8b5982c728ca7be7143bf', '2025-11-09 23:12:56', 1),
(4, '7e97719985821f5268f6e0e333aad409', '2025-11-09 23:28:42', 1),
(5, 'aa08d74fb42d05bc037fca20c7ee78ce', '2025-11-09 23:38:25', 0),
(6, '70c644c04a33258046b8a3ad2f61586f', '2025-11-09 23:47:42', 1),
(7, '99948755432e6d9e11f7efc27fe86046', '2025-11-09 23:55:24', 1),
(8, '17683424aa619366da8665fb8ce03149', '2025-11-10 00:20:01', 0),
(9, '30d1a0486ae17769ffb1acc6834d8281', '2025-11-10 00:26:09', 0),
(10, '5951f16b75325b8d04869d98271bb9d1', '2025-11-10 00:33:36', 1),
(11, 'b3649118018ba5674eec55392fff5d61', '2025-11-10 00:42:59', 0),
(12, '35eae46356dac8dc2f1f8824d83dd3bd', '2025-11-10 16:01:15', 0),
(13, 'dd740ed0b5b129fab3b2bd7484b06d6f', '2025-11-10 16:16:03', 0),
(14, 'eafb2d76195bcc7ee26a655fe84bae60', '2025-11-10 23:39:55', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `ID_Admin` varchar(50) NOT NULL,
  `Legajo` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `first_login_done` tinyint(1) NOT NULL DEFAULT 0,
  `ID_Rol` int(11) NOT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admin`
--

INSERT INTO `admin` (`ID_Admin`, `Legajo`, `Nombre`, `Apellido`, `Email`, `Password`, `first_login_done`, `ID_Rol`, `reset_token_hash`, `reset_token_expires_at`) VALUES
('lione_29646', 29646, 'Sol', 'Puello Lione', 'spuello646@alumnos.frh.utn.edu.ar', '$2y$10$1kUqQds0wHVN4uQyrtoVOONSyuZJl9f//BE7ps4E8NBDC1GBuk7b2', 1, 1, NULL, NULL),
('mastroianni_91196', 91196, 'Juan Ignacio', 'Mastroianni', 'jmastroianni@frh.utn.edu.ar', '$2y$10$MnXHKQN.82zWvo4AKNfQbuYdtddyDWaS8UVaER/zkS62Hlm5Giony', 0, 3, NULL, NULL),
('matta_30184', 30184, 'Tomas', 'Matta Palladino', 'tmatta184@alumnos.frh.utn.edu.ar', '$2y$10$Gs3kZ2i73onQqxWDv8qOu.bQAecDdHgtQhXCLRrgm7Jtu2k5BcuMC', 1, 1, NULL, NULL),
('perez_30226', 30226, 'Valentin', 'Perez', 'vperez226@alumnos.frh.utn.edu.ar', '$2y$10$UyFjwYtRZWDERu.pkLiIeei9AsIaEk4w0JJUBZbYjYEPhNi9QtiTW', 0, 1, NULL, NULL),
('soria_30270', 30270, 'Daiana', 'Soria Piola', 'dsoria270@alumnos.frh.utn.edu.ar', '$2y$10$sKRHJd9gVu0R9K4ngZ/LDuXMx/mvU5iI8k.cuJXFHrlII5XHEgUJG', 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `ID_Cuil_Alumno` bigint(20) NOT NULL,
  `DNI_Alumno` int(11) NOT NULL,
  `Nombre_Alumno` varchar(50) NOT NULL,
  `Apellido_Alumno` varchar(50) NOT NULL,
  `Email_Alumno` varchar(100) NOT NULL,
  `Direccion` varchar(100) NOT NULL,
  `Telefono` varchar(30) NOT NULL,
  `ID_Rol` int(11) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `first_login_done` tinyint(1) NOT NULL DEFAULT 0,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alumno`
--

INSERT INTO `alumno` (`ID_Cuil_Alumno`, `DNI_Alumno`, `Nombre_Alumno`, `Apellido_Alumno`, `Email_Alumno`, `Direccion`, `Telefono`, `ID_Rol`, `Password`, `first_login_done`, `reset_token_hash`, `reset_token_expires_at`) VALUES
(20431223444, 43122344, 'Mateo', 'Fernández', 'mateo.fernandez@gmail.com', 'Calle San Juan 856', '1132345678', 2, '$2y$10$EfMAATw/SksyrCRx9cuNceCAOBuXrX8Az0ka.FkxZdP6SRIBCpJn2', 1, NULL, NULL),
(20433456784, 43345678, 'Felipe', 'Castro', 'felipe.castro@gmail.com', 'Calle Independencia 900', '1120000010', 2, '$2y$10$U1NcKW9E7NAjcSABZiATdeaACJ5HhyY3cCFXS4EKVe8HLQb5fqxC6', 1, NULL, NULL),
(20438901234, 43890123, 'Benjamín', 'Herrera', 'benjamin.herrera@gmail.com', 'Calle Belgrano 980', '1120000006', 2, '$2y$10$UO3l32cSoNsHjkfyMX7ZC.NP.J/u.kZbZsJCVtRtRXqqysU7UMa0i', 0, NULL, NULL),
(20441223454, 44122345, 'Luciano', 'Santos', 'luciano.santos@gmail.com', 'Calle Moreno 1200', '1190123456', 2, '$2y$10$V0v4JQZWh4HcZC4D.GGDZuqyIH5/KZOCgi.Lxa.W.kXAV9XwKBXcG', 0, NULL, NULL),
(20447654324, 44765432, 'Santiago', 'López', 'santiago.lopez@gmail.com', 'Calle Tucumán 1450', '1156789012', 2, '$2y$10$Zun7NAVH22NF6Ah8NiAiw.Z6dMe3Docij/8/UW6n02HufsasYQGhK', 0, NULL, NULL),
(20450987654, 45098765, 'Franco', 'Domínguez', 'franco.dominguez@gmail.com', 'Calle Pueyrredón 600', '1112345678', 2, '$2y$10$agt2AzVv6QBeAlIOif2VmOly1Mamr3U.xr/5hJ6FOLWeppNyyCobC', 0, NULL, NULL),
(20451234564, 45123456, 'Tomás', 'Navarro', 'tomas.navarro@gmail.com', 'Calle Jujuy 660', '1120000008', 2, '$2y$10$QEVsEJPfIJjOnZsQESgbNOqOlUY5QfoHXYRmeXWnz5TAGClAnsI7i', 0, NULL, NULL),
(20457890124, 45789012, 'Agustín', 'Méndez', 'agustin.mendez@gmail.com', 'Calle Lavalle 1045', '1120000002', 2, '$2y$10$rCEWgeXB9.zVC/k8V5vW8e/ymf1B56zpAl5O6U8z4tD5cOkbo93bm', 0, NULL, NULL),
(20459871234, 45987123, 'Tomás', 'González', 'tomas.gonzalez@gmail.com', 'Calle Mendoza 777', '1178901234', 2, '$2y$10$pHIQwpyXNKle5zLzNw6TIuM1gsdFFuHGEP6HPOdGaH3rzJ2x67TSW', 0, NULL, NULL),
(20460123454, 46012345, 'Joaquín', 'Suárez', 'joaquin.suarez@gmail.com', 'Calle Mitre 3020', '1120000004', 2, '$2y$10$pkjCyX/Y7wQDuHL5U.snK./xfUYwH2emADkU.96DYsGi6CS.rGkS6', 0, NULL, NULL),
(27376543222, 43765432, 'Martina', 'Vega', 'martina.vega@gmail.com', 'Av. Mitre 2200', '1101234567', 2, '$2y$10$q5djmmu8myfrX5ywCLAuTe8tApi6NEObSRCHC/4aQSvKPNJTQoy7m', 1, NULL, NULL),
(27385678922, 38567892, 'Emilia', 'Flores', 'emilia.flores@gmail.com', 'Av. Pueyrredón 740', '1120000007', 2, '$2y$10$N96X6Fvae9RjqGPiqEqO0.wyk44UNPnsfuhg5.9kzGdXni65VBRJO', 0, NULL, NULL),
(27391234562, 39123456, 'Lucía', 'Gómez', 'lucia.gomez@gmail.com', 'Av. Callao 1850', '1120000001', 2, '$2y$10$bnlE/GCKWMSXeB1QA4Utm.DxThKxeXgGQLWQ09QyWUFlwMKxfgb4G', 0, NULL, NULL),
(27398765422, 43987654, 'Camila', 'Pereyra', 'camila.pereyra@gmail.com', 'Av. Rivadavia 3400', '1167890123', 2, '$2y$10$PRW1jCQZxG9MibsMnYjm4OhA8EBt7EMlYiIPVjynb3gkx8gZJhW.W', 0, NULL, NULL),
(27427890122, 42789012, 'Martina', 'Delgado', 'martina.delgado@gmail.com', 'Av. San Martín 500', '1120000005', 2, '$2y$10$mj66SZkGm1KMfl8g7UkVYez3VNoWnotZtx6S7.gCzae7oU0SfwKGW', 0, NULL, NULL),
(27435567892, 43556789, 'Milagros', 'Torres', 'milagros.torres@gmail.com', 'Av. Córdoba 870', '1120000003', 2, '$2y$10$..lIwV6YF178j1G5VkBG7.BVWrKaiO5h/h/Eko4e7iicyt.Ggexc2', 0, NULL, NULL),
(27440123452, 44012345, 'Sofía', 'Martínez', 'sofia.martinez@gmail.com', 'Av. Corrientes 1234', '1123456789', 2, '$2y$10$T4Rx0K8FAyT.bQETXrbiXuPctkhbUPl2so3Pq9W6MxwYbg.fiMuPW', 0, NULL, NULL),
(27446789012, 44678901, 'Valeria', 'Benítez', 'valeria.benitez@gmail.com', 'Av. Santa Fe 2300', '1120000009', 2, '$2y$10$mfj5z93eCOKJ9rpr2ZGS6uz4UosPwIvP3E02Uhsu/Ni58o1mf9DO2', 0, NULL, NULL),
(27448765432, 44876543, 'Julieta', 'Ramírez', 'julieta.ramirez@gmail.com', 'Av. Santa Fe 900', '1189012345', 2, '$2y$10$2pGA4t9JI1d8H2BFIgiJEeYBBqFgJH1JNeIfr7SxzbG9DYqfQ.ubO', 0, NULL, NULL),
(27452334562, 45233456, 'Valentina', 'Rojas', 'valentina.rojas@gmail.com', 'Av. Belgrano 2222', '1145678901', 2, '$2y$10$6j7nEvyyw6clyRsl8z9LWOxGYFOKzRVNIgnqnPCDVQPGm3Bs9/mTe', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_abm`
--

CREATE TABLE `auditoria_abm` (
  `ID_Auditoria` int(11) NOT NULL,
  `ID_Admin` varchar(50) NOT NULL,
  `Fecha_Modif` date NOT NULL,
  `ID_Curso` int(11) DEFAULT NULL,
  `ID_Alumno` bigint(20) DEFAULT NULL,
  `Tipo_Operacion` varchar(50) NOT NULL,
  `Detalle` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificacion`
--

CREATE TABLE `certificacion` (
  `ID_CUV` varchar(50) NOT NULL,
  `ID_Inscripcion_Certif` int(11) NOT NULL,
  `Fecha_Emision` date NOT NULL,
  `Estado_Aprobacion` varchar(50) NOT NULL,
  `ID_Admin` varchar(50) NOT NULL,
  `Archivo` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `certificacion`
--

INSERT INTO `certificacion` (`ID_CUV`, `ID_Inscripcion_Certif`, `Fecha_Emision`, `Estado_Aprobacion`, `ID_Admin`, `Archivo`) VALUES
('G2025010001', 1, '2025-10-27', 'APROBADO', 'lione_29646', NULL),
('G2025010002', 2, '2025-10-27', 'ASISTIDO', 'lione_29646', NULL),
('G2025010003', 3, '2025-10-27', 'APROBADO', 'lione_29646', NULL),
('G2025040001', 4, '2025-10-28', 'APROBADO', 'lione_29646', NULL),
('G2025060001', 6, '2025-10-29', 'APROBADO', 'lione_29646', NULL),
('G2025060002', 7, '2025-10-29', 'APROBADO', 'lione_29646', NULL),
('G2025070001', 8, '2025-11-06', 'ASISTIDO', 'lione_29646', NULL),
('G2025070002', 9, '2025-11-07', 'APROBADO', 'lione_29646', NULL),
('G2025070003', 10, '2025-11-07', 'APROBADO', 'lione_29646', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto`
--

CREATE TABLE `contacto` (
  `ID_Contacto` int(11) NOT NULL,
  `Rol` varchar(50) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Mensaje` text NOT NULL,
  `Fecha_Contacto` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso`
--

CREATE TABLE `curso` (
  `ID_Curso` int(11) NOT NULL,
  `Nombre_Curso` varchar(100) NOT NULL,
  `Modalidad` varchar(50) DEFAULT NULL,
  `Docente` varchar(100) DEFAULT NULL,
  `Carga_Horaria` int(11) DEFAULT NULL,
  `Descripcion` varchar(500) DEFAULT NULL,
  `Requisitos` varchar(255) DEFAULT NULL,
  `Categoria` varchar(255) NOT NULL,
  `Tipo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `curso`
--

INSERT INTO `curso` (`ID_Curso`, `Nombre_Curso`, `Modalidad`, `Docente`, `Carga_Horaria`, `Descripcion`, `Requisitos`, `Categoria`, `Tipo`) VALUES
(1, 'Instalación de Paneles Solares', 'Virtual sincrónica', 'Leonardo Sotelo', 16, 'En este sentido se desarrolla esta cursada basada en las últimas tecnologías de paneles solares', 'Sin requisitos previos', 'CODICTADO', 'Genuino'),
(2, 'Instalación de Camaras de Seguridad y Alarmas', 'Virtual sincrónica', 'Leonardo Sotelo', 16, 'Trabajaremos con equipos de última generación empleando los métodos más actuales para reforzar la seguridad.', 'Sin requisitos previos', 'CODICTADO', 'Genuino'),
(3, 'Electricidad Industrial', 'Virtual sincrónica', 'Juan Carlos Bueno', 50, 'La electricidad industrial forma parte de una especialización dentro del rubro de la electricidad.', 'Conocimientos básicos de electricidad.', 'CODICTADO', 'Genuino'),
(4, 'Logística y Seguridad en el Transporte de Materiales Peligrosos', 'Virtual asincrónica', '', 18, 'Curso de Logística y Seguridad en el Transporte de Materiales Peligrosos brindado en conjunto entre la UTN FRH y CATAMP.', 'Sin requisitos previos', 'CODICTADO', 'Genuino'),
(5, 'Introducción a la Aviación Ejecutiva', 'Virtual asincrónica', 'Flavio Alberto Mansilla ', 8, 'El hecho de que los vuelos generales tengan destinos inciertos y requieran de un análisis previo de la operación, los recursos, horarios, etc. hace que este tipo de vuelos tengan un carácter atractivo.', 'Conocimiento aeronáutico general.', 'CURSO/TALLER', 'Genuino'),
(6, 'Manejo de Mercancías Peligrosas en la Industria Aeronáutica', 'Virtual sincrónica', 'Instr. Guillermo Vasto', 6, 'Hacer un curso de transporte de mercancías peligrosas es crucial para cumplir con las normativas legales, garantizar la seguridad y reducir riesgos de accidentes y daños ambientales.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(7, 'Inocuidad Alimentaria', 'Virtual sincrónica', 'Ing. Jorge Mario Marconi', 12, 'En el área de influencia de la regional HAEDO de UTN se han detectado numerosas empresas de fabricación de alimentos.', 'Secundario completo Poseer base de conocimientos orientada hacia la comprensión de los procesos, formulación de procedimientos, interpretación y cumplimiento de metodologías.', 'CURSO/TALLER', 'Genuino'),
(8, 'Manejo Seguro de Alimentos', 'Virtual sincrónica', 'Gabriela Ruiz', 9, 'Fundamental para toda empresa de transporte y logística que opere en la cadena de suministro alimentaria, ya que garantiza que el personal involucrado en el traslado, almacenamiento y manipulación de productos alimenticios.', 'Presentar documentación personal requerida para el carnet.', 'CURSO/TALLER', 'Genuino'),
(9, 'Rotulado de Alimentos: Aprender y Aplicar', 'Virtual sincrónica', 'Ing. María Agostina Mellano', 4, 'El correcto rotulado de alimentos es una herramienta clave para garantizar el derecho del consumidor a la información clara, veraz y completa sobre los productos que consume.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(10, 'Automatizacion y Control', 'Híbrida', 'Pablo Barone', 24, 'Capacitar al personal en la automatización de procesos para lograr una adecuada eficiencia energética.', 'Conocimientos de electricidad, electromecánica o electrónica.', 'CURSO/TALLER', 'Genuino'),
(11, 'Robótica y Control del Movimiento', 'Presencial', 'Pablo Barone', 24, 'Alta demanda de robotización de procesos.', 'Tener conocimientos de electricidad, electromecánica y electrónica, preferentemente relacionados con la industria.', 'CURSO/TALLER', 'Genuino'),
(12, 'Uso de Scanner Automotriz', 'Presencial', '', 14, 'Este curso responde a la creciente demanda laboral para formar profesionales con dominio de tecnologías de diagnóstico.', 'Tener conocimientos sobre motores vehiculares e inyección electrónica.', 'CURSO/TALLER', 'Genuino'),
(13, 'Electricidad del Automotor', 'Presencial', '', 42, 'Pensado para formar profesionales adaptados a las nuevas tecnologías de la industria automotriz.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(14, 'Colocación, Diagnóstico y Reparación de Equipos de GNC Vehicular', 'Presencial', '', 70, 'Los participantes adquirirán conocimientos teóricos y prácticos dentro este sistema de combustible automotriz que día a día se implementa mas dentro de la industria.', 'Tener conocimientos sobre motores vehiculares e inyección electrónica.', 'CURSO/TALLER', 'Genuino'),
(15, 'Inyección Electrónica Automotriz', 'Presencial', '', 70, 'Los estudiantes adquieren conocimiento sobre el funcionamiento , diagnostico y mantenimiento de los sistemas de inyección , desarrollando habilidades esenciales para identificar y solucionar fallos en vehículos.', 'Tener conocimientos sobre motores vehiculares.', 'CURSO/TALLER', 'Genuino'),
(16, 'Mecánica de 1er Año', 'Presencial', '', 144, 'Este curso de 1er año busca brindar a los estudiantes los conocimientos esenciales de la mecánica automotriz, combinando teoría con práctica.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(17, 'Mecánica de 2do Año', 'Presencial', '', 70, 'En el curso de Mecánica de 2do año se fundamenta la necesidad de profundizar los conocimientos adquiridos en el primer año , consolidando competencias técnicas y ampliando la capacidad de diagnostico y reparación.', 'Tener conocimientos básicos de mecánica.', 'CURSO/TALLER', 'Genuino'),
(18, 'Refrigeración Automotriz', 'Presencial', '', 60, 'Capacitar a los participantes para lograr el objetivo de tener una amplia y rápida salida laboral cubriendo las altas demandas del rubro en épocas de verano.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(19, 'Liderazgo, Motivación Personal y Conducción de Equipos', 'Virtual sincrónica', 'Lic. Gallo Viviana', 24, 'En contextos organizacionales marcados por la innovación, la tecnología y la colaboración interdisciplinaria, el liderazgo es una competencia clave para los profesionales de ingeniería. Este curso esté diseñado para desarrollar un estilo de liderazgo consciente, adaptable y orientado al trabajo en equipo y la gestión del talento.', 'Estudiantes o graduados de las carreras de ingeniería.', 'CURSO/TALLER', 'Genuino'),
(20, 'Inteligencia Emocional', 'Presencial', 'Lic. Maximiliano Gaston Cancelo', 24, 'La inteligencia emocional es una habilidad fundamental para el desarrollo personal, social y profesional en el mundo contemporáneo.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(22, 'Taller de Presentaciones Efectivas', 'Presencial', 'Nicolás Gabriele', 8, 'Tiene como objetivo fortalecer las habilidades de comunicación de los participantes, permitiéndoles expresar sus ideas con claridad, persuasión y confianza en contextos académicos y profesionales.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(23, 'Programación CNC de Tornos', 'Virtual asincrónica', 'Ing. Diego M. Russo', 24, 'En el ámbito de la manufactura industrial, la programación CNC (Control Numérico por Computadora) para tornos es una competencia clave que permite la fabricación precisa, eficiente y repetible de piezas mecanizadas.', 'Conocimientos básicos de planos, (no excluyente).', 'CURSO/TALLER', 'Genuino'),
(24, 'Programación CNC de Fresas', 'Virtual asincrónica', 'Ing. Diego M. Russo', 26, 'Las fresadoras CNC permiten realizar operaciones como el fresado, taladrado, ranurado y contorneado, entre otras, en materiales variados como metales, plásticos y compuestos.', 'Conocimientos básicos de planos, (no excluyente).', 'CURSO/TALLER', 'Genuino'),
(25, 'Lectura e Interpretación de Planos Mecánicos', 'Virtual asincrónica', 'Ing. Diego M. Russo', 24, 'La lectura e interpretación de planos mecánicos es una competencia esencial en los campos de la ingeniería, la mecánica industrial y el diseño técnico, ya que permite comprender, analizar y ejecutar proyectos de fabricación, mantenimiento y montaje de componentes y sistemas mecánicos.', 'Conocimientos básicos de planos, (no excluyente).', 'CURSO/TALLER', 'Genuino'),
(26, 'Metalografía Nivel 1', 'Híbrida', 'Ing. Nelson Alvarez Villar y Dr. Ing. Maximiliano Zanin', 60, 'El curso está orientado al cumplimiento de los requisitos académicos necesarios para presentarse a un examen de Certificación para el Nivel 1 del Esquema de Certificación para \"Metalógrafo/a de Laboratorio de materiales ferrosos\" en el Organismo de Certificación de Personas de la Comisión Nacional de Energía Atómica.', 'Ser mayor de edad y tener título secundario.', 'CURSO/TALLER', 'Genuino'),
(27, 'Metrología Dimensional Mecánica', 'Virtual asincrónica', 'Luis Antonio Villario', 68, 'En el presente curso, los participantes serán capacitados en lo referente a la ciencia Metrológica; en General de Mediciones Mecánicas. En cuanto a Metrología Dimensional Mecánica, el participante adquirirá los conocimientos que le permitirán llevar a cabo el estudio de mediciones de precisión para control de procesos en la industria.', 'Secundario completo.', 'CURSO/TALLER', 'Genuino'),
(28, 'Electrotecnia e Instalaciones Eléctricas Industriales', 'Virtual sincrónica', 'Ing. Jorge Alberto Alessi', 96, 'Un curso de instalaciones eléctricas es esencial para cualquier persona que desee trabajar en la industria eléctrica o para aquellos que deseen realizar reparaciones o instalaciones eléctricas en sus propias casas o negocios.', 'Secundario completo: Título Técnico Electricista, Electromecánico o Electrónico.', 'CURSO/TALLER', 'Genuino'),
(29, 'Máquinas Eléctricas de Potencia', 'Virtual', 'Ing. Jorge Alberto Alessi', 96, 'Un curso de máquinas eléctricas de potencia es esencial para cualquier persona que desee trabajar en la industria eléctrica o para aquellos que deseen especializarse en la generación, transmisión o distribución de energía eléctrica.', 'Secundario completo: Título Técnico Electricista, Electromecánico o Electrónico.', 'CURSO/TALLER', 'Genuino'),
(30, 'Seguridad, Higiene y Salud Ocupacional', 'Virtual sincrónica', 'Lic. Alejo AGUIRRE', 12, 'La Seguridad, Higiene y Salud Ocupacional es un área crítica en cualquier entorno laboral, ya que se enfoca en la prevención de accidentes, enfermedades profesionales y condiciones laborales que puedan afectar el bienestar de los trabajadores.', 'Secundario completo.', 'CURSO/TALLER', 'Genuino'),
(31, 'Implementación de Sistema de Calidad ISO 9001', 'Virtual', 'Arq. Andres NITTI', 12, 'Pilar fundamental para las organizaciones que buscan garantizar la calidad en sus procesos, productos y servicios. Esta norma internacional, reconocida globalmente, proporciona un marco estructurado para establecer, mantener y mejorar continuamente un sistema de gestión enfocado en satisfacer las necesidades y expectativas de los clientes.', 'Secundario completo.', 'CURSO/TALLER', 'Genuino'),
(32, 'Empresa ferroviaria, introducción del ferrocarril en el transporte (módulo 1)', 'Virtual', 'Ing. Ernesto Falzone', 39, 'Esta propuesta cubre todas las necesidades requeridas para poder recuperar el conocimiento en este medio de transporte tan importante para el crecimiento socioeconómico de un país.', 'Sin requisitos previos.', 'CURSO/TALLER', 'Genuino'),
(33, 'Electrotécnia (módulo 2)', 'Virtual', 'Ing. Dario David De Lima', 42, 'Esta propuesta cubre todas las necesidades requeridas para poder recuperar el conocimiento en este medio de transporte tan importante para el crecimiento socioeconómico de un país.', 'Sin requisitos previos.', 'CURSO/TALLER', 'Genuino'),
(34, 'Instalaciones, infraestructura de la vía. Electrificación (módulo 3)', 'Virtual', 'Ing. Mariano Gentile', 48, 'Esta propuesta cubre todas las necesidades requeridas para poder recuperar el conocimiento en este medio de transporte tan importante para el crecimiento socioeconómico de un país.', 'Sin requisitos previos.', 'CURSO/TALLER', 'Genuino'),
(35, 'Seguridad Ferroviaria y Señalización (módulo 4)', 'Virtual', 'Ing. Di Siervi Diego Hernán', 48, 'Esta propuesta cubre todas las necesidades requeridas para poder recuperar el conocimiento en este medio de transporte tan importante para el crecimiento socioeconómico de un país.', 'Sin requisitos previos.', 'CURSO/TALLER', 'Genuino'),
(36, 'Material Rodante (módulo 5)', 'Virtual', 'Ing. Di Siervi Diego Hernán', 48, 'Esta propuesta cubre todas las necesidades requeridas para poder recuperar el conocimiento en este medio de transporte tan importante para el crecimiento socioeconómico de un país.', 'Sin requisitos previos.', 'CURSO/TALLER', 'Genuino'),
(37, 'Aire Acondicionado e Instalación de Unidades Split', 'Presencial', 'Ing. Raúl Oscar Müller', 48, 'La instalación de unidades split de aire acondicionado es una competencia técnica fundamental en el ámbito de la climatización, ya que estos equipos son ampliamente utilizados tanto en entornos residenciales como comerciales por su eficiencia energética, bajo nivel de ruido y facilidad de control.', 'Se recomienda un nivel de educación secundaria completa.', 'CURSO/TALLER', 'Genuino'),
(38, 'Reparación de Celulares y Tablet (Nivel 1)', 'Presencial', 'Mauricio Ferreyra', 32, 'Un técnico reparador de celulares y tabletas es un profesional dedicado a extender la vida útil de los dispositivos móviles mediante la reparación de averías ya sea en el hardware o en el software.', 'Mayor de 16 años.', 'CURSO/TALLER', 'Genuino'),
(39, 'Refrigeración Industrial', 'Presencial', 'Ing. Raúl Oscar Müller', 48, 'El gran desarrollo, crecimiento y evolución tecnológica de los sistemas frigoríficos de uso familiar, comercial e industrial, conlleva a una demanda en crecimiento sostenible de personal técnico adecuadamente capacitado para la instalación y mantenimiento de sistemas frigoríficos', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(40, 'Reparación de Celulares Nivel 2', 'Presencial', 'Mauricio Ferreyra', 24, 'Microsoldado y electrónica aplicada a la reparación de placas de celulares.', 'Tener los conocimientos básicos que se dictan en el curso de reparación de celulares nivel inicial. Acceso a dispositivo con conectividad.', 'CURSO/TALLER', 'Genuino'),
(41, 'Reparación de PC', 'Presencial', 'Mauricio Ferreyra', 32, 'Es preciso formar usuarios de computadoras con conocimientos, técnicas, herramientas y habilidades para desempeñarse en el mercado laboral actual, dando respuesta inmediata a la alta demanda de profesionales en áreas técnicas.', 'Ser mayor de 16 años.', 'CURSO/TALLER', 'Genuino'),
(42, 'Fibra óptica Nivel 1', 'Presencial', 'Ing. Libralato Martin Diego', 21, 'Este curso surge dada la necesidad de capacitar técnicos que trabajan actualmente para las empresas de internet que empalman FO creando y manteniendo la red actual de FTTH en el país.', 'No requiere conocimientos previos.', 'CURSO/TALLER', 'Genuino'),
(43, 'Inglés 1', 'Virtual sincrónica', 'Julieta Pagani', 16, 'Elemental A1 está dirigido a cualquier persona que desee obtener un Nivel A1 de acuerdo con el Marco Común Europeo de Referencia para las Lenguas (MCER) y prepararse para aprobar exámenes oficiales. ', 'Sin requisitos previos.', 'CURSO/TALLER', 'Genuino'),
(44, 'Inglés 2', 'Virtual sincrónica', 'Julieta Pagani', 16, 'El curso 2 de Inglés general Elemental A1 está dirigido a cualquier persona que desee obtener un Nivel A1 de acuerdo con el Marco Común Europeo de Referencia para las Lenguas (MCER) y prepararse para aprobar exámenes oficiales. ', 'Nociones básicas del nivel 1.', 'CURSO/TALLER', 'Genuino'),
(45, 'Inglés 3', 'Virtual sincrónica', 'Julieta Pagani', 16, 'El curso 3 de Inglés general Elemental A1 está dirigido a cualquier persona que desee obtener un Nivel A1 de acuerdo con el Marco Común Europeo de Referencia para las Lenguas (MCER) y prepararse para aprobar exámenes oficiales.', 'Nociones básicas de los niveles 1 y 2.', 'CURSO/TALLER', 'Genuino'),
(46, 'Francés Nivel 1', 'Virtual sincrónica', 'Lic. Carole FABRE', 30, 'El idioma francés se sustenta en una amplia gama de aspectos que abarcan lo cultural, académico, profesional y personal.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(47, 'Excel Inicial', 'Virtual sincrónica', 'Lic. Alcides González y Martín Barbieri', 16, 'Microsoft Excel es una herramienta fundamental para la gestión de datos, análisis y presentación de información en diversos campos como finanzas, contabilidad, marketing, administración, entre otros. Dominar Excel te permite realizar tareas de forma eficiente, automatizar procesos y tomar decisiones informadas.', 'Sin requisitos.', 'CURSO/TALLER', 'Genuino'),
(48, 'Excel Intermedio', 'Virtual sincrónica', 'Lic. Alcides González y Martín Barbieri', 16, 'Microsoft Excel es una herramienta fundamental para la gestión de datos, análisis y presentación de información en diversos campos como finanzas, contabilidad, marketing, administración, entre otros. Dominar Excel te permite realizar tareas de forma eficiente, automatizar procesos y tomar decisiones informadas. Este curso te proporcionará las bases sólidas para convertirte en un usuario experto de Excel.', 'Nociones básicas de Excel.', 'CURSO/TALLER', 'Genuino'),
(49, 'Excel Avanzado', 'Virtual sincrónica', 'Lic. Alcides González y Martín Barbieri', 16, 'Microsoft Excel es una herramienta fundamental para la gestión de datos, análisis y presentación de información en diversos campos como finanzas, contabilidad, marketing, administración, entre otros. Dominar Excel te permite realizar tareas de forma eficiente, automatizar procesos y tomar decisiones informadas. Este curso te proporcionará las bases sólidas para convertirte en un usuario experto de Excel.', 'Conocimientos intermedios de Excel.', 'CURSO/TALLER', 'Genuino'),
(50, 'Programación aplicada a la Ingeniería', 'Presencial', 'Ing. Juan Carlos Polidoro', 48, 'Los alumnos de la universidad obtendrán la capacidad de programar en un idioma que forma la habilidad para hacerlo en cualquier lenguaje de programación.', 'Ser estudiante o egresado de alguna ingeniería con Análisis, AGA y computación aprobadas.', 'CURSO/TALLER', 'Genuino'),
(51, 'Power Bi Inicial', 'Virtual sincrónica', 'Lic. Alcides González y Martín Barbieri', 16, 'Power BI de Microsoft es una herramienta poderosa que permite a usuarios de todos los niveles de experiencia conectar, transformar, visualizar y compartir datos de manera intuitiva y eficiente.', 'Tener nociones avanzadas de Excel.', 'CURSO/TALLER', 'Genuino'),
(52, 'Power BI Avanzado', 'Virtual sincrónica', 'Lic. Alcides González y Martín Barbieri', 16, 'Power BI de Microsoft es una herramienta poderosa que permite a usuarios de todos los niveles de experiencia conectar, transformar, visualizar y compartir datos de manera intuitiva y eficiente.', 'Tener nociones básicas de Power BI.', 'CURSO/TALLER', 'Genuino'),
(53, 'Inteligencia Artificial: Generación de Prompts', 'Virtual sincrónica', 'Lic. Alcides González y Martín Barbieri', 16, 'Dentro de esta área, la generación de prompts se ha convertido en un elemento esencial para optimizar el rendimiento de los modelos de IA, como los modelos generativos de texto e imagen.', 'Sin requisitos previos.', 'CURSO/TALLER', 'Genuino'),
(54, 'Desarrollo de Mandos Medios', 'Virtual sincrónica', 'Lic. Gallo Viviana', 24, 'Las personas eligen tomar un curso de desarrollo de mandos medios por varias razones, que generalmente se relacionan con el deseo de mejorar sus habilidades de liderazgo, incrementar su valor en la organización y avanzar en sus carreras.', 'Estudiantes o graduados de las carreras de ingeniería.', 'CURSO/TALLER', 'Genuino'),
(55, 'Desarrollo Organizacional', 'Virtual sincrónica', 'Lic. Gallo Viviana', 24, 'Este curso se adapta bien a cualquier profesional con responsabilidad en la conducción de equipos y con interés en fortalecer su rol como agente de cambio dentro de una organización.', 'Estudiantes o graduados de las carreras de ingeniería.', 'CURSO/TALLER', 'Genuino'),
(56, 'Project Management', 'Virtual sincrónica', 'Ing. Nicolás Gabriele', 12, 'El curso de Project Management capacita a los participantes en la gestión eficiente de proyectos, combinando teoría y práctica. Desarrollarán habilidades clave como liderazgo, trabajo en equipo y resolución de problemas, mejorando su empleabilidad. ', 'No posee requisitos previos.', 'CURSO/TALLER', 'Genuino'),
(57, 'El avance de las prótesis para amputados y sus beneficios funcionales', 'Virtual sincrónica', 'Lic. Damiana Pacho', 30, 'En el campo de la rehabilitación física, existen profesionales dedicados al tratamiento específico de pacientes amputados, que tienen como meta mejorar la condición física y la salud emocional del individuo con el fin de reinsertarlo en la sociedad.', 'Ser profesional de la salud.', 'CURSO/TALLER', 'Genuino'),
(58, 'Diseño e Impresión Digital de Órthosis Neuromuscular', 'Virtual sincrónica', 'Christian Capmourteres y Diego Nally', 16, 'El curso de Diseño e Impresión Digital de Órthosis Neuromuscular tiene como finalidad capacitar a profesionales en el uso de tecnologías digitales aplicadas a la rehabilitación, integrando escaneo 3D, diseño asistido por computadora (CAD) e impresión 3D para la creación personalizada de órthosis.', 'Contar con una computadora.', 'CURSO/TALLER', 'Genuino'),
(59, 'Diplomatura en Gestión de la Seguridad Operacional e Investigación de Accidentes Ferroviarios', 'Virtual sincrónica', 'Director: Ing. Pablo Cosentino', 108, 'En esta Diplomatura se abordarán las principales técnicas de investigación junto con los aspectos técnicos de la infraestructura, el material rodante y la operación que aportará la carrera de Ingeniería Ferroviaria de la UTN Haedo.', 'Sin requisitos.', 'DIPLOMATURA', 'Genuino'),
(60, 'Diplomatura en Seguridad Operacional en el Transporte', 'Virtual sincrónica', '', 100, 'Esta diplomatura tiene como propósito formar profesionales competentes en seguridad operacional e investigación, aportando enfoques comunes a todos los modos de transporte desde el punto de vista metodológico.', 'Sin requisitos.', 'DIPLOMATURA', 'Genuino'),
(61, 'Diplomatura en Formación Profesional Ferroviaria', 'Virtual sincrónica', 'Director: Ing. Di Siervi Jose Antonio', 225, 'En la zona no existe oferta educativa similar a nivel universitario respecto a tecnologías ferroviarias, material ferroviario y afines cubriendo de esta manera todas las necesidades requeridas para poder recuperar este medio de transporte tan importante para el crecimiento socioeconómico de un país. ', 'Sin requisitos.', 'DIPLOMATURA', 'Genuino'),
(62, 'CCNA I', 'Virtual sincrónica', 'Pablo Gambacorta', 64, 'El objetivo es adquirir las competencias necesarias para mejorar el acceso a los equipos y aprender a configurar básicamente los aspectos físicos (hardware) y lógicos (software) de una Red. Para ser parte de la comunidad que diseña, construye y sueña la tecnología de Redes que conecta a todos en todo lugar, que cambia la forma en la que trabajamos, vivimos, jugamos y aprendemos; hay varios caminos.', 'Conocimientos básicos en informática.', 'CISCO', 'Genuino'),
(63, 'CCNA II', 'Virtual sincrónica', 'Pablo Gambacorta', 64, 'En este curso podrá adquirir las competencias necesarias para mejorar el acceso a los equipos y aprender a configurar básicamente los aspectos físicos (hardware) y lógicos (software) de una Red.', 'Haber finalizado CCNA I.', 'CISCO', 'Genuino'),
(64, 'IT Essentials', 'Virtual sincrónica', 'Germán Alberto Lamartine', 70, 'En el curso IT Essentials (ITE), se presentan aspectos del hardware y el software de las computadoras. Los temas que se tratan son los dispositivos móviles, Linux, macOS, la virtualización, la computación en la nube; además, se brinda información sobre operación y herramientas de los sistemas operativos Microsoft Windows, la seguridad, las redes, la resolución de problemas y las responsabilidades del profesional de IT.', 'Se requiere conocimiento previo acerca del manejo de PC.', 'CISCO', 'Genuino'),
(65, 'Programming Essentials en Python', 'Virtual sincrónica', 'Germán Alberto Lamartine', 70, 'Es omnipresente, las personas usan numerosos dispositivos con tecnología Python a diario, se den cuenta o no. Ha habido millones (bueno, en realidad miles de millones) de líneas de código escritas en Python, lo que significa oportunidades casi ilimitadas para la reutilización de código y aprender de ejemplos bien elaborados.', 'Se recomienda conocimiento en programación relacionada a objetos.', 'CISCO', 'Genuino'),
(66, 'CyberOps', 'Virtual sincrónica', '', 70, 'Las organizaciones de hoy en día tienen el desafío de detectar rápidamente las intrusiones a la ciberseguridad y de responder eficazmente a los incidentes de seguridad. Los equipos de personal en los centros de operaciones de seguridad (SOC) están atentos a los sistemas de seguridad y protegen a las organizaciones detectando y respondiendo a los ataques y las amenazas de ciberseguridad.', 'Destrezas de navegación en Internet y PC / Conceptos básicos de los sistemas Windows y Linux / Comprensión básica de las redes informáticas (nivel CCNA ITN) / Comprensión binaria y hexadecimal Familiaridad con Cisco Packet Tracer.', 'CISCO', 'Genuino'),
(67, 'Programming Essentials en Python', 'Virtual sincrónica', 'Germán Alberto Lamartine', 70, 'Es omnipresente, las personas usan numerosos dispositivos con tecnología Python a diario, se den cuenta o no. Ha habido millones (bueno, en realidad miles de millones) de líneas de código escritas en Python, lo que significa oportunidades casi ilimitadas para la reutilización de código y aprender de ejemplos bien elaborados. ', 'Se recomienda conocimiento en programación relacionada a objetos.', 'CISCO', 'Genuino'),
(68, 'Gestión de Infraestructura de Redes', 'Virtual sincrónica', 'Germán Alberto Lamartine', 40, 'La capacitación, desarrollada por Lightera, es reconocida por la «Building Industry Consulting Service International Inc.» (BICSI) y acredita parte de los conocimientos exigidos para el examen «Registered Communications Distribution Designer» (RCDD).', 'Concepto de Red y Cableado Estructurado abordados en las capacitaciones Data Cabling System y FCP Fibras ópticas.', 'LIGHTERA', 'Genuino'),
(69, 'Fibra óptica', 'Virtual sincrónica', '', 24, 'El curso de Fibras Ópticas certificado por Lightera es un entrenamiento especialmente desarrollado con el fin de formar Profesionales aptos para diseñar e instalar sistemas de comunicaciones basados en Fibras Ópticas.', 'Experiencia en cableado estructurado (No excluyente).', 'LIGHTERA', 'Genuino'),
(70, 'Cableado Estructurado de Datos', 'Virtual sincrónica', '', 24, 'Capacitación dirigida al área de cableado estructurado para aplicaciones en redes informáticas locales (LAN), consultorios en general, edificios inteligentes, condominios, etc.', 'Sin requisitos.', 'LIGHTERA', 'Genuino');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `duracion_curso`
--

CREATE TABLE `duracion_curso` (
  `ID_Curso` int(11) NOT NULL,
  `Horario` varchar(50) NOT NULL,
  `Inicio_Curso` date NOT NULL,
  `Fin_Curso` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `duracion_curso`
--

INSERT INTO `duracion_curso` (`ID_Curso`, `Horario`, `Inicio_Curso`, `Fin_Curso`) VALUES
(1, '09:00 - 11:00', '2025-03-04', '2025-06-11'),
(2, '14:00 - 16:00', '2025-03-04', '2025-07-15'),
(3, '18:00 - 20:00', '2025-03-11', '2025-07-31'),
(4, '08:00 - 10:00', '2025-03-12', '2025-08-05'),
(5, '10:00 - 12:00', '2025-03-17', '2025-06-25'),
(6, '15:00 - 17:00', '2025-03-18', '2025-07-30'),
(7, '17:00 - 19:00', '2025-03-20', '2025-08-10'),
(8, '19:00 - 21:00', '2025-03-24', '2025-09-15'),
(9, '08:00 - 10:00', '2025-03-25', '2025-06-20'),
(10, '09:30 - 11:30', '2025-03-27', '2025-07-01'),
(11, '14:00 - 16:00', '2025-04-01', '2025-08-05'),
(12, '18:00 - 20:00', '2025-04-02', '2025-08-25'),
(13, '10:00 - 12:00', '2025-04-07', '2025-07-10'),
(14, '16:00 - 18:00', '2025-04-08', '2025-09-02'),
(15, '09:00 - 11:00', '2025-04-09', '2025-08-12'),
(16, '14:00 - 16:00', '2025-04-10', '2025-09-25'),
(17, '17:00 - 19:00', '2025-04-14', '2025-09-30'),
(18, '19:00 - 21:00', '2025-04-15', '2025-10-10'),
(19, '08:00 - 10:00', '2025-04-16', '2025-08-05'),
(20, '09:00 - 11:00', '2025-04-17', '2025-07-30'),
(22, '14:00 - 16:00', '2025-04-22', '2025-09-10'),
(23, '16:00 - 18:00', '2025-04-23', '2025-09-20'),
(24, '18:00 - 20:00', '2025-04-24', '2025-10-01'),
(25, '08:00 - 10:00', '2025-04-25', '2025-08-15'),
(26, '09:00 - 11:00', '2025-04-28', '2025-09-02'),
(27, '15:00 - 17:00', '2025-04-29', '2025-09-30'),
(28, '17:00 - 19:00', '2025-04-30', '2025-09-25'),
(29, '19:00 - 21:00', '2025-05-02', '2025-10-10'),
(30, '08:30 - 10:30', '2025-05-05', '2025-09-15'),
(31, '09:30 - 11:30', '2025-05-06', '2025-09-20'),
(32, '14:00 - 16:00', '2025-05-07', '2025-10-05'),
(33, '16:00 - 18:00', '2025-05-08', '2025-10-15'),
(34, '18:00 - 20:00', '2025-05-09', '2025-10-25'),
(35, '10:00 - 12:00', '2025-05-12', '2025-09-10'),
(36, '14:00 - 16:00', '2025-05-13', '2025-09-18'),
(37, '17:00 - 19:00', '2025-05-14', '2025-09-25'),
(38, '08:00 - 10:00', '2025-05-15', '2025-09-30'),
(39, '09:00 - 11:00', '2025-05-16', '2025-10-01'),
(40, '19:00 - 21:00', '2025-05-19', '2025-10-10'),
(41, '08:30 - 10:30', '2025-05-20', '2025-09-05'),
(42, '14:00 - 16:00', '2025-05-21', '2025-09-25'),
(43, '16:00 - 18:00', '2025-05-22', '2025-09-30'),
(44, '18:00 - 20:00', '2025-05-23', '2025-10-05'),
(45, '09:00 - 11:00', '2025-05-26', '2025-09-30'),
(46, '10:00 - 12:00', '2025-05-27', '2025-10-10'),
(47, '15:00 - 17:00', '2025-05-28', '2025-10-15'),
(48, '17:00 - 19:00', '2025-05-29', '2025-10-20'),
(49, '19:00 - 21:00', '2025-05-30', '2025-10-25'),
(50, '08:00 - 10:00', '2025-06-02', '2025-10-30'),
(51, '09:00 - 11:00', '2025-06-03', '2025-10-20'),
(52, '14:00 - 16:00', '2025-06-04', '2025-10-10'),
(53, '16:00 - 18:00', '2025-06-05', '2025-10-25'),
(54, '18:00 - 20:00', '2025-06-06', '2025-10-30'),
(55, '08:30 - 10:30', '2025-06-09', '2025-10-05'),
(56, '09:30 - 11:30', '2025-06-10', '2025-10-10'),
(57, '14:00 - 16:00', '2025-06-11', '2025-10-15'),
(58, '17:00 - 19:00', '2025-06-12', '2025-10-20'),
(59, '19:00 - 21:00', '2025-06-13', '2025-10-25'),
(60, '08:00 - 10:00', '2025-06-16', '2025-10-30'),
(61, '10:00 - 12:00', '2025-06-17', '2025-10-15'),
(62, '14:00 - 16:00', '2025-06-18', '2025-10-20'),
(63, '16:00 - 18:00', '2025-06-19', '2025-10-25'),
(64, '18:00 - 20:00', '2025-06-20', '2025-10-30'),
(65, '08:00 - 10:00', '2025-06-23', '2025-10-15'),
(66, '09:00 - 11:00', '2025-06-24', '2025-10-20'),
(67, '15:00 - 17:00', '2025-06-25', '2025-10-25'),
(68, '17:00 - 19:00', '2025-06-26', '2025-10-30'),
(69, '19:00 - 21:00', '2025-06-27', '2025-11-01'),
(70, '08:30 - 10:30', '2025-06-30', '2025-10-31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta_satisfaccion`
--

CREATE TABLE `encuesta_satisfaccion` (
  `ID_Encuesta` int(11) NOT NULL,
  `ID_Inscripcion` int(11) NOT NULL,
  `Fecha_Encuesta` timestamp NOT NULL DEFAULT current_timestamp(),
  `desempeno_formador` varchar(50) NOT NULL,
  `claridad_temas` varchar(50) NOT NULL,
  `ejemplos_practicos` varchar(50) NOT NULL,
  `respuesta_dudas` varchar(50) NOT NULL,
  `cumplimiento_horarios` varchar(50) NOT NULL,
  `satisfaccion_curso` int(2) NOT NULL,
  `contribucion_laboral` int(2) NOT NULL,
  `recomienda_frh` varchar(5) NOT NULL,
  `tema_no_hablado` text DEFAULT NULL,
  `sugerencias` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuesta_satisfaccion`
--

INSERT INTO `encuesta_satisfaccion` (`ID_Encuesta`, `ID_Inscripcion`, `Fecha_Encuesta`, `desempeno_formador`, `claridad_temas`, `ejemplos_practicos`, `respuesta_dudas`, `cumplimiento_horarios`, `satisfaccion_curso`, `contribucion_laboral`, `recomienda_frh`, `tema_no_hablado`, `sugerencias`) VALUES
(1, 2, '2025-11-08 02:46:58', 'Muy bien', 'Muy bien', 'Muy bien', 'Muy bien', 'Bien', 9, 7, 'Sí', 'Se habló todo lol xd', 'Y la verdad que no!!!');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripcion`
--

CREATE TABLE `inscripcion` (
  `ID_Inscripcion` int(11) NOT NULL,
  `ID_Cuil_Alumno` bigint(20) NOT NULL,
  `ID_Curso` int(11) NOT NULL,
  `Cuatrimestre` varchar(20) NOT NULL,
  `Anio` int(11) NOT NULL,
  `Estado_Cursada` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inscripcion`
--

INSERT INTO `inscripcion` (`ID_Inscripcion`, `ID_Cuil_Alumno`, `ID_Curso`, `Cuatrimestre`, `Anio`, `Estado_Cursada`) VALUES
(1, 27440123452, 1, 'Primer Cuatrimestre', 2024, 'CERTIFICADA'),
(2, 20431223444, 1, 'Primer Cuatrimestre', 2024, 'CERTIFICADA'),
(3, 27452334562, 1, 'Primer Cuatrimestre', 2024, 'CERTIFICADA'),
(4, 20447654324, 4, 'Primer Cuatrimestre', 2024, 'CERTIFICADA'),
(5, 27398765422, 4, 'Primer Cuatrimestre', 2024, 'Finalizado'),
(6, 20459871234, 6, 'Segundo Cuatrimestre', 2024, 'CERTIFICADA'),
(7, 27448765432, 6, 'Segundo Cuatrimestre', 2024, 'CERTIFICADA'),
(8, 20441223454, 7, 'Segundo Cuatrimestre', 2024, 'CERTIFICADA'),
(9, 27376543222, 7, 'Segundo Cuatrimestre', 2024, 'CERTIFICADA'),
(10, 20450987654, 7, 'Segundo Cuatrimestre', 2024, 'CERTIFICADA'),
(11, 27391234562, 23, 'Primer Cuatrimestre', 2025, 'Finalizado'),
(12, 20457890124, 23, 'Primer Cuatrimestre', 2025, 'Finalizado'),
(13, 27435567892, 23, 'Primer Cuatrimestre', 2025, 'Finalizado'),
(14, 20460123454, 38, 'Primer Cuatrimestre', 2025, 'Finalizado'),
(15, 27427890122, 38, 'Primer Cuatrimestre', 2025, 'Finalizado'),
(16, 20438901234, 69, 'Segundo Cuatrimestre', 2025, 'En curso'),
(17, 27385678922, 69, 'Segundo Cuatrimestre', 2025, 'En curso'),
(18, 20451234564, 69, 'Segundo Cuatrimestre', 2025, 'En curso'),
(19, 27446789012, 68, 'Segundo Cuatrimestre', 2025, 'En curso'),
(20, 20433456784, 68, 'Segundo Cuatrimestre', 2025, 'En curso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `ID_Rol` int(11) NOT NULL,
  `Nombre_Rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`ID_Rol`, `Nombre_Rol`) VALUES
(1, 'ADMIN'),
(2, 'ALUMNO'),
(3, 'SECRETARIO');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acceso_externo`
--
ALTER TABLE `acceso_externo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ID_Admin`),
  ADD UNIQUE KEY `Legajo` (`Legajo`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`),
  ADD KEY `FK_Admin_Rol` (`ID_Rol`);

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`ID_Cuil_Alumno`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`),
  ADD KEY `FK_Alumno_Rol` (`ID_Rol`);

--
-- Indices de la tabla `auditoria_abm`
--
ALTER TABLE `auditoria_abm`
  ADD PRIMARY KEY (`ID_Auditoria`),
  ADD KEY `FK_Auditoria_Admin` (`ID_Admin`),
  ADD KEY `FK_Auditoria_Curso` (`ID_Curso`),
  ADD KEY `FK_Auditoria_Alumno` (`ID_Alumno`);

--
-- Indices de la tabla `certificacion`
--
ALTER TABLE `certificacion`
  ADD PRIMARY KEY (`ID_CUV`),
  ADD KEY `FK_Certif_Inscrip` (`ID_Inscripcion_Certif`),
  ADD KEY `FK_Certif_Admin` (`ID_Admin`);

--
-- Indices de la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD PRIMARY KEY (`ID_Contacto`);

--
-- Indices de la tabla `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`ID_Curso`);

--
-- Indices de la tabla `duracion_curso`
--
ALTER TABLE `duracion_curso`
  ADD KEY `FK_Duracion_Curso` (`ID_Curso`);

--
-- Indices de la tabla `encuesta_satisfaccion`
--
ALTER TABLE `encuesta_satisfaccion`
  ADD PRIMARY KEY (`ID_Encuesta`),
  ADD UNIQUE KEY `idx_unique_inscripcion` (`ID_Inscripcion`);

--
-- Indices de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD PRIMARY KEY (`ID_Inscripcion`),
  ADD KEY `FK_Inscrip_Alumno` (`ID_Cuil_Alumno`),
  ADD KEY `FK_Inscrip_Curso` (`ID_Curso`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`ID_Rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acceso_externo`
--
ALTER TABLE `acceso_externo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `auditoria_abm`
--
ALTER TABLE `auditoria_abm`
  MODIFY `ID_Auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contacto`
--
ALTER TABLE `contacto`
  MODIFY `ID_Contacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `curso`
--
ALTER TABLE `curso`
  MODIFY `ID_Curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT de la tabla `encuesta_satisfaccion`
--
ALTER TABLE `encuesta_satisfaccion`
  MODIFY `ID_Encuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  MODIFY `ID_Inscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `ID_Rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `FK_Admin_Rol` FOREIGN KEY (`ID_Rol`) REFERENCES `rol` (`ID_Rol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD CONSTRAINT `FK_Alumno_Rol` FOREIGN KEY (`ID_Rol`) REFERENCES `rol` (`ID_Rol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `auditoria_abm`
--
ALTER TABLE `auditoria_abm`
  ADD CONSTRAINT `FK_Auditoria_Admin` FOREIGN KEY (`ID_Admin`) REFERENCES `admin` (`ID_Admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Auditoria_Alumno` FOREIGN KEY (`ID_Alumno`) REFERENCES `alumno` (`ID_Cuil_Alumno`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Auditoria_Curso` FOREIGN KEY (`ID_Curso`) REFERENCES `curso` (`ID_Curso`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `certificacion`
--
ALTER TABLE `certificacion`
  ADD CONSTRAINT `FK_Certif_Admin` FOREIGN KEY (`ID_Admin`) REFERENCES `admin` (`ID_Admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Certif_Inscrip` FOREIGN KEY (`ID_Inscripcion_Certif`) REFERENCES `inscripcion` (`ID_Inscripcion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `duracion_curso`
--
ALTER TABLE `duracion_curso`
  ADD CONSTRAINT `FK_Duracion_Curso` FOREIGN KEY (`ID_Curso`) REFERENCES `curso` (`ID_Curso`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `encuesta_satisfaccion`
--
ALTER TABLE `encuesta_satisfaccion`
  ADD CONSTRAINT `encuesta_satisfaccion_ibfk_1` FOREIGN KEY (`ID_Inscripcion`) REFERENCES `inscripcion` (`ID_Inscripcion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD CONSTRAINT `FK_Inscrip_Alumno` FOREIGN KEY (`ID_Cuil_Alumno`) REFERENCES `alumno` (`ID_Cuil_Alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Inscrip_Curso` FOREIGN KEY (`ID_Curso`) REFERENCES `curso` (`ID_Curso`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
