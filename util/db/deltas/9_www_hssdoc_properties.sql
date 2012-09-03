CREATE TABLE `www_hssdoc_properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
  `name` varchar(255) NOT NULL,
  `object` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY(`id`)
) COMMENT='' ENGINE='InnoDB' COLLATE 'utf8_general_ci';

