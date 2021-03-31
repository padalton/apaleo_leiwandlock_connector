<?php
require_once('config.php');

date_default_timezone_set("Europe/Vienna");

$db = new PDO("mysql:host={$config['db']['host']};port={$config['db']['port']};charset=utf8",
    $config['db']['user'],
    $config['db']['password']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE DATABASE IF NOT EXISTS `{$config['db']['db_name']}` /*!40100 COLLATE 'utf8_general_ci' */");
$db->exec("use `{$config['db']['db_name']}`");

$db->exec("CREATE TABLE IF NOT EXISTS `auth_codes` (
`id` VARCHAR(36) NOT NULL,
	`request_sent` TINYINT(1) NOT NULL DEFAULT 0,
	`auth_code` TEXT NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'");


$db->exec("CREATE TABLE IF NOT EXISTS `apaleo_tokens` (
`id` varchar(255) NOT NULL,
  `access_token` text,
  `refresh_token` text,
  `account_id` varchar(50) DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `scope` text,
  `token_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `apaleo_tokens_exp_pk` (`expires`)
) 
COLLATE='utf8_general_ci'");

$db->exec("CREATE TABLE IF NOT EXISTS `leiwand_setup` (
	`id` VARCHAR(36) NOT NULL,
	`account_code` VARCHAR(10) NOT NULL,
	`property_id` VARCHAR(10) NOT NULL,
	`host` VARCHAR(150) NOT NULL,
	`ll_token` VARCHAR(100) NOT NULL,
	`active` TINYINT(1) NOT NULL DEFAULT 0,
	`subscription_id` VARCHAR(50) DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'");

$db->exec("CREATE TABLE IF NOT EXISTS `leiwand_units` (
	`id` VARCHAR(36) NOT NULL,
	`account_id` VARCHAR(36) NOT NULL,
	`property_id` VARCHAR(36) NOT NULL,
	`room_id` VARCHAR(36) NOT NULL,
	`active` TINYINT(1) NOT NULL DEFAULT 0,
	`synced` TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'");

$db->exec("CREATE TABLE IF NOT EXISTS `reservations` (
	`id` VARCHAR(100) NOT NULL,
	`account_id` VARCHAR(50) NULL,
	`property_id` VARCHAR(50) NULL,
	`reservation_id` VARCHAR(50) NULL,
	`qrcode_generated` TINYINT(1) NOT NULL DEFAULT 0,
	`email_sent` DATETIME DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'");

$db->exec("CREATE TABLE IF NOT EXISTS `email_setup` (
  `id` varchar(100) NOT NULL,
  `account_id` varchar(50) DEFAULT NULL,
  `SMTPSecure` varchar(10) DEFAULT NULL,
  `SMTPAuth` tinyint(1) NOT NULL DEFAULT '0',
  `Host` varchar(150) DEFAULT NULL,
  `Port` int(11) DEFAULT '25',
  `Username` varchar(50) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL,
  `FromAddress` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) 
COLLATE='utf8_general_ci'");