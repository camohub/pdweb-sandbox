<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Uploads extends AbstractMigration
{
   /**
	* Migrate Up.
	*/
	public function up()
	{
		$this->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		$this->query( 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"' );
		$this->query( 'SET time_zone = "+00:00"' );

		////////////////////////////////////////////////////////////////////////////////////////
		///// UPLOADS_ARTICLES TABLE //////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////

		$this->query( '
			CREATE TABLE `uploads_articles` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  				`articles_id` int(11) UNSIGNED DEFAULT NULL,
  				`name` varchar(100) COLLATE utf8_slovak_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query( '
			ALTER TABLE `uploads_articles`
			  ADD UNIQUE KEY `uploads_articles_name_uidx` (`name`),
			  ADD KEY `uploads_articles_articles_id_idx` (`articles_id`)
		' );

		$this->query('
			ALTER TABLE `uploads_articles` 
			ADD CONSTRAINT `uploads_articles_articles_id_fk` FOREIGN KEY (`articles_id`) REFERENCES `articles` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
		');

		////////////////////////////////////////////////////////////////////////////////////////
		///// UPLOADS_PRODUCTS TABLE //////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////

		$this->query('
			CREATE TABLE `uploads_products` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  				`products_id` int(11) UNSIGNED DEFAULT NULL,
  				`name` varchar(100) COLLATE utf8_slovak_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query( '
			ALTER TABLE `uploads_products`
			  ADD KEY `uploads_products_products_id_idx` (`products_id`),
			  ADD UNIQUE KEY `uploads_products_name_uidx` (`name`)
		' );

		$this->query('
			ALTER TABLE `uploads_products` 
			ADD CONSTRAINT `uploads_products_products_id_fk` FOREIGN KEY (`products_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
		');



		$this->query( 'SET FOREIGN_KEY_CHECKS = 1' );

	}


	/**
	* Migrate Down.
	*/
	public function down()
	{
		return;
	}

}