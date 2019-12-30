<?php

defined('_CARDANO') or die;

class DB extends PDO {

	public function __construct($host, $db, $user, $pass) {
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		);
		parent::__construct('mysql:host='.$host.';dbname='.$db.';charset=utf8mb4', $user, $pass, $opt);
		parent::exec('CREATE TABLE IF NOT EXISTS `rewards` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` bigint(20) NOT NULL,
		  `chat_id` bigint(20) NOT NULL,
		  `addr` varchar(255) NOT NULL,
		  `total_reward` bigint(20) UNSIGNED NOT NULL,
		  `last_reward` bigint(20) UNSIGNED NOT NULL,
		  `publish` tinyint(1) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `user_id_chat_id_addr` (`user_id`,`chat_id`,`addr`) USING BTREE,
		  KEY `chat_id` (`chat_id`),
		  KEY `user_id` (`user_id`),
		  KEY `addr` (`addr`),
		  KEY `publish` (`publish`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
		parent::exec('CREATE TABLE IF NOT EXISTS `users` (
		  `user_id` bigint(20) NOT NULL,
		  `username` varchar(255) NOT NULL,
		  `first_name` varchar(255) NOT NULL,
		  `last_name` varchar(255) NOT NULL,
		  `create_time` timestamp NOT NULL DEFAULT current_timestamp(),
		  `update_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
		  `lang` char(2) NOT NULL,
		  PRIMARY KEY (`user_id`) USING BTREE,
		  KEY `username` (`username`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
	}

}