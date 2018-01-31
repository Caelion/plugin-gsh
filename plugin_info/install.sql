CREATE TABLE IF NOT EXISTS `gsh_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enable` TINYINT(1) NULL,
  `link_type` varchar(127),
  `link_id` int(11) NOT NULL,
  `type` varchar(255),
  `options` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique` (`link_type` ASC, `link_id` ASC),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;