<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;


class Acl extends AbstractMigration
{
	/**
	 * Migrate Up.
	 */
	public function up()
	{
		$this->query('SET FOREIGN_KEY_CHECKS=0');
		$this->query('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"');
		$this->query('SET time_zone = "+00:00"');

		/////////////////////////////////////////////////////////////////////////////////
		///// ROLES TABLE //////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `acl_roles` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  			`name` varchar(25) COLLATE utf8_slovak_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');


		$this->query( 'INSERT INTO `acl_roles` (`name`) VALUES ("admin"), ("editor"), ("registered")' );

		/////////////////////////////////////////////////////////////////////////////////
		///// USERS TABLE //////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `acl_users` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`user_name` varchar(30) COLLATE utf8_slovak_ci NOT NULL,
				`email` varchar(50) COLLATE utf8_slovak_ci NOT NULL,
				`password` varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
				`active` smallint(6) NOT NULL,
				`created` datetime NOT NULL,
				`confirmation_code` varchar(40) COLLATE utf8_slovak_ci DEFAULT NULL,
				`social_network_params` longtext COLLATE utf8_slovak_ci,
				`resource` varchar(20) COLLATE utf8_slovak_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
		');

		$this->query('
			ALTER TABLE `acl_users`	ADD UNIQUE KEY `acl_users_user_name_email_resource_uidx` (`user_name`,`email`,`resource`)
		');

		$this->query('
			INSERT INTO `acl_users` 
			(`user_name`, `email`, `password`, `active`, `created`, `confirmation_code`, `social_network_params`, `resource`) 
			VALUES 
			("Čamo", "kontakt@pdweb.sk", "$2y$10$1/aTxIK.UnCYg9EO5EvPiOyLVxoLe8Vuw91PS1LgIPlEIk3i4mNjq", 1, "2015-01-19 19:45:45", NULL, NULL, "App")
		');

		/////////////////////////////////////////////////////////////////////////////////
		///// USERS_ROLES TABLE ////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `acl_users_roles` (
				`acl_users_id` int(11) UNSIGNED NOT NULL,
		 		`acl_roles_id` int(11) UNSIGNED NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		');

		$this->query('
			INSERT INTO `acl_users_roles` (`acl_users_id`, `acl_roles_id`) VALUES (1, 1)
		');

		$this->query('
			ALTER TABLE `acl_users_roles`
				ADD PRIMARY KEY (`acl_users_id`,`acl_roles_id`),
				ADD KEY `acl_users_roles_acl_users_id_idx` (`acl_users_id`),
				ADD KEY `acl_users_roles_acl_roles_id_idx` (`acl_roles_id`);
		');

		/////////////////////////////////////////////////////////////////////////////////
		///// FOREIGN KEYS /////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		$this->query('
			ALTER TABLE `acl_users_roles`
				ADD CONSTRAINT `acl_users_roles_acl_users_id_fk` FOREIGN KEY (`acl_users_id`) REFERENCES `acl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				ADD CONSTRAINT `acl_users_roles_acl_roles_id_fk` FOREIGN KEY (`acl_roles_id`) REFERENCES `acl_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
		');


		$this->query( 'SET FOREIGN_KEY_CHECKS=1' );

	}


	/**
	 * Migrate Down.
	 */
	public function down()
	{

	}

}