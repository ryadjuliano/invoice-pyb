ALTER TABLE `sim_notes` ADD `default_sale` TINYINT(1) NULL, ADD `default_quote` TINYINT(1) NULL;
UPDATE `sim_settings` SET `version` = '3.5.3' WHERE `setting_id` = 1;
