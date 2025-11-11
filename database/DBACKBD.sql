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
  `Nombres` varchar(100) NOT NULL,
  `Apellido1` varchar(50) NOT NULL,
  `Apellido2` varchar(50) DEFAULT NULL,
  `RFC` varchar(13) NOT NULL,
  `Nomina` bigint NOT NULL,
  `Fecha_Ingreso` date NOT NULL,
  `Puesto` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `Sueldo` decimal(10,2) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `licencia` varchar(25) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_Empleado`),
  UNIQUE KEY `RFC_UNIQUE` (`RFC`),
  UNIQUE KEY `Nomina_UNIQUE` (`Nomina`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados`
--

LOCK TABLES `empleados` WRITE;
/*!40000 ALTER TABLE `empleados` DISABLE KEYS */;
INSERT INTO `empleados` (`ID_Empleado`,`Nombres`,`Apellido1`,`Apellido2`,`RFC`,`Nomina`,`Fecha_Ingreso`,`Puesto`,`departamento`,`Sueldo`,`telefono`,`email`,`licencia`,`direccion`,`estado`) VALUES
(1,'Kevin Ricardo','Lopez','Payan','LOPK800101ABC',100001,'2023-02-14','Ingeniero de Sistemas','Tecnología',18500.00,'6688253351','kevin.lopez@dback.com','LIC765432','Av. Álamos 120, Los Mochis, Sin.','activo'),
(2,'Angel Gabriel','Flores','Guevara','FLOG900215MNB',100002,'2022-11-03','Analista de Datos','Tecnología',16200.00,'6688123476','angel.flores@dback.com','LIC554433','Calle Hidalgo 245, Los Mochis, Sin.','activo'),
(3,'Mariana','Torres','Delgado','TODM870612PL1',100003,'2021-08-16','Coordinadora de Operaciones','Operaciones',14800.00,'6688098765','mariana.torres@dback.com','LIC102938','Blvd. Rosales 560, Los Mochis, Sin.','activo'),
(4,'Luis Fernando','Sanchez','Ibarra','SAIL850927QW8',100004,'2019-05-10','Supervisor de Flota','Operaciones',17250.00,'6688345120','luis.sanchez@dback.com','LIC564738','Av. Independencia 88, Los Mochis, Sin.','activo'),
(5,'Daniela','Gomez','Ruiz','GORD920304TX5',100005,'2020-01-22','Especialista RH','Recursos Humanos',13900.00,'6688450912','daniela.gomez@dback.com','LIC837465','C. Benito Juárez 410, Los Mochis, Sin.','activo'),
(6,'Carlos Eduardo','Martinez','Leon','MALE930718ZR3',100006,'2018-07-02','Contador Senior','Finanzas',15800.00,'6688542034','carlos.martinez@dback.com','LIC293847','Av. Gabriel Leyva 920, Los Mochis, Sin.','activo'),
(7,'Sofia','Hernandez','Campos','HECS950112JK7',100007,'2021-03-18','Ejecutiva Comercial','Ventas',13250.00,'6688675490','sofia.hernandez@dback.com','LIC485920','C. Ignacio Zaragoza 302, Los Mochis, Sin.','activo'),
(8,'Jorge Alberto','Navarro','Vega','NAVJ880925LM4',100008,'2017-09-05','Jefe de Taller','Mantenimiento',16700.00,'6688723415','jorge.navarro@dback.com','LIC657483','Carretera México 15 Km 3, Los Mochis, Sin.','activo'),
(9,'Paola Andrea','Rios','Luna','RILP940615SD2',100009,'2022-04-11','Coordinadora de Marketing','Marketing',12600.00,'6688901234','paola.rios@dback.com','LIC918273','Av. Constitución 150, Los Mochis, Sin.','activo'),
(10,'Miguel Angel','Cardenas','Soto','CASM860730HU6',100010,'2016-12-01','Gerente de Operaciones','Operaciones',21500.00,'6688998765','miguel.cardenas@dback.com','LIC746291','Blvd. Centenario 230, Los Mochis, Sin.','activo'),
(11,'Fernanda','Perez','Ortiz','PEOF970209BF1',100011,'2023-06-19','Asesora de Servicio','Atención al Cliente',11800.00,'6688789456','fernanda.perez@dback.com','LIC564738','C. Allende 67, Los Mochis, Sin.','activo'),
(12,'Ricardo Emilio','Aguilar','Mora','AURR910521CN9',100012,'2015-03-23','Director General','Dirección',32000.00,'6688844321','ricardo.aguilar@dback.com','LIC120045','Av. Álvaro Obregón 800, Los Mochis, Sin.','activo');
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `ActualizarFechaIngresoEmpleado` BEFORE INSERT ON `empleados` FOR EACH ROW BEGIN
    IF NEW.Fecha_Ingreso IS NULL OR NEW.Fecha_Ingreso = '' THEN
        SET NEW.Fecha_Ingreso = CURRENT_DATE();
    END IF;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `Bajarsueldo` BEFORE UPDATE ON `empleados` FOR EACH ROW BEGIN
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
  `ID` int NOT NULL,
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `guardar_fecha_hora_mantenimiento` BEFORE INSERT ON `mantenimiento_gruas` FOR EACH ROW BEGIN
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
  `ID_Usuario` int NOT NULL,
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `actualizar_fecha_cambio_contraseña` BEFORE UPDATE ON `usuarios` FOR EACH ROW BEGIN
    IF OLD.contraseña <> NEW.contraseña THEN
        SET NEW.Fecha_cambio_contraseña = NOW();
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
