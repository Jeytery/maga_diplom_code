-- Version 1.0.1 (Binary Id)
USE xbitoakr_beta_orionis_service;


SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'TRADITIONAL';

-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////

-- Delete all records

DELETE FROM `roles`;
DELETE FROM `role_privileges`;
DELETE FROM `user_privileges`;
DELETE FROM `object_options`;
DELETE FROM `object_translations`;
DELETE FROM `users`;
DELETE FROM `sensors`;
DELETE FROM `settings`;
DELETE FROM `route_parts`;
DELETE FROM `routes`;
DELETE FROM `route_types`;
DELETE FROM `track_parts`;
DELETE FROM `tracks`;
DELETE FROM `track_types`;
DELETE FROM `group_users`;
DELETE FROM `groups`;

-- roles

INSERT INTO `roles` ( role_id, name, description )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'admin', 'Admin access to service' ), 
       ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'customer', 'Customer access to service' ), 
       ( UUID_TO_BIN('823eff80-0b90-11ef-a8a3-1633d0f9fc3b', true), 'anonymous', 'Anonymous access to service' );

-- role_privileges

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'role_privileges', true, true, true, true );      
      
INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'users', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'groups', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'sensors', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'routes', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'tracks', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'settings', true, true, true, true );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'users', true, false, false, false );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'settings', true, false, false, false );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'groups', true, true, true, true );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'sensors', true, true, true, true );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'routes', true, true, true, true );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'tracks', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823eff80-0b90-11ef-a8a3-1633d0f9fc3b', true), 'users', true, false, false, false );

-- users

INSERT INTO `users` ( user_id, role_id, first_name, last_name, email, phone, username, password, status )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'admin', '', 'admin@beta.orionis', '', 'admin', UNHEX(SHA1(CONCAT('Vmd693GhbnL6', 'orionis.admin'))), 1 );

INSERT INTO `users` ( user_id, role_id, first_name, last_name, email, phone, username, password, status )
VALUES ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'customer', '', 'customer@beta.orionis', '', 'customer', UNHEX(SHA1(CONCAT('Vmd693GhbnL6', 'orionis.customer'))), 1 );

INSERT INTO `users` ( user_id, role_id, first_name, last_name, email, phone, username, password, status )
VALUES ( UUID_TO_BIN('5d7ede30-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('823eff80-0b90-11ef-a8a3-1633d0f9fc3b', true), 'anonymous', 'guest', 'anonymous@beta.orionis', '', UNHEX(SHA1(CONCAT('Vmd693GhbnL6', 'anonymous'))), '', 1 );

-- user_privileges

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', true, true, true, true );

INSERT INTO `user_privileges` (user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', true, true, true, false );

INSERT INTO `user_privileges` (user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7ede30-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7ede30-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', true, true, true, false );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'user_privileges', true, true, true, true ); -- 0 Access to any records

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'users', true, true, true, true ); -- 0 Access to any records

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'sensors', true, true, true, true ); -- 0 Access to any records

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'tracks', true, true, true, true ); -- 0 Access to any records

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'routes', true, true, true, true ); -- 0 Access to any records

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'groups', true, true, true, true ); -- 0 Access to any records


-- object_options

INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', 'emailVerified', 'true', 'boolean' ), 
       ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', 'phoneVerified', 'false', 'boolean' ), 
       ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', 'bannedDays', '1', 'number' );

INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
VALUES ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', 'emailVerified', 'true', 'boolean' ), 
       ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', 'phoneVerified', 'false', 'boolean' ), 
       ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), 'users', 'bannedDays', '1', 'number' );

/* settings: accessTokenExpiredTime = 900 | number , timezoneOffset = +00:00 | string */

INSERT INTO `settings` ( name, value, `type` )
VALUES ( 'accessTokenExpiredTime', '900', 'number' ), ( 'timezoneOffset', '0', 'number' ), ( 'dstOffset', '0', 'number' ),
( 'googleMapsKey', '', 'string' ),( 'googleMapsStyles', 'google_map_uber_style.json', 'string' ),
( 'hereAppId', '', 'string' ),( 'hereAppCode', '', 'string' ),
( 'mapboxAccessToken', '', 'string' ),( 'mapquestKey', '', 'string' ),
( 'dateFormat', '', 'string' ),( 'timeFormat', '', 'string' ),
( 'userRegisterAttemptsByHours', '24', 'number' ), ( 'userAuthAttemptsByHours', '1', 'number' ),
( 'userTokenAttemptsByHours', '24', 'number' ), ( 'userPasswordRecoveryAttemptsByHours', '24', 'number' ),
( 'userRegisterAttempts', '3', 'number' ), ( 'userAuthAttempts', '3', 'number' ),
( 'userTokenAttempts', '1', 'number' ), ( 'userPasswordRecoveryAttempts', '1', 'number' ),
( 'userCanDeleteProfile', 'false', 'boolean' ),
( 'iosAppVersion', '1.0.0', 'string' ),( 'androidAppVersion', '1.0.0', 'string' ),
( 'iosAppUpdate', 'https://apps.apple.com/', 'string' ),( 'androidAppUpdate', 'https://play.google.com/', 'string' );


