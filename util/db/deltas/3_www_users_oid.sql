CREATE TABLE IF NOT EXISTS `www_users_oid` (
  `identity` varchar(255) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `pending` varchar(255) DEFAULT NULL,
  `pending_attrs` text,
  UNIQUE KEY `identity` (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
