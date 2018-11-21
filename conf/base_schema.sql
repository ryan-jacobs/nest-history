CREATE TABLE `thermostat_status` (
	`id` int NOT NULL AUTO_INCREMENT,
	`thermostat_id` int NOT NULL,
  `structure_status_id` int NOT NULL,
	`temperature` FLOAT NOT NULL,
	`heat` int NOT NULL,
	`alt_heat` int NOT NULL,
	`ac` int NOT NULL,
	`fan` int NOT NULL,
	`auto_away` int NOT NULL,
	`manual_away` int NOT NULL,
	`leaf` int NOT NULL,
  `humidifier` int NOT NULL,
	`humidity` int NOT NULL,
	`mode` varchar(32) NOT NULL,
	`battery_level` FLOAT NOT NULL,
	`eco_temp_high` FLOAT NOT NULL,
	`eco_temp_low` FLOAT NOT NULL,
	`target_mode` varchar(32) NOT NULL,
	`target_temperature` FLOAT NOT NULL,
	`target_time` int NOT NULL,
  `target_humidity` FLOAT NOT NULL,
	`polled` TIMESTAMP NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `thermostats` (
	`id` int NOT NULL AUTO_INCREMENT,
	`serial` varchar(36) NOT NULL UNIQUE,
	`structure_id` int NOT NULL,
	`where` varchar(256) NOT NULL,
	`name` varchar(256) NOT NULL,
	`scale` varchar(16) NOT NULL,
	`last_polled` TIMESTAMP NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `structures` (
	`id` int NOT NULL AUTO_INCREMENT,
	`uuid` varchar(36) NOT NULL UNIQUE,
	`country` varchar(16) NOT NULL,
	`postal_code` varchar(16) NOT NULL,
	`city` varchar(256) NOT NULL,
	`name` varchar(256) NOT NULL,
	`last_polled` TIMESTAMP NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `structure_status` (
	`id` int NOT NULL AUTO_INCREMENT,
	`structure_id` int NOT NULL,
	`temperature` FLOAT NOT NULL,
	`conditions` varchar(32) NOT NULL,
	`conditions_icon` varchar(32) NOT NULL,
	`humidity` int NOT NULL,
	`wind` int NOT NULL,
	`wind_speed` int NOT NULL,
	`away` int NOT NULL,
	`polled` TIMESTAMP NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `variables` (
	`id` int NOT NULL AUTO_INCREMENT,
	`key` varchar(64) NOT NULL,
	`value` TEXT NOT NULL,
	PRIMARY KEY (`id`)
);

ALTER TABLE `thermostat_status` ADD CONSTRAINT `thermostat_status_fk0` FOREIGN KEY (`thermostat_id`) REFERENCES `thermostats`(`id`);

ALTER TABLE `thermostat_status` ADD CONSTRAINT `thermostat_status_fk1` FOREIGN KEY (`structure_status_id`) REFERENCES `structure_status`(`id`);

ALTER TABLE `thermostats` ADD CONSTRAINT `thermostats_fk0` FOREIGN KEY (`structure_id`) REFERENCES `structures`(`id`);

ALTER TABLE `structure_status` ADD CONSTRAINT `structure_status_fk0` FOREIGN KEY (`structure_id`) REFERENCES `structures`(`id`);

