-- Beta Orionis (Rigel) Database Schemas
-- Rigel The White Blue Giant, The Leg of Orion, Osiris
-- Amon Ra Eye
-- Version 1.0.1 (Binary Id)
-- 3bit.app Copyright (c) 2023, MySQL version
-- All rights reserved.
-- Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
-- written permission from the software developer to use or copy it

SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'TRADITIONAL';
-- SET @CHARSET_COLLATE = 'DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci'; -- DEFAULT CHARSET = utf8; | DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/* Service schema
 * DROP SCHEMA IF EXISTS beta_orionis_service;
 * CREATE SCHEMA beta_orionis_service;
 * USE beta_orionis_service;
 */

/* Namecheap hosting
 * 
 * Database schema: Service
 *   
 * admin credentials
 * -----------------
 * database: xbitoakr_beta_orionis_service
 * username: xbitoakr_beta_orionis
 * password: ...
 *
 */

-- USE <schema_name>;
-- ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_general_ci

-- Database schema: Service

/* 
 * DROP SCHEMA IF EXISTS xbitoakr_beta_orionis_service;  
 * CREATE SCHEMA xbitoakr_beta_orionis_service CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci; 
 */

USE xbitoakr_beta_orionis_service;

-- Doker
-- DROP SCHEMA IF EXISTS doker_beta_orionis;   
-- CREATE SCHEMA doker_beta_orionis CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci; 
-- USE doker_beta_orionis;

-- [tables]
-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////

-- ------------ [main] ------------

-- users ( roles: 1 - admin, 2 - customer ) identical for role name

DROP TABLE IF EXISTS `users`; 

