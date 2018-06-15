

ALTER TABLE `empresas` DROP `mi_pyme`;
ALTER TABLE `tabla_impuesto_unico` CHANGE `calidad_a_rebajar` `cantidad_a_rebajar` DECIMAL(5,2) NOT NULL;



ALTER TABLE `empresas` ADD `sis` TINYINT(1) NOT NULL AFTER `base_datos`;
ALTER TABLE `empresas` ADD `caja_id` INT NOT NULL AFTER `sis`;
ALTER TABLE `empresas` ADD `mutual_id` INT NOT NULL AFTER `caja_id`;
ALTER TABLE `empresas` ADD `codigo_caja` VARCHAR(255) NULL DEFAULT NULL AFTER `caja_id`;
ALTER TABLE `empresas` ADD `codigo_mutual` VARCHAR(255) NULL DEFAULT NULL AFTER `mutual_id`;
ALTER TABLE `empresas` ADD `tasa_fija_mutual` INT NOT NULL AFTER `codigo_mutual`; 
ALTER TABLE `empresas` ADD `tasa_adicional_mutual` INT NOT NULL AFTER `tasa_fija_mutual`;
ALTER TABLE `empresas` ADD `gratificacion` TINYTEXT NOT NULL AFTER `tasa_adicional_mutual`;
ALTER TABLE `empresas` ADD `zona` DECIMAL(5,2) NOT NULL AFTER `gratificacion`;