-- data --

INSERT INTO `route_types` ( route_type_id, name, description )
VALUES ( UUID_TO_BIN('386c0e60-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'Trekking', 'Trekking route around the area' ), 
       ( UUID_TO_BIN('386c1124-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'Сycling', 'Сycling route around the area' ),
       ( UUID_TO_BIN('386c12a0-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'Cargo transportation', 'Cargo transportation by area' );

INSERT INTO `routes` ( route_id, route_type_id, user_id, group_id, name, description, tags ) 
VALUES ( UUID_TO_BIN('4528be41-0bb1-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('386c0e60-0bb0-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), null, 'Treking in Nepal', 'To the Everest Base Camp','Nepal Everest' ),
       ( UUID_TO_BIN('4528c0aa-0bb1-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('386c0e60-0bb0-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), null, 'Treking in Pakistan', 'To the K-2 Base Camp', 'Pakistan K-2' ),
       ( UUID_TO_BIN('4528c1a9-0bb1-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('386c1124-0bb0-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), null, 'Сycling in Italy', 'Cycling by route near Rome', 'Italy Rome' ),
       ( UUID_TO_BIN('4528c28c-0bb1-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('386c1124-0bb0-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), null, 'Сycling in France', 'Cycling by route near Nice and Monaco', 'France Nice Monaco' ),
       ( UUID_TO_BIN('4528c364-0bb1-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('386c12a0-0bb0-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7ede30-0b92-11ef-a8a3-1633d0f9fc3b', true), null, 'Cargo transportation to Germany', 'Cargo transportation from anywhere from EU to Germany', 'EU Germany' );
      
INSERT INTO `track_types` ( track_type_id, name, description )
VALUES ( UUID_TO_BIN('df29f5b9-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'Route point', 'Point of route when trekking' ), 
       ( UUID_TO_BIN('df29f811-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'Location point', 'Point of location on area' );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('4528be41-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'routes', true, true, true, true );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('4528c0aa-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'routes', true, true, true, true );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('4528c1a9-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'routes', true, false, false, false );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('4528c1a9-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'routes', true, true, true, true );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('4528c28c-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'routes', true, true, true, true );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('4528c364-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'routes', true, true, true, true );

-- groups

INSERT INTO `groups` ( group_id, user_id, name, description, tags )
VALUES ( UUID_TO_BIN('c2094f28-0c5e-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 'Administrative group', 'System administrative group', 'Admin System' );

INSERT INTO group_users ( group_id, user_id )
VALUES ( UUID_TO_BIN('c2094f28-0c5e-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true) ),
       ( UUID_TO_BIN('c2094f28-0c5e-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true) ), 
       ( UUID_TO_BIN('c2094f28-0c5e-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7ede30-0b92-11ef-a8a3-1633d0f9fc3b', true) );

-- 28.0022826,86.8425658 Everest Base Camp Trek
INSERT INTO route_parts ( route_part_id, route_id, track_type_id, name, description, tags, latitude, longitude )
VALUES ( UUID_TO_BIN('1a5be942-0c60-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('4528be41-0bb1-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('df29f5b9-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'Base Point Track', 'Everest Base Camp Trek', 'Base Camp Everest', 28.0022826, 86.8425658 );

-- Tracks
INSERT INTO `sensors` ( sensor_id, user_id, name, serial_number, device_name )
VALUES ( UUID_TO_BIN('22f7ad2a-0c60-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 'Admin sensor', '1234567890', 'Sensor device' );

INSERT INTO tracks ( track_id, sensor_id, latitude, longitude, time, altitude, accuracy, bearing, speed, satellites, battery, timezone_offset )
VALUES ( UUID_TO_BIN('41bf375c-0c60-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('22f7ad2a-0c60-11ef-a8a3-1633d0f9fc3b', true), 28.0022826, 86.8425658, 1706092801, 5300, 2.0, 90, 1.0, 7, 75, 0 ),
       ( UUID_TO_BIN('41bf39eb-0c60-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('22f7ad2a-0c60-11ef-a8a3-1633d0f9fc3b', true), 28.0022826, 86.8425660, 1706092802, 5301, 2.2, 91, 1.5, 8, 73, 0 );

INSERT INTO track_parts ( track_part_id, track_id, track_type_id, name, description, tags )
VALUES ( UUID_TO_BIN('2e65fca8-0c60-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('41bf375c-0c60-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('df29f5b9-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'Base Point Track', 'Everest Base Camp Trek', 'Base Camp Everest' );

-- user_privileges

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'tracks', true, true, true, true );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'sensors', true, true, true, true );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('22f7ad2a-0c60-11ef-a8a3-1633d0f9fc3b', true), 'sensors', true, true, true, true );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), 0x0, 'sensors', true, true, true, true );

INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('5d7edd35-0b92-11ef-a8a3-1633d0f9fc3b', true), UUID_TO_BIN('22f7ad2a-0c60-11ef-a8a3-1633d0f9fc3b', true), 'sensors', true, true, true, true );

-- object_translations

INSERT INTO `object_translations` ( object_id, object_name, name, value, language_code )
VALUES ( UUID_TO_BIN('386c0e60-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'route_types', 'name', 'Треккинг', 'ru' ), 
       ( UUID_TO_BIN('386c0e60-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'route_types', 'description', 'Маршрут треккинга по окрестностям', 'ru' ),
       ( UUID_TO_BIN('386c1124-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'route_types', 'name', 'Велопрогулка', 'ru' ), 
       ( UUID_TO_BIN('386c1124-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'route_types', 'description', 'Маршрут велопрогулки по окрестностям', 'ru' ),
       ( UUID_TO_BIN('386c12a0-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'route_types', 'name', 'Грузоперевозка', 'ru' ), 
       ( UUID_TO_BIN('386c12a0-0bb0-11ef-a8a3-1633d0f9fc3b', true), 'route_types', 'description', 'Грузоперевозки по регионам', 'ru' );

INSERT INTO `object_translations` ( object_id, object_name, name, value, language_code )
VALUES ( UUID_TO_BIN('df29f5b9-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'track_types', 'name', 'Точка маршрута', 'ru' ), 
       ( UUID_TO_BIN('df29f5b9-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'track_types', 'description', 'Точка маршрута во время треккинга', 'ru' ),
       ( UUID_TO_BIN('df29f811-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'track_types', 'name', 'Точка местоположения', 'ru' ), 
       ( UUID_TO_BIN('df29f811-0bb1-11ef-a8a3-1633d0f9fc3b', true), 'track_types', 'description', 'Точка местоположения на местности', 'ru' );


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////


SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;


/*
USE xbitoakr_beta_orionis_market;

DELETE FROM `orders`;
DELETE FROM `products`;

-- orders

INSERT INTO `orders` ( order_id, status, amount, user_email, user_phone )
VALUES ( 1, 1, 0.50, 'customer@beta.orionis', '' );

-- products

INSERT INTO `products` ( product_id, product_category_id, name, description, code, price, sale_price, slug, status, language_code )
VALUES ( 1, null, '1 Month subscription', 'Customer subscription for 1 Month', 'CS-1', 0.50, 0.50, 'customer_subscription_1_month', 1, 'eng' );

INSERT INTO `products` ( product_category_id, name, description, code, price, sale_price, slug, status, language_code )
VALUES ( 2, null, '6 Months subscription', 'Customer subscription for 6 Months', 'CS-2', 1.00, 1.00, 'customer_subscription_6_months', 1, 'eng' );

INSERT INTO `products` ( product_category_id, name, description, code, price, sale_price, slug, status, language_code )
VALUES ( 3, null, '1 Year subscription', 'Customer subscription for 1 Year', 'CS-3', 1.50, 1.50, 'customer_subscription_1_year', 1, 'eng' );
*/

/*
INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
VALUES ( 1, 'users', 'storeId', '1', 'number'), (1, 'users', 'storeAdmin', 'true', 'boolean' );

-- object_options

INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
VALUES (1, 'orders', 'orderProducts', '1,2', 'array'), (1, 'orders', 'userId', '1', 'number');

INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
VALUES (1, 'orders', 'orderPayments', '1', 'array'), (1, 'orders', 'orderAmount', '0.50', 'number');

-- object_options

UPDATE `object_options` SET value = '1,2' WHERE object_id = 1 AND object_name = 'orders' AND name = 'orderProducts';

INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
VALUES (1, 'orders', 'orderProductsCount', '1,1', 'array');

INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
VALUES ( 1, 'products', 'includedProducts', '', 'array' ), ( 2, 'products', 'includedProducts', '', 'array' );

INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
VALUES (1, 'products', 'storeId', '1', 'number'), (2, 'products', 'storeId', '1', 'number');
*/


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////


USE xbitoakr_beta_orionis_support;

-- roles

INSERT INTO `roles` ( role_id, name, description )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'admin', 'Admin access to service' ), 
       ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'customer', 'Customer access to service' ), 
       ( UUID_TO_BIN('823eff80-0b90-11ef-a8a3-1633d0f9fc3b', true), 'anonymous', 'Anonymous access to service' );

-- role_privileges

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'role_privileges', true, true, true, true );      
      
INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'push_tokens', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'notifications', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'template_types', true, true, true, true );

INSERT INTO `role_privileges` ( role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efc72-0b90-11ef-a8a3-1633d0f9fc3b', true), 'templates', true, true, true, true );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'push_tokens', false, true, false, false );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'notifications', true, false, false, false );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'template_types', true, false, false, false );

INSERT INTO `role_privileges` (role_id, object_name, can_select, can_insert, can_update, can_delete )
VALUES ( UUID_TO_BIN('823efe85-0b90-11ef-a8a3-1633d0f9fc3b', true), 'templates', true, false, false, false );

-- template_types

INSERT INTO `template_types` ( template_type_id, name, description )
VALUES ( UUID_TO_BIN('05d5cb04-8fe3-11ef-b65c-267d3e19e8d5', true), 'pushNotification', 'Push notification' );

INSERT INTO `template_types` ( template_type_id, name, description )
VALUES ( UUID_TO_BIN('05d5ccf5-8fe3-11ef-b65c-267d3e19e8d5', true), 'emailNotification', 'Email notification' );

INSERT INTO `template_types` ( template_type_id, name, description )
VALUES ( UUID_TO_BIN('05d5cdb2-8fe3-11ef-b65c-267d3e19e8d5', true), 'smsNotification', 'Sms notification' );

-- templates

INSERT INTO `templates` ( template_id, template_type_id, name, title, body )
VALUES ( UUID_TO_BIN('d3761ba3-8fe3-11ef-b65c-267d3e19e8d5', true), UUID_TO_BIN('05d5cb04-8fe3-11ef-b65c-267d3e19e8d5', true), 'userLogonPushNotification', 'User logon', 'User {{userEmail}} logon successfully' );

INSERT INTO `templates` ( template_id, template_type_id, name, title, body )
VALUES ( UUID_TO_BIN('d37620be-8fe3-11ef-b65c-267d3e19e8d5', true), UUID_TO_BIN('05d5cb04-8fe3-11ef-b65c-267d3e19e8d5', true), 'userRegisteredPushNotification', 'User registered', 'User {{userEmail}} regsitered successfully' );

INSERT INTO `templates` ( template_id, template_type_id, name, title, body )
VALUES ( UUID_TO_BIN('d376223a-8fe3-11ef-b65c-267d3e19e8d5', true), UUID_TO_BIN('05d5cb04-8fe3-11ef-b65c-267d3e19e8d5', true), 'passwordRecoveryPushNotification', 'Password recovery', 'Password recivery instruction with pin code was sent to {{userEmail}}' );

INSERT INTO `templates` ( template_id, template_type_id, name, title, body )
VALUES ( UUID_TO_BIN('d376239b-8fe3-11ef-b65c-267d3e19e8d5', true), UUID_TO_BIN('05d5cb04-8fe3-11ef-b65c-267d3e19e8d5', true), 'testPushNotification', 'Test notification', 'This is a test notification"}' );

INSERT INTO `templates` ( template_id, template_type_id, name, title, body )
VALUES ( UUID_TO_BIN('d37624e8-8fe3-11ef-b65c-267d3e19e8d5', true), UUID_TO_BIN('05d5cb04-8fe3-11ef-b65c-267d3e19e8d5', true), 'testUserPushNotification', 'Test user', 'UserId = {{userId}} Username {{username}}' );