CREATE TABLE `users` (

    user_id BINARY(16) NOT NULL,
    role_id BINARY(16) DEFAULT NULL,
    first_name VARCHAR(64) NOT NULL,
    last_name VARCHAR(64) NOT NULL,
    email VARCHAR(64) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    username VARCHAR(64) UNIQUE NOT NULL,
    password BINARY(20) DEFAULT NULL,
    picture VARCHAR(512) DEFAULT NULL,
    status TINYINT NOT NULL DEFAULT 0, -- 0 undefined 1 activated -1 banned -2 temporary banned -10 auth disabled -11 removed
    access_token VARCHAR(767) UNIQUE DEFAULT NULL, -- 64 bytes DEFAULT, 767 max LENGTH (JWT)
    access_token_created TIMESTAMP DEFAULT NULL, -- for any request, restricted limited time
    update_token VARCHAR(64) NOT NULL DEFAULT '', -- for get_token request parameter (token is update_token), restricted once used
    -- update_token one time used
    -- update_token_created TIMESTAMP DEFAULT NULL, -- not used
    -- refresh_token possible many times using
    -- refresh_token VARCHAR(64) NOT NULL DEFAULT '', -- token possible many times using
    -- refresh_token_created TIMESTAMP DEFAULT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
    PRIMARY KEY (user_id),
    KEY idx_username (username),
    KEY idx_access_token (access_token),
    FULLTEXT KEY idx_first_name_last_name (first_name, last_name),
    -- KEY idx_first_name (first_name),
    -- KEY idx_last_name (last_name),
    KEY idx_email (email),
    KEY idx_phone (phone),
    KEY idx_status (status),
    KEY idx_update_token (update_token),
    KEY idx_fk_role_id (role_id),
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES `roles` (role_id) ON DELETE SET NULL
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- sensors

DROP TABLE IF EXISTS `sensors`;

CREATE TABLE `sensors` (

    sensor_id BINARY(16) NOT NULL,
    user_id BINARY(16) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    serial_number VARCHAR(64) DEFAULT NULL,
    device_name VARCHAR(64) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
    PRIMARY KEY (sensor_id),
    KEY idx_name (name),
    KEY idx_device_name (device_name),
    KEY idx_phone (phone),
    KEY idx_fk_user_id (user_id),
    CONSTRAINT fk_sensor_user FOREIGN KEY (user_id) REFERENCES `users` (user_id) ON DELETE CASCADE
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- tracks

DROP TABLE IF EXISTS `tracks`;

CREATE TABLE `tracks` (

    track_id BINARY(16) NOT NULL,
    sensor_id BINARY(16) NOT NULL,
    latitude DOUBLE NOT NULL,
    longitude DOUBLE NOT NULL,
    `time` BIGINT DEFAULT NULL,
    altitude DOUBLE DEFAULT NULL,
    accuracy DOUBLE DEFAULT NULL,
    bearing DOUBLE DEFAULT NULL,
    speed DOUBLE DEFAULT NULL,
    satellites TINYINT DEFAULT NULL,
    battery TINYINT DEFAULT NULL,
    time_offset SMALLINT DEFAULT NULL, -- timezone_offset + dst_offset
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
    PRIMARY KEY (track_id),
    KEY idx_time (time),
    KEY idx_create_date (created_at),
    KEY idx_fk_sensor_id (sensor_id),
    CONSTRAINT fk_track_sensor FOREIGN KEY (sensor_id) REFERENCES `sensors` (sensor_id) ON DELETE CASCADE

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- track_parts

DROP TABLE IF EXISTS `track_parts`;

CREATE TABLE `track_parts` (

    track_part_id BINARY(16) NOT NULL,
    track_id BINARY(16) NOT NULL,
    track_type_id BINARY(16) DEFAULT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    tags VARCHAR(256) NOT NULL DEFAULT '',
    picture VARCHAR(512) DEFAULT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (track_part_id),
    KEY idx_name (name),
    KEY idx_tags (tags),
	KEY idx_fk_track_id (track_id),
    CONSTRAINT fk_track FOREIGN KEY (track_id) REFERENCES `tracks` (track_id) ON DELETE CASCADE,
    KEY idx_fk_track_type_id (track_type_id),
    CONSTRAINT fk_track_type FOREIGN KEY (track_type_id) REFERENCES `track_types` (track_type_id) ON DELETE SET NULL

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- routes

DROP TABLE IF EXISTS `routes`;

CREATE TABLE `routes` (

    route_id BINARY(16) NOT NULL,
    route_type_id BINARY(16) DEFAULT NULL,
    user_id BINARY(16) NOT NULL,
    group_id BINARY(16) DEFAULT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    tags VARCHAR(256) NOT NULL DEFAULT '',
    picture VARCHAR(512) DEFAULT NULL,
    started TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    finished TIMESTAMP NULL DEFAULT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (route_id),
    KEY idx_name (name),
    KEY idx_tags (tags),
    KEY idx_started (started),
    KEY idx_finished (finished),
    KEY idx_fk_route_type_id (route_type_id),
    CONSTRAINT fk_route_type FOREIGN KEY (route_type_id) REFERENCES `route_types` (route_type_id) ON DELETE SET NULL,
    KEY idx_fk_user_id (user_id),
    CONSTRAINT fk_route_owner_user FOREIGN KEY (user_id) REFERENCES `users` (user_id) ON DELETE CASCADE,
    KEY idx_fk_group_id (group_id),
    CONSTRAINT fk_route_group FOREIGN KEY (group_id) REFERENCES `groups` (group_id) ON DELETE CASCADE

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- route_parts

DROP TABLE IF EXISTS `route_parts`;

CREATE TABLE `route_parts` (

    route_part_id BINARY(16) NOT NULL,
    route_id BINARY(16) NOT NULL,
    track_type_id BINARY(16) DEFAULT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    tags VARCHAR(256) NOT NULL DEFAULT '',
    picture VARCHAR(512) DEFAULT NULL,
    latitude DOUBLE NOT NULL,
    longitude DOUBLE NOT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (route_part_id),
    KEY idx_name (name),
    KEY idx_tags (tags),
	KEY idx_fk_route_id (route_id),
    CONSTRAINT fk_route FOREIGN KEY (route_id) REFERENCES `routes` (route_id) ON DELETE CASCADE,
    KEY idx_fk_track_type_id (track_type_id),
    CONSTRAINT fk_route_track_type FOREIGN KEY (track_type_id) REFERENCES `track_types` (track_type_id) ON DELETE SET NULL

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- groups

DROP TABLE IF EXISTS `groups`;

CREATE TABLE `groups` (

    group_id BINARY(16) NOT NULL,
    user_id BINARY(16) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    tags VARCHAR(256) NOT NULL DEFAULT '',
    picture VARCHAR(512) DEFAULT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (group_id),
    KEY idx_name (name),
    KEY idx_description (description),
    KEY idx_tags (tags),
    KEY idx_fk_user_id (user_id),
    CONSTRAINT fk_group_owner_user FOREIGN KEY (user_id) REFERENCES `users` (user_id) ON DELETE CASCADE

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- group_users

DROP TABLE IF EXISTS `group_users`;

CREATE TABLE `group_users` (

    group_id BINARY(16) NOT NULL,
    user_id BINARY(16) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (group_id, user_id), -- UNIQUE KEY idx_unique_group_user (group_id, user_id)
    KEY idx_fk_group_id (group_id),
    CONSTRAINT fk_group FOREIGN KEY (group_id) REFERENCES `groups` (group_id) ON DELETE CASCADE,
    KEY idx_fk_user_id (user_id),
    CONSTRAINT fk_group_user FOREIGN KEY (user_id) REFERENCES `users` (user_id) ON DELETE CASCADE

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [dictionaries] ------------

-- languages (support languages for schema, language_id should be unique for all)

DROP TABLE IF EXISTS `languages`;

CREATE TABLE `languages` (

    language_id BINARY(16) NOT NULL,
    name VARCHAR(128) UNIQUE NOT NULL,
    code VARCHAR(5) UNIQUE NOT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
    PRIMARY KEY (language_id),
    KEY idx_name (name),
    KEY idx_code (code)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- track_types

DROP TABLE IF EXISTS `track_types`;

CREATE TABLE `track_types` (

    track_type_id BINARY(16) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
    PRIMARY KEY (track_type_id),
    -- FULLTEXT KEY idx_name_description (name, description),
    KEY idx_name (name),
    KEY idx_description (description)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- route_types

DROP TABLE IF EXISTS `route_types`;

CREATE TABLE `route_types` (

    route_type_id BINARY(16) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
    PRIMARY KEY (route_type_id),
    -- FULLTEXT KEY idx_name_description (name, description),
    KEY idx_name (name),
    KEY idx_description (description)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [union helpers] ------------

-- ------------ [settings / options] ------------

-- settings

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (

    name VARCHAR(128) UNIQUE NOT NULL,
    description VARCHAR(256) NOT NULL DEFAULT '',
    value VARCHAR(512) NOT NULL DEFAULT '',
    `type` VARCHAR(16) NOT NULL DEFAULT 'string', -- type: string, number, boolean, array
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_name (name)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- object_options ( table objects options, object_name: user, route  )

DROP TABLE IF EXISTS `object_options`;

CREATE TABLE `object_options` (

    object_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    value VARCHAR(512) NOT NULL DEFAULT '',
    option_type VARCHAR(16) NOT NULL DEFAULT 'string', -- option_type: string, number, boolean, array
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (object_id, object_name, name),
    KEY idx_name (name)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- object_translations ( table objects translations, object_name: route, route_types )

DROP TABLE IF EXISTS `object_translations`;

CREATE TABLE `object_translations` (

    object_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    value VARCHAR(512) NOT NULL DEFAULT '',
    language_code VARCHAR(5) DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (object_id, object_name, name),
    KEY idx_name (name),
    KEY idx_language_code (language_code)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- roles

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (

    role_id BINARY(16) NOT NULL,
    name VARCHAR(64) NOT NULL, -- role name
    description VARCHAR(256) NOT NULL DEFAULT '',
    
    PRIMARY KEY (role_id),
    KEY idx_name (name),
    KEY idx_description (description)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- role_privileges ( access right role for privileges to table objects )

DROP TABLE IF EXISTS `role_privileges`;

CREATE TABLE `role_privileges` (

    role_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    can_select BOOLEAN NOT NULL DEFAULT FALSE,
    can_insert BOOLEAN NOT NULL DEFAULT FALSE,
    can_update BOOLEAN NOT NULL DEFAULT FALSE,
    can_delete BOOLEAN NOT NULL DEFAULT FALSE,
    
    PRIMARY KEY (role_id, object_name),
    KEY idx_object_name (object_name),
    KEY idx_fk_role_id (role_id),
    CONSTRAINT fk_role_privilege FOREIGN KEY (role_id) REFERENCES `roles` (role_id) ON DELETE CASCADE

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- user_privileges ( access right user for select | insert | update | delete object_id row of object_name)

DROP TABLE IF EXISTS `user_privileges`;

CREATE TABLE `user_privileges` (

    user_id BINARY(16) NOT NULL,
    object_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    can_select BOOLEAN NOT NULL DEFAULT FALSE,
    can_insert BOOLEAN NOT NULL DEFAULT FALSE,
    can_update BOOLEAN NOT NULL DEFAULT FALSE,
    can_delete BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (user_id, object_id, object_name),
    KEY idx_object_name (object_name),
	KEY idx_fk_user_id (user_id),
    CONSTRAINT fk_user_privilege FOREIGN KEY (user_id) REFERENCES `users` (user_id) ON DELETE CASCADE

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- attempts (user login, register, token, password recovery attempts)

DROP TABLE IF EXISTS `attempts`;

CREATE TABLE `attempts` (

    username VARCHAR(64) DEFAULT NULL, -- username
    ip_address VARCHAR(16) NOT NULL, -- IP address
    attempt_type VARCHAR(32) NOT NULL, -- userAuth, userRegister, userToken, passwordRecovery
    device_type VARCHAR(16) DEFAULT NULL, -- Android, iOS
    device_number VARCHAR(64) DEFAULT NULL, -- Serial number of device
    user_agent VARCHAR(64) DEFAULT NULL, -- Browser type
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    KEY idx_username (username),
    KEY idx_ip_address (ip_address),
    KEY idx_attempt_type (attempt_type),
    KEY idx_device_number (device_number),
    KEY idx_created_at (created_at)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- key_privileges (access right non user to user data or promo codes actions)

DROP TABLE IF EXISTS `key_privileges`;

CREATE TABLE `key_privileges` (

    user_id BINARY(16) NOT NULL, -- key provider
    access_key VARCHAR(16) UNIQUE NOT NULL, -- 'XLC196FF=='
    access_type TINYINT NOT NULL DEFAULT 0, -- 0 undefined 1 sensor access 2 route access 3 group acceess X subscription discount (future version)
    object_id BINARY(16) NOT NULL,
    -- object_name VARCHAR(32) NOT NULL,
    status TINYINT NOT NULL DEFAULT 0, -- 0 undefined 1 accessible -1 inaccessible
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    available_count INTEGER NOT NULL DEFAULT 1, -- how much times key can be using
    used_count INTEGER NOT NULL DEFAULT 0, -- how much times key used
    expired_time TIMESTAMP DEFAULT NULL, -- time in minutes
    -- discount INTEGER DEFAULT NULL, -- discount in percent
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    KEY idx_access_key (access_key),
    KEY idx_name (name),
    KEY idx_status (status),
    KEY idx_created_at (created_at)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [log] ------------

-- audit ( structured log )

-- ------------ [functions] ------------

/*
 * IF NOT EXISTS
 * DEFINER='admin'@'localhost'
 * SQL SECURITY INVOKER (execute under executor grants)
 */

-- get_picture_link

DROP FUNCTION IF EXISTS `get_picture_link`;

DELIMITER $$

CREATE FUNCTION `get_picture_link`( -- fileStorage=LocalFileSystem; fileName=users_2_rXneEvVqHZgHIpJKWpXMMbAdBoyjleBD
    p_picture TINYTEXT,
    p_object_name TINYTEXT
) RETURNS TINYTEXT
BEGIN
    DECLARE v_picture_link TINYTEXT DEFAULT NULL;
    DECLARE v_file_storage TINYTEXT;
    DECLARE v_file_name TINYTEXT;
    DECLARE v_length INTEGER;
    DECLARE v_position INTEGER;

    IF p_picture != '' THEN
    
        SET v_file_storage = TRIM(SUBSTRING_INDEX(p_picture, ';', 1));
       
        SET v_length = LENGTH(v_file_storage);
       
        IF v_length > 0 THEN
        
            SET v_file_name = TRIM(SUBSTRING(p_picture, v_length + 1));
           
            SET v_position = INSTR(v_file_storage, '=');
           
            SET v_file_storage = TRIM(SUBSTRING(v_file_storage, v_position + 1));

        ELSE
        
            SET v_file_name = p_picture;
        	
        END IF;
        
        IF v_file_storage = 'LocalFileSystem' THEN
        
            SET v_position = INSTR(v_file_name, '=');
        
            SET v_file_name = TRIM(SUBSTRING(v_file_name, v_position + 1));
           
            SET v_picture_link = CONCAT('https://3bit.app/b/service/pic/', p_object_name, '/', v_file_name);
        
        ELSEIF v_file_storage = 'GoogleDrive' THEN
        
            SET v_position = INSTR(v_file_name, '=');
        
            SET v_file_name = TRIM(SUBSTRING(v_file_name, v_position + 1));
        
            SET v_picture_link = CONCAT('https://drive.google.com/', p_object_name, '/', v_file_name);
        	
        END IF;
    	
    END IF;

    RETURN v_picture_link;  
END $$

DELIMITER ;

-- get_encoded_id

DROP FUNCTION IF EXISTS `get_encoded_id`;

DELIMITER $$

CREATE FUNCTION `get_encoded_id`(
  p_object_name TINYTEXT,
  p_object_id TINYTEXT
) RETURNS TINYTEXT
BEGIN
  DECLARE v_encoded_id TINYTEXT;
  DECLARE v_encode_password VARCHAR(8) DEFAULT 'F7xWfqFr';

  SELECT TO_BASE64(ENCODE(CONCAT(p_object_name, '_', p_object_id), v_encode_password)) INTO v_encoded_id;
   
  RETURN v_encoded_id;
END $$

DELIMITER ;

-- get_decoded_id

DROP FUNCTION IF EXISTS `get_decoded_id`;

DELIMITER $$

CREATE FUNCTION `get_decoded_id`(
  p_encoded_id TINYTEXT
) RETURNS TINYTEXT
BEGIN
  DECLARE v_decoded TINYTEXT;
  DECLARE v_decoded_id TINYTEXT;
  DECLARE v_decode_password VARCHAR(8) DEFAULT 'F7xWfqFr';

  SELECT DECODE(FROM_BASE64(p_encoded_id), v_decode_password) INTO v_decoded;
 
  SELECT SUBSTRING_INDEX(v_decoded, '_', -1) INTO v_decoded_id;
   
  RETURN v_decoded_id;
END $$

DELIMITER ;

/*
 * Implemetation for BIN_TO_UUID and UUID_TO_BIN for MySQL 5 or MariaDB with the swap_flag parameter
 * 
 * https://stackoverflow.com/questions/28251144/inserting-and-selecting-uuids-as-binary16
 * https://dev.mysql.com/doc/refman/8.0/en/miscellaneous-functions.html#function_uuid-to-bin
 */

-- Binary to UUID

DROP FUNCTION IF EXISTS `BIN_TO_UUID`;

DELIMITER $$

CREATE FUNCTION `BIN_TO_UUID`(binary_uuid BINARY(16), swap_flag BOOLEAN)
RETURNS CHAR(36)
DETERMINISTIC
BEGIN
   DECLARE hex_str CHAR(32);
   SET hex_str = HEX(binary_uuid);
   RETURN LOWER(CONCAT(
     IF (swap_flag, SUBSTR(hex_str, 9, 8), SUBSTR(hex_str, 1, 8)), '-',
     IF (swap_flag, SUBSTR(hex_str, 5, 4), SUBSTR(hex_str, 9, 4)), '-',
     IF (swap_flag, SUBSTR(hex_str, 1, 4), SUBSTR(hex_str, 13, 4)), '-',
     SUBSTR(hex_str, 17, 4), '-',
     SUBSTR(hex_str, 21)
  ));
END $$

DELIMITER ;

-- UUID to Binary

DROP FUNCTION IF EXISTS `UUID_TO_BIN`;

DELIMITER $$

CREATE FUNCTION `UUID_TO_BIN`(string_uuid CHAR(36), swap_flag BOOLEAN)
RETURNS BINARY(16)
DETERMINISTIC
BEGIN
  RETURN UNHEX(CONCAT(
    IF (swap_flag, SUBSTRING(string_uuid, 15, 4), SUBSTRING(string_uuid, 1, 8)),
    SUBSTRING(string_uuid, 10, 4),
    IF (swap_flag, SUBSTRING(string_uuid, 1, 8), SUBSTRING(string_uuid, 15, 4)),
    SUBSTRING(string_uuid, 20, 4),
    SUBSTRING(string_uuid, 25)
  ));
END $$

DELIMITER ;


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////


/* Market schema
 * DROP SCHEMA IF EXISTS beta_orionis_market;
 * CREATE SCHEMA beta_orionis_market;
 * USE beta_orionis_market;
 */

/* Namecheap hosting
 * 
 * Database schema: Market
 *   
 * admin credentials
 * -----------------
 * database: xbitoakr_beta_orionis_market
 * username: xbitoakr_beta_orionis_market
 * password: ...
 *
 */

-- Database schema: Market

/* 
 * DROP SCHEMA IF EXISTS xbitoakr_beta_orionis_market;   
 * CREATE SCHEMA xbitoakr_beta_orionis_market CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci; 
 */

USE xbitoakr_beta_orionis_market;

-- orders

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (

    order_id BINARY(16) NOT NULL,
    status TINYINT NOT NULL DEFAULT 0, -- 0 undefined 1 accepted 10 completed
    amount DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    user_id BINARY(16) DEFAULT NULL,
    user_email VARCHAR(64) DEFAULT NULL, -- ORDER WITHOUT registration
    user_phone VARCHAR(20) DEFAULT NULL, -- ORDER WITHOUT registration
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (order_id),
    KEY idx_status (status),
    KEY idx_amount (amount)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- stores

DROP TABLE IF EXISTS `stores`;

CREATE TABLE `stores` (

  store_id BINARY(16) NOT NULL,
  user_id BINARY(16) DEFAULT NULL, -- store's owner
  name VARCHAR(128) NOT NULL DEFAULT '',
  description VARCHAR(256) NOT NULL DEFAULT '',
  picture VARCHAR(512) DEFAULT NULL,
  status TINYINT NOT NULL DEFAULT 0, -- 0 undefined 1 available -1 not available
  language_code VARCHAR(5) DEFAULT '',
  last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (store_id),
  KEY idx_user_id (user_id),
  KEY idx_name (name),
  KEY idx_description (description),
  KEY idx_status (status),
  KEY idx_language_code (language_code),
  KEY idx_fk_user_id (user_id),
  FOREIGN KEY (user_id) REFERENCES `users` (user_id) ON DELETE SET NULL (!!!)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- products

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (

    product_id BINARY(16) NOT NULL,
    product_category_id BINARY(16) DEFAULT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    code VARCHAR(32) UNIQUE NOT NULL,
    price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    -- discount_price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    slug VARCHAR(32) NOT NULL DEFAULT '',
    picture VARCHAR(512) DEFAULT NULL,
    product_virtual BOOLEAN NOT NULL DEFAULT TRUE, /* virtual product have a package */
    status TINYINT NOT NULL DEFAULT 0, /* 0 - undefined 1 - available -1 - not available */
    language_code VARCHAR(5) DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (product_id),
    KEY idx_code (code),
    KEY idx_name (name),
    KEY idx_description (description),
    KEY idx_price (price),
    KEY idx_sale_price (sale_price),
    -- KEY idx_discount_price (discount_price),
    KEY idx_slug (slug),
    KEY idx_status (status),
    KEY idx_language_code (language_code),
    KEY idx_fk_product_category_id (product_category_id),
    CONSTRAINT fk_product_category FOREIGN KEY (product_category_id) REFERENCES `product_categories` (product_category_id) ON DELETE SET NULL

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [dictionaries] ------------

-- currencies

DROP TABLE IF EXISTS `currencies`;

CREATE TABLE `currencies` (

    currency_id BINARY(16) NOT NULL,
    name VARCHAR(128) UNIQUE NOT NULL,
    code VARCHAR(3) UNIQUE NOT NULL,
    symbol VARCHAR(8) NOT NULL DEFAULT '',
    value DECIMAL(8,2) NOT NULL DEFAULT 1.00,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
    PRIMARY KEY (currency_id),
    KEY idx_name (name),
    KEY idx_code (code)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- languages (support languages for schema, language_id should be unique for all)

DROP TABLE IF EXISTS `languages`;

CREATE TABLE `languages` (

    language_id BINARY(16) NOT NULL,
    name VARCHAR(128) UNIQUE NOT NULL,
    code VARCHAR(5) UNIQUE NOT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
    PRIMARY KEY (language_id),
    KEY idx_name (name),
    KEY idx_code (code)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- product_categories

DROP TABLE IF EXISTS `product_categories`;

CREATE TABLE `product_categories` (

    product_category_id BINARY(16) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
    PRIMARY KEY (product_category_id),
    -- FULLTEXT KEY idx_name_description (name, description),
    KEY idx_name (name),
    KEY idx_description (description)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [union helpers] ------------

-- store_products

DROP TABLE IF EXISTS `store_products`;

CREATE TABLE `store_products` (

    store_id BINARY(16) NOT NULL,
    product_id BINARY(16) NOT NULL,
  
    PRIMARY KEY (store_id, product_id),
    KEY idx_store_id (store_id)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [settings / options] ------------

-- object_options ( table objects options, object_name: order, store, product )

DROP TABLE IF EXISTS `object_options`;

CREATE TABLE `object_options` (

    object_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    value VARCHAR(512) NOT NULL DEFAULT '',
    option_type VARCHAR(16) NOT NULL DEFAULT 'string', -- option_type: string, number, boolean, array
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (object_id, object_name, name),
    KEY idx_name (name)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- object_translations ( table objects translations, object_name: store, product )

DROP TABLE IF EXISTS `object_translations`;

CREATE TABLE `object_translations` (

    object_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    value VARCHAR(512) NOT NULL DEFAULT '',
    language_code VARCHAR(5) DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (object_id, object_name, name),
    KEY idx_name (name),
    KEY idx_language_code (language_code)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- role_privileges ( access right role for privileges to table objects )

DROP TABLE IF EXISTS `role_privileges`;

CREATE TABLE `role_privileges` (

    role_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    can_select BOOLEAN NOT NULL DEFAULT FALSE,
    can_insert BOOLEAN NOT NULL DEFAULT FALSE,
    can_update BOOLEAN NOT NULL DEFAULT FALSE,
    can_delete BOOLEAN NOT NULL DEFAULT FALSE,
    
    PRIMARY KEY (role_id, object_name),
    KEY idx_object_name (object_name),
    KEY idx_role_id (role_id)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- user_privileges ( access right user for select | insert | update | delete object_id row of object_name)

DROP TABLE IF EXISTS `user_privileges`;

CREATE TABLE `user_privileges` (

    user_id BINARY(16) NOT NULL,
    object_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    can_select BOOLEAN NOT NULL DEFAULT FALSE,
    can_insert BOOLEAN NOT NULL DEFAULT FALSE,
    can_update BOOLEAN NOT NULL DEFAULT FALSE,
    can_delete BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (user_id, object_id, object_name),
    KEY idx_object_name (object_name),
	KEY idx_user_id (user_id)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [log] ------------

-- audit ( structured log )


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////


/* Support schema
 * DROP SCHEMA IF EXISTS beta_orionis_support;
 * CREATE SCHEMA beta_orionis_support;
 * USE beta_orionis_support;
 */

/* Namecheap hosting
 * 
 * Database schema: Support
 *   
 * admin credentials
 * -----------------
 * database: xbitoakr_beta_orionis_support
 * username: xbitoakr_beta_orionis_support
 * password: ...
 *
 */

-- Database schema: Support

/* 
 * DROP SCHEMA IF EXISTS xbitoakr_beta_orionis_support;   
 * CREATE SCHEMA xbitoakr_beta_orionis_support CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci; 
 */

USE xbitoakr_beta_orionis_support;

-- events, notifications, campaigns, analytics

-- ------------ [main] ------------

-- events

DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (

    event_id BINARY(16) NOT NULL,
    user_id BINARY(16) NOT NULL,
    sensor_id BINARY(16) DEFAULT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    `data` TINYTEXT DEFAULT NULL, -- key1 = value1; key2 = value2
    status TINYINT NOT NULL DEFAULT 0, -- 0 undefined 1 completed
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (event_id),
    KEY idx_user_id (user_id),
    KEY idx_sensor_id (sensor_id),
    KEY idx_name (name)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- push tokens

DROP TABLE IF EXISTS `push_tokens`;

CREATE TABLE `push_tokens` (

    token_id BINARY(16) NOT NULL,
    user_id BINARY(16) NOT NULL,
    sensor_id BINARY(16) NOT NULL,
    token VARCHAR(767) UNIQUE DEFAULT NULL, -- 64 bytes DEFAULT, 767 max LENGTH (JWT)
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (token_id),
    KEY idx_user_id (user_id),
    KEY idx_sensor_id (sensor_id),
    KEY idx_token (token)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- notifications

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (

    notification_id BINARY(16) NOT NULL,
    user_id BINARY(16) NOT NULL,
    sensor_id BINARY(16) DEFAULT NULL,
    template_id BINARY(16) NOT NULL,
    substitutions TINYTEXT DEFAULT NULL,
    `data` TINYTEXT DEFAULT NULL, -- key1 = value1; key2 = value2 convert to fcm data message (fcm data message https://firebase.google.com/docs/cloud-messaging/concept-options#notifications_and_data_messages)
    schedule TINYTEXT DEFAULT NULL,
    status TINYINT NOT NULL DEFAULT 0, -- 0 undefined (not scheduled) 1 sent
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (notification_id),
    KEY idx_user_id (user_id),
    KEY idx_sensor_id (sensor_id),
    KEY idx_template_id (template_id)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- templates

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates` (

    template_id BINARY(16) NOT NULL,
    template_type_id BINARY(16) DEFAULT NULL, -- template_types: email, sms, push
    name VARCHAR(128) NOT NULL DEFAULT '',
    title TINYTEXT NOT NULL,
    body TEXT NOT NULL,
    language_code VARCHAR(5) DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (template_id),
    KEY idx_fk_template_type_id (template_type_id),
    KEY idx_name (name),
    KEY idx_language_code (language_code),
	CONSTRAINT fk_template_type FOREIGN KEY (template_type_id) REFERENCES `template_types` (template_type_id) ON DELETE SET NULL

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*

-- analytics

DROP TABLE IF EXISTS `analytics`;

CREATE TABLE `analytics` ( -- events analytics

    analytic_id BINARY(16) NOT NULL,
    user_id BINARY(16) NOT NULL,
    username VARCHAR(64) NOT NULL,
    
    PRIMARY KEY (analytic_id, user_id),
    KEY idx_username (username)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

*/


-- ------------ [dictionaries] ------------

-- languages (support languages for schema, language_id should be unique for all)

DROP TABLE IF EXISTS `languages`;

CREATE TABLE `languages` (

    language_id BINARY(16) NOT NULL,
    name VARCHAR(128) UNIQUE NOT NULL,
    code VARCHAR(5) UNIQUE NOT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
    PRIMARY KEY (language_id),
    KEY idx_name (name),
    KEY idx_code (code)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- template_types

DROP TABLE IF EXISTS `template_types`;

CREATE TABLE `template_types` (

    template_type_id BINARY(16) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description VARCHAR(256) NOT NULL DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
    PRIMARY KEY (template_type_id),
    -- FULLTEXT KEY idx_name_description (name, description),
    KEY idx_name (name),
    KEY idx_description (description)
  
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [settings / options] ------------

-- object_options ( table objects options, object_name: user, route  )

DROP TABLE IF EXISTS `object_options`;

CREATE TABLE `object_options` (

    object_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    value VARCHAR(512) NOT NULL DEFAULT '',
    option_type VARCHAR(16) NOT NULL DEFAULT 'string', -- option_type: string, number, boolean, array
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (object_id, object_name, name),
    KEY idx_name (name)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- object_translations ( table objects translations, object_name: route, route_types )

DROP TABLE IF EXISTS `object_translations`;

CREATE TABLE `object_translations` (

    object_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL DEFAULT '',
    value VARCHAR(512) NOT NULL DEFAULT '',
    language_code VARCHAR(5) DEFAULT '',
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (object_id, object_name, name),
    KEY idx_name (name),
    KEY idx_language_code (language_code)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- roles

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (

    role_id BINARY(16) NOT NULL,
    name VARCHAR(64) NOT NULL, -- role name
    description VARCHAR(256) NOT NULL DEFAULT '',
    
    PRIMARY KEY (role_id),
    KEY idx_name (name),
    KEY idx_description (description)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- role_privileges ( access right role for privileges to table objects )

DROP TABLE IF EXISTS `role_privileges`;

CREATE TABLE `role_privileges` (

    role_id BINARY(16) NOT NULL,
    object_name VARCHAR(32) NOT NULL,
    can_select BOOLEAN NOT NULL DEFAULT FALSE,
    can_insert BOOLEAN NOT NULL DEFAULT FALSE,
    can_update BOOLEAN NOT NULL DEFAULT FALSE,
    can_delete BOOLEAN NOT NULL DEFAULT FALSE,
    
    PRIMARY KEY (role_id, object_name),
    KEY idx_object_name (object_name),
    KEY idx_role_id (role_id)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------ [log ] ------------

-- audit ( structured log )


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////


SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;

