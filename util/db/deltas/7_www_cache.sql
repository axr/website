CREATE TABLE IF NOT EXISTS `www_cache` (
  `key` varchar(255) NOT NULL,
  `data` mediumtext NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

