-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: dback
-- ------------------------------------------------------
-- Server version	8.0.40

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `datos_empresa`
--
CREATE DATABASE  IF NOT EXISTS `dback`;
USE `dback`;

DROP TABLE IF EXISTS `datos_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datos_empresa` (
  `Razon_Social` varchar(100) NOT NULL,
  `Nombre_Comercial` varchar(100) DEFAULT NULL,
  `Estado` varchar(100) DEFAULT NULL,
  `Ciudad` varchar(100) DEFAULT NULL,
  `Direccion` varchar(200) DEFAULT NULL,
  `Codigo_Postal` int DEFAULT NULL,
  `Telefono` bigint DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Fecha_Creacion` date DEFAULT NULL,
  `Descripcion` text,
  PRIMARY KEY (`Razon_Social`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_empresa`
--

LOCK TABLES `datos_empresa` WRITE;
/*!40000 ALTER TABLE `datos_empresa` DISABLE KEYS */;
INSERT INTO `datos_empresa` VALUES ('Gruas DBACK','SERVICIO DE GRUAS Y MANIOBRAS D´BACK (GASTELUM/BACA)','Sinaloa','Los Mochis','Manuel Castro Elizalde 895 SUR',81233,6688132905,NULL,NULL,'El Servicio de Grúas llamado SERVICIO DE GRUAS Y MANIOBRAS D´BACK (GASTELUM/BACA) ubicada en Sinaloa ciudad Los Mochis ofrece varios servicios de calidad.');
/*!40000 ALTER TABLE `datos_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados`
--

DROP TABLE IF EXISTS `empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados` (
  `ID_Empleado` int NOT NULL AUTO_INCREMENT,
  `Apellido1` varchar(50) NOT NULL,
  `Apellido2` varchar(50) NOT NULL,
  `Nombres` varchar(100) NOT NULL,
  `RFC` varchar(13) NOT NULL,
  `Nomina` bigint NOT NULL,
  `Fecha_Ingreso` date NOT NULL,
  `Puesto` varchar(100) NOT NULL,
  `Sueldo` decimal(5,2) NOT NULL,
  `telefono` bigint NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `licencia` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ID_Empleado`),
  UNIQUE KEY `RFC_UNIQUE` (`RFC`),
  UNIQUE KEY `Nomina_UNIQUE` (`Nomina`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados`
--

LOCK TABLES `empleados` WRITE;
/*!40000 ALTER TABLE `empleados` DISABLE KEYS */;
INSERT INTO `empleados` VALUES (1,'Lopez','Payan','Kevin Ricardo','e3d2f424e2r',1542144815151,'2025-02-24','Ing. en sistemas',550.00,6688253351,NULL,NULL),(2,'Flores','Guevara','Angel Gabriel','qfeqwbgfqd',5772553554241,'2025-02-24','sistemas',500.00,0,NULL,NULL);
/*!40000 ALTER TABLE `empleados` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER `ActualizarFechaIngresoEmpleado` BEFORE INSERT ON `empleados` FOR EACH ROW BEGIN
        SET NEW.Fecha_Ingreso = CURRENT_DATE();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER `Bajarsueldo` BEFORE UPDATE ON `empleados` FOR EACH ROW BEGIN
    IF NEW.Sueldo < OLD.Sueldo THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'En México, reducir el sueldo de un empleado no es una práctica permitida según la Ley Federal del Trabajo.';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `gruas`
--

DROP TABLE IF EXISTS `gruas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gruas` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Placa` varchar(7) NOT NULL,
  `Marca` varchar(100) NOT NULL,
  `Modelo` varchar(100) NOT NULL,
  `Tipo` enum('Plataforma','Arrastre','Remolque') NOT NULL,
  `Estado` enum('Activa','Mantenimiento','Inactiva') NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`),
  UNIQUE KEY `Placa_UNIQUE` (`Placa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gruas`
--

LOCK TABLES `gruas` WRITE;
/*!40000 ALTER TABLE `gruas` DISABLE KEYS */;
INSERT INTO `gruas` VALUES (1,'aefgw','suru','tuneado','Arrastre','Activa');
/*!40000 ALTER TABLE `gruas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_servicio`
--

DROP TABLE IF EXISTS `historial_servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_servicio` (
  `ID_Servicio` int NOT NULL AUTO_INCREMENT,
  `ID_Grua` int NOT NULL,
  `ID_Empleado` int NOT NULL,
  `Estado` enum('En espera','En proceso','Completado','Cancelado') NOT NULL,
  `Placa_Veiculo` varchar(7) NOT NULL,
  `Marca` varchar(50) NOT NULL,
  `Modelo` varchar(50) DEFAULT NULL,
  `Color` varchar(50) DEFAULT NULL,
  `Nombre_Completo` varchar(200) NOT NULL,
  `Telefono` bigint NOT NULL,
  `Tipo_Vehiculo` enum('Automovil','Camioneta','Motocicleta','Camion') NOT NULL,
  `Tipo_Servicio` enum('Remolque','Cambio de batería','Suministro de gasolina','Cambio de llanta','Servicio de arranque') NOT NULL,
  `Descripcion_Problema` varchar(400) NOT NULL,
  `Direccion_Inicio` varchar(200) NOT NULL,
  `Direccion_Fin` varchar(200) NOT NULL,
  `Costo_Servicio` int NOT NULL,
  `Fecha_Hora_Inicio` datetime NOT NULL,
  `Fecha_Hora_Fin` datetime DEFAULT NULL,
  PRIMARY KEY (`ID_Servicio`),
  KEY `ID_Grua_idx` (`ID_Grua`),
  KEY `ID_Empleado_idx` (`ID_Empleado`),
  CONSTRAINT `ID_Empleado` FOREIGN KEY (`ID_Empleado`) REFERENCES `empleados` (`ID_Empleado`),
  CONSTRAINT `ID_Grua` FOREIGN KEY (`ID_Grua`) REFERENCES `gruas` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_servicio`
--

LOCK TABLES `historial_servicio` WRITE;
/*!40000 ALTER TABLE `historial_servicio` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_servicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mantenimiento_gruas`
--

DROP TABLE IF EXISTS `mantenimiento_gruas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mantenimiento_gruas` (
  `ID_Mantenimiento` int NOT NULL AUTO_INCREMENT,
  `ID_Grua` int NOT NULL,
  `Descripcion` varchar(200) NOT NULL,
  `Costo` bigint NOT NULL,
  `Fecha_Hora` datetime DEFAULT NULL,
  `Autorizo` varchar(200) NOT NULL,
  PRIMARY KEY (`ID_Mantenimiento`),
  KEY `ID_MG_idx` (`ID_Grua`),
  CONSTRAINT `ID_MG` FOREIGN KEY (`ID_Grua`) REFERENCES `gruas` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mantenimiento_gruas`
--

LOCK TABLES `mantenimiento_gruas` WRITE;
/*!40000 ALTER TABLE `mantenimiento_gruas` DISABLE KEYS */;
INSERT INTO `mantenimiento_gruas` VALUES (1,1,'cambio aceite',2000,'2025-05-05 17:50:38','gerente');
/*!40000 ALTER TABLE `mantenimiento_gruas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER `guardar_fecha_hora_mantenimiento` BEFORE INSERT ON `mantenimiento_gruas` FOR EACH ROW BEGIN
    SET NEW.Fecha_Hora = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pagos_paypal`
--

DROP TABLE IF EXISTS `pagos_paypal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_paypal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solicitud_id` int NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `email_paypal` varchar(100) NOT NULL,
  `nombre_paypal` varchar(100) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `currency_code` varchar(3) NOT NULL DEFAULT 'MXN',
  `fecha_pago` datetime DEFAULT CURRENT_TIMESTAMP,
  `detalles` text,
  PRIMARY KEY (`id`),
  KEY `solicitud_id` (`solicitud_id`),
  CONSTRAINT `pagos_paypal_ibfk_1` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_paypal`
--

LOCK TABLES `pagos_paypal` WRITE;
/*!40000 ALTER TABLE `pagos_paypal` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_paypal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registros_arco`
--

DROP TABLE IF EXISTS `registros_arco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registros_arco` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ID_Servicio` int DEFAULT NULL,
  `estado` enum('recibida','en_proceso','completada','rechazada') DEFAULT 'recibida',
  `comentarios` text,
  `evidencia` text,
  PRIMARY KEY (`id`),
  KEY `ID_AS_idx` (`ID_Servicio`),
  CONSTRAINT `ID_AS` FOREIGN KEY (`ID_Servicio`) REFERENCES `historial_servicio` (`ID_Servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registros_arco`
--

LOCK TABLES `registros_arco` WRITE;
/*!40000 ALTER TABLE `registros_arco` DISABLE KEYS */;
/*!40000 ALTER TABLE `registros_arco` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reparacion-servicio`
--

DROP TABLE IF EXISTS `reparacion-servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reparacion-servicio` (
  `ID_Gasto` int NOT NULL AUTO_INCREMENT,
  `ID_Grua` int NOT NULL,
  `Tipo` enum('Reparacion','Gasto_Oficina','Gasolina') NOT NULL,
  `Descripcion` varchar(400) NOT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL,
  `Costo` int NOT NULL,
  PRIMARY KEY (`ID_Gasto`),
  KEY `ID_GruaGasto_idx` (`ID_Grua`),
  CONSTRAINT `ID_GruaGasto` FOREIGN KEY (`ID_Grua`) REFERENCES `gruas` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reparacion-servicio`
--

LOCK TABLES `reparacion-servicio` WRITE;
/*!40000 ALTER TABLE `reparacion-servicio` DISABLE KEYS */;
/*!40000 ALTER TABLE `reparacion-servicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes`
--

DROP TABLE IF EXISTS `solicitudes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ubicacion` text NOT NULL,
  `coordenadas` varchar(50) DEFAULT NULL,
  `tipo_vehiculo` enum('automovil','camioneta','motocicleta','camion') NOT NULL,
  `marca_vehiculo` varchar(50) NOT NULL,
  `modelo_vehiculo` varchar(50) NOT NULL,
  `foto_vehiculo` varchar(255) DEFAULT NULL,
  `tipo_servicio` enum('remolque','bateria','gasolina','llanta','arranque','otro') NOT NULL,
  `descripcion_problema` text,
  `urgencia` enum('normal','urgente','emergencia') NOT NULL DEFAULT 'normal',
  `distancia_km` decimal(10,2) DEFAULT NULL,
  `costo_estimado` decimal(10,2) DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','asignada','en_camino','en_proceso','completada','cancelada') DEFAULT 'pendiente',
  `consentimiento_datos` tinyint(1) DEFAULT '0',
  `ip_cliente` varchar(45) DEFAULT NULL,
  `user_agent` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes`
--

LOCK TABLES `solicitudes` WRITE;
/*!40000 ALTER TABLE `solicitudes` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `ID_Usuario` int NOT NULL AUTO_INCREMENT,
  `ID_Empleado` int NOT NULL,
  `ROL` enum('Admin','Chofer','RH','Gerente','SysAdmin') NOT NULL,
  `Usuario` varchar(50) NOT NULL,
  `Contraseña` varchar(25) NOT NULL,
  `Fecha_cambio_contraseña` datetime DEFAULT NULL,
  PRIMARY KEY (`ID_Usuario`),
  KEY `ID_Empleado_idx` (`ID_Empleado`),
  CONSTRAINT `ID_EU` FOREIGN KEY (`ID_Empleado`) REFERENCES `empleados` (`ID_Empleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,1,'SysAdmin','KRLP','2001','2025-05-05 17:40:53');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER `actualizar_fecha_cambio_contraseña` BEFORE UPDATE ON `usuarios` FOR EACH ROW BEGIN
    IF OLD.`Contraseña` <> NEW.`Contraseña` THEN
        SET NEW.`Fecha_cambio_contraseña` = NOW();
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping events for database 'dback'
--

--
-- Additional compatibility objects required by the PHP app
-- (agregados para que no falte nada que el código usa)
--

-- Ampliar tabla gruas con campo de disponibilidad
ALTER TABLE `gruas`
  ADD COLUMN `disponible_desde` datetime DEFAULT NULL;

--
-- Table structure for table `configuracion_auto_asignacion`
--
DROP TABLE IF EXISTS `configuracion_auto_asignacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion_auto_asignacion` (
  `parametro` varchar(100) NOT NULL,
  `valor` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`parametro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gruas_disponibles`
--
DROP TABLE IF EXISTS `gruas_disponibles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gruas_disponibles` (
  `ID` int NOT NULL,
  `tiene_coordenadas` tinyint(1) NOT NULL DEFAULT '0',
  `disponible_desde` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  CONSTRAINT `fk_gd_grua` FOREIGN KEY (`ID`) REFERENCES `gruas` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuracion_tipos_servicio`
--
DROP TABLE IF EXISTS `configuracion_tipos_servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion_tipos_servicio` (
  `tipo_servicio` varchar(50) NOT NULL,
  `tipo_grua_preferido` varchar(50) NOT NULL,
  PRIMARY KEY (`tipo_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historial_asignaciones`
--
DROP TABLE IF EXISTS `historial_asignaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_asignaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solicitud_id` int NOT NULL,
  `grua_id` int DEFAULT NULL,
  `equipo_asignado_id` int DEFAULT NULL,
  `metodo_asignacion` varchar(20) DEFAULT NULL,
  `fecha_asignacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitud_id_idx` (`solicitud_id`),
  KEY `grua_id_idx` (`grua_id`),
  CONSTRAINT `fk_hist_asig_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`),
  CONSTRAINT `fk_hist_asig_grua` FOREIGN KEY (`grua_id`) REFERENCES `gruas` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notificaciones_usuarios`
--
DROP TABLE IF EXISTS `notificaciones_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text,
  `creada_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id_idx` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eventos_sistema`
--
DROP TABLE IF EXISTS `eventos_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos_sistema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  `descripcion` text,
  `creada_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suspension_servicio`
--
DROP TABLE IF EXISTS `suspension_servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suspension_servicio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `razon` text,
  `fecha_suspension` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Ampliar tabla solicitudes con campos usados por módulos
--
ALTER TABLE `solicitudes`
  ADD COLUMN `cliente_id` int DEFAULT NULL,
  ADD COLUMN `fecha_servicio` datetime DEFAULT NULL,
  ADD COLUMN `ubicacion_origen` text,
  ADD COLUMN `ubicacion_destino` text,
  ADD COLUMN `costo` decimal(10,2) DEFAULT NULL,
  ADD COLUMN `descripcion` text,
  ADD COLUMN `grua_asignada_id` int DEFAULT NULL,
  ADD COLUMN `equipo_asignado_id` int DEFAULT NULL,
  ADD COLUMN `metodo_asignacion` varchar(20) DEFAULT NULL,
  ADD COLUMN `fecha_asignacion` datetime DEFAULT NULL,
  ADD COLUMN `fecha_completado` datetime DEFAULT NULL,
  ADD COLUMN `usuario_id` int DEFAULT NULL;

--
-- Table structure for table `equipos_ayuda`
--
DROP TABLE IF EXISTS `equipos_ayuda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipos_ayuda` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Tipo_Servicio` varchar(50) NOT NULL,
  `Disponible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Ampliar tabla empleados con campos de estado/departamento
--
ALTER TABLE `empleados`
  ADD COLUMN `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  ADD COLUMN `fecha_baja` datetime DEFAULT NULL,
  ADD COLUMN `departamento` varchar(100) DEFAULT NULL;

--
-- Table structure for table `historial_empleados`
--
DROP TABLE IF EXISTS `historial_empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_empleados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `empleado_id_idx` (`empleado_id`),
  CONSTRAINT `fk_hist_emp` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`ID_Empleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clientes`
--
DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `solicitudes_servicio`
--
DROP TABLE IF EXISTS `solicitudes_servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_servicio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_cliente` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ubicacion_origen` text NOT NULL,
  `ubicacion_destino` text NOT NULL,
  `tipo_vehiculo` varchar(50) NOT NULL,
  `marca_vehiculo` varchar(50) NOT NULL,
  `modelo_vehiculo` varchar(50) NOT NULL,
  `foto_vehiculo` varchar(255) DEFAULT NULL,
  `tipo_servicio` varchar(50) NOT NULL,
  `descripcion_problema` text,
  `nivel_urgencia` varchar(20) NOT NULL,
  `distancia_estimada` decimal(10,2) DEFAULT NULL,
  `costo_estimado` decimal(10,2) DEFAULT NULL,
  `metodo_pago` varchar(20) DEFAULT NULL,
  `deposito` decimal(10,2) DEFAULT NULL,
  `pago_restante` decimal(10,2) DEFAULT NULL,
  `paypal_order_id` varchar(50) DEFAULT NULL,
  `paypal_status` varchar(20) DEFAULT NULL,
  `paypal_email` varchar(100) DEFAULT NULL,
  `paypal_name` varchar(100) DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mantenimientos`
--
DROP TABLE IF EXISTS `mantenimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mantenimientos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `GruaID` int NOT NULL,
  `Tipo` varchar(100) NOT NULL,
  `Fecha` date NOT NULL,
  `Tecnico` varchar(100) DEFAULT NULL,
  `Costo` decimal(10,2) DEFAULT NULL,
  `Detalles` text,
  PRIMARY KEY (`ID`),
  KEY `GruaID_idx` (`GruaID`),
  CONSTRAINT `fk_mant_grua` FOREIGN KEY (`GruaID`) REFERENCES `gruas` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Ampliar tabla usuarios con campos opcionales usados por reportes
--
ALTER TABLE `usuarios`
  ADD COLUMN `cargo` varchar(50) DEFAULT NULL,
  ADD COLUMN `activo` tinyint(1) NOT NULL DEFAULT '1';

--
-- Dumping routines for database 'dback'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-05 17:53:12
