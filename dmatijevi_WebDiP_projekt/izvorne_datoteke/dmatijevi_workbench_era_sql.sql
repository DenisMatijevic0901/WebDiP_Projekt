-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema WebDiP2021x071
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema WebDiP2021x071
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `WebDiP2021x071` ;
USE `WebDiP2021x071` ;

-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`tip_korisnika`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`tip_korisnika` (
  `tip_korisnika_id` INT NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`tip_korisnika_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`korisnik`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`korisnik` (
  `korisnik_id` INT NOT NULL AUTO_INCREMENT,
  `ime` VARCHAR(45) NOT NULL,
  `prezime` VARCHAR(45) NOT NULL,
  `datum_rodenja` DATE NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `korisnicko_ime` VARCHAR(45) NOT NULL,
  `lozinka` VARCHAR(45) NOT NULL,
  `lozinka_sha256` CHAR(64) NOT NULL,
  `broj_neuspjesnih_prijava` TINYINT NOT NULL,
  `status` TINYINT NOT NULL,
  `aktivacijski_kod` VARCHAR(50) NOT NULL,
  `aktiviran` TINYINT NOT NULL,
  `vrijeme_registriranja` TIMESTAMP NULL,
  `tip_korisnika` INT NOT NULL,
  PRIMARY KEY (`korisnik_id`),
  INDEX `fk_korisnik_korisnik_tip_korisnika_idx` (`tip_korisnika` ASC),
  CONSTRAINT `fk_korisnik_tip_korisnika_tip_korisnika_id`
    FOREIGN KEY (`tip_korisnika`)
    REFERENCES `WebDiP2021x071`.`tip_korisnika` (`tip_korisnika_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`tip_radnje`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`tip_radnje` (
  `tip_radnje_id` INT NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`tip_radnje_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`dnevnik_rada`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`dnevnik_rada` (
  `dnevnik_rada_id` INT NOT NULL AUTO_INCREMENT,
  `korisnik` INT NOT NULL,
  `tip_radnje` INT NOT NULL,
  `radnja` VARCHAR(200) NULL,
  `upit` VARCHAR(200) NULL,
  `datum_vrijeme` DATETIME NOT NULL,
  PRIMARY KEY (`dnevnik_rada_id`),
  INDEX `fk_dnevnik_rada_korisnik_korisnik_id_idx` (`korisnik` ASC),
  INDEX `fk_dnevnik_rada_tip_radnje_tip_radnje_id_idx` (`tip_radnje` ASC),
  CONSTRAINT `fk_dnevnik_rada_korisnik_korisnik_id`
    FOREIGN KEY (`korisnik`)
    REFERENCES `WebDiP2021x071`.`korisnik` (`korisnik_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_dnevnik_rada_tip_radnje_tip_radnje_id`
    FOREIGN KEY (`tip_radnje`)
    REFERENCES `WebDiP2021x071`.`tip_radnje` (`tip_radnje_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`drzava`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`drzava` (
  `drzava_id` INT NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(50) NOT NULL,
  `glavni_grad` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`drzava_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`moderator_drzava`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`moderator_drzava` (
  `korisnik` INT NOT NULL,
  `drzava` INT NOT NULL,
  PRIMARY KEY (`korisnik`, `drzava`),
  INDEX `fk_moderator_drzava_drzava_drzava_id_idx` (`drzava` ASC),
  CONSTRAINT `fk_moderator_drzava_korisnik_korisnik_id`
    FOREIGN KEY (`korisnik`)
    REFERENCES `WebDiP2021x071`.`korisnik` (`korisnik_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_moderator_drzava_drzava_drzava_id`
    FOREIGN KEY (`drzava`)
    REFERENCES `WebDiP2021x071`.`drzava` (`drzava_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`utrka`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`utrka` (
  `utrka_id` INT NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(70) NOT NULL,
  `vrijeme_zavrsetka_prijava` DATETIME NOT NULL,
  `zakljucana` TINYINT NOT NULL,
  `drzava` INT NOT NULL,
  PRIMARY KEY (`utrka_id`),
  INDEX `fk_utrka_drzava_drzava_id_idx` (`drzava` ASC),
  CONSTRAINT `fk_utrka_drzava_drzava_id`
    FOREIGN KEY (`drzava`)
    REFERENCES `WebDiP2021x071`.`drzava` (`drzava_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`etapa`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`etapa` (
  `etapa_id` INT NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(50) NOT NULL,
  `datum_i_vrijeme` DATETIME NOT NULL,
  `zakljucana` TINYINT NOT NULL,
  `utrka` INT NOT NULL,
  PRIMARY KEY (`etapa_id`),
  INDEX `fk_etapa_utrka_utrka_id_idx` (`utrka` ASC),
  CONSTRAINT `fk_etapa_utrka_utrka_id`
    FOREIGN KEY (`utrka`)
    REFERENCES `WebDiP2021x071`.`utrka` (`utrka_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`prijava`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`prijava` (
  `prijava_id` INT NOT NULL AUTO_INCREMENT,
  `godina_rodenja` INT NOT NULL,
  `slika` VARCHAR(100) NOT NULL,
  `utrka` INT NOT NULL,
  `korisnik` INT NOT NULL,
  PRIMARY KEY (`prijava_id`),
  INDEX `fk_prijava_utrka_utrka_id_idx` (`utrka` ASC),
  INDEX `fk_prijava_korisnik_korisnik_id_idx` (`korisnik` ASC),
  CONSTRAINT `fk_prijava_utrka_utrka_id`
    FOREIGN KEY (`utrka`)
    REFERENCES `WebDiP2021x071`.`utrka` (`utrka_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_prijava_korisnik_korisnik_id`
    FOREIGN KEY (`korisnik`)
    REFERENCES `WebDiP2021x071`.`korisnik` (`korisnik_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`rezultat_etape`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`rezultat_etape` (
  `rezultat_id` INT NOT NULL AUTO_INCREMENT,
  `korisnik` INT NOT NULL,
  `etapa` INT NOT NULL,
  `evidentirano_vrijeme` TIME NULL,
  `ostvareni_bodovi` INT NULL,
  `odustao` TINYINT NOT NULL,
  PRIMARY KEY (`rezultat_id`),
  INDEX `fk_rezultat_korisnik_korisnik_id_idx` (`korisnik` ASC),
  INDEX `fk_rezultat_etapa_etapa_id_idx` (`etapa` ASC),
  CONSTRAINT `fk_rezultat_korisnik_korisnik_id`
    FOREIGN KEY (`korisnik`)
    REFERENCES `WebDiP2021x071`.`korisnik` (`korisnik_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_rezultat_etapa_etapa_id`
    FOREIGN KEY (`etapa`)
    REFERENCES `WebDiP2021x071`.`etapa` (`etapa_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `WebDiP2021x071`.`pobjednik`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WebDiP2021x071`.`pobjednik` (
  `pobjednik_id` INT NOT NULL AUTO_INCREMENT,
  `utrka` INT NOT NULL,
  `korisnik` INT NOT NULL,
  PRIMARY KEY (`pobjednik_id`),
  INDEX `fk_pobjednik_utkra_utrka_id_idx` (`utrka` ASC),
  INDEX `fk_pobjednik_korisnik_korisnik_id_idx` (`korisnik` ASC),
  UNIQUE INDEX `utrka_UNIQUE` (`utrka` ASC),
  CONSTRAINT `fk_pobjednik_utkra_utrka_id`
    FOREIGN KEY (`utrka`)
    REFERENCES `WebDiP2021x071`.`utrka` (`utrka_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_pobjednik_korisnik_korisnik_id`
    FOREIGN KEY (`korisnik`)
    REFERENCES `WebDiP2021x071`.`korisnik` (`korisnik_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
