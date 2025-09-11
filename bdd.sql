-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: shopping
-- ------------------------------------------------------
-- Server version	9.4.0

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
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria` (
  `IDcategoria` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(45) DEFAULT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`IDcategoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local`
--

DROP TABLE IF EXISTS `local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local` (
  `IDlocal` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `ubicacion` varchar(45) DEFAULT NULL,
  `rubro` varchar(45) NOT NULL,
  `usuarioFK` int NOT NULL,
  PRIMARY KEY (`IDlocal`),
  KEY `usuario-local_idx` (`usuarioFK`),
  CONSTRAINT `usuario-local` FOREIGN KEY (`usuarioFK`) REFERENCES `usuario` (`IDusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local`
--

LOCK TABLES `local` WRITE;
/*!40000 ALTER TABLE `local` DISABLE KEYS */;
/*!40000 ALTER TABLE `local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `novedad`
--

DROP TABLE IF EXISTS `novedad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `novedad` (
  `IDnovedad` int NOT NULL AUTO_INCREMENT,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `usuarioHabilitado` varchar(45) NOT NULL,
  `descripcion` varchar(1000) DEFAULT NULL,
  `cabecera` varchar(3000) DEFAULT NULL,
  `cuerpo` varchar(7000) DEFAULT NULL,
  `imagen` blob,
  PRIMARY KEY (`IDnovedad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `novedad`
--

LOCK TABLES `novedad` WRITE;
/*!40000 ALTER TABLE `novedad` DISABLE KEYS */;
/*!40000 ALTER TABLE `novedad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocion`
--

DROP TABLE IF EXISTS `promocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promocion` (
  `IDpromocion` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(45) NOT NULL,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `categoriaHabilitada` varchar(45) NOT NULL,
  `dia` int DEFAULT NULL,
  `estado` varchar(45) NOT NULL,
  `localFk` int NOT NULL,
  PRIMARY KEY (`IDpromocion`),
  KEY `local-promocion_idx` (`localFk`),
  CONSTRAINT `promocion-local` FOREIGN KEY (`localFk`) REFERENCES `local` (`IDlocal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocion`
--

LOCK TABLES `promocion` WRITE;
/*!40000 ALTER TABLE `promocion` DISABLE KEYS */;
/*!40000 ALTER TABLE `promocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `IDrol` int NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `descripcion` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`IDrol`),
  UNIQUE KEY `nombre_UNIQUE` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usopromocion`
--

DROP TABLE IF EXISTS `usopromocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usopromocion` (
  `usuarioFk` int NOT NULL,
  `promoFK` int NOT NULL,
  `fechaUso` date DEFAULT NULL,
  `estado` varchar(45) NOT NULL,
  PRIMARY KEY (`usuarioFk`,`promoFK`),
  KEY `uso-promocion_idx` (`promoFK`),
  CONSTRAINT `uso-promocion` FOREIGN KEY (`promoFK`) REFERENCES `promocion` (`IDpromocion`),
  CONSTRAINT `uso-usuario` FOREIGN KEY (`usuarioFk`) REFERENCES `usuario` (`IDusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usopromocion`
--

LOCK TABLES `usopromocion` WRITE;
/*!40000 ALTER TABLE `usopromocion` DISABLE KEYS */;
/*!40000 ALTER TABLE `usopromocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `IDusuario` int NOT NULL AUTO_INCREMENT,
  `nombreUsuario` varchar(45) NOT NULL,
  `clave` varchar(45) NOT NULL,
  `tipoFK` int NOT NULL,
  `categoriaFK` int NOT NULL,
  `estado` binary(1) DEFAULT '0',
  PRIMARY KEY (`IDusuario`),
  KEY `usuario-rol_idx` (`tipoFK`),
  KEY `usuario-categoria_idx` (`categoriaFK`),
  CONSTRAINT `usuario-categoria` FOREIGN KEY (`categoriaFK`) REFERENCES `categoria` (`IDcategoria`),
  CONSTRAINT `usuario-rol` FOREIGN KEY (`tipoFK`) REFERENCES `rol` (`IDrol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-11 17:14:56
