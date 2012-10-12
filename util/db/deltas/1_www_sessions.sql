CREATE TABLE IF NOT EXISTS `www_sessions` (
  `id` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `expires` int(10) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

