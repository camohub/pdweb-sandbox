<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ProductsTableCreate extends AbstractMigration
{
	/**
	 * Migrate Up.
	 */
	public function up()
	{

		$this->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		$this->query( 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"' );
		$this->query( 'SET time_zone = "+00:00"' );

		/////////////////////////////////////////////////////////////////////////////////////////
		///// PRODUCTS_STATUSES TABLE CREATE ///////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `products_statuses` (
			  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `title` varchar(30) COLLATE utf8_slovak_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->table( 'products_statuses' )
			->insert([
				['title' => 'virtual'],
				['title' => 'published'],
				['title' => 'unpublished'],
				['title' => 'sold'],
				['title' => 'deleted'],
			])
			->save();

		/////////////////////////////////////////////////////////////////////////////////
		///// PRODUCTS TABLE ///////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `products` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`parent_id` int(11) UNSIGNED NOT NULL,
				`meta_desc` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`title` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`url_title` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`price` decimal (10,2) NOT NULL,
				`stock` int(11) UNSIGNED NOT NULL,
				`products_statuses_id` int(11) UNSIGNED NOT NULL,
				`created` datetime NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query('
			ALTER TABLE `products`
				ADD KEY `products_url_title_idx` (`url_title`),
				ADD KEY `products_price_idx` (`price`),
				ADD KEY `products_created_idx` (`created`)
		');

		/////////////////////////////////////////////////////////////////////////////////
		///// PRODUCTS_CATEGORIES_PRODUCTS TABLE ///////////////////////////////////////
		///// JOIN CATEGORIES_PRODUCTS TABLE //////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `products_categories_products` (
				`products_id` int(11) UNSIGNED NOT NULL,
				`categories_products_id` int(11) UNSIGNED NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		');

		$this->query('
			ALTER TABLE `products_categories_products`
				ADD PRIMARY KEY (`products_id`,`categories_products_id`),
				ADD KEY `products_categories_products_products_id_idx` (`products_id`),
				ADD KEY `products_categorie_products_categories_products_id_idx` (`categories_products_id`)
		');

		////////////////////////////////////////////////////////////////////////////////
		///// COMMENTS_PRODUCTS TABLE /////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `comments_products` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`products_id` int(11) UNSIGNED DEFAULT NULL,
				`acl_users_id` int(11) UNSIGNED DEFAULT NULL,
				`user_name` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`email` varchar(50) COLLATE utf8_slovak_ci NOT NULL,
				`content` longtext COLLATE utf8_slovak_ci NOT NULL,
				`created` datetime NOT NULL,
				`deleted` tinyint(1) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query('
			ALTER TABLE `comments_products`
				ADD KEY `comments_products_users_id_idx` (`acl_users_id`),
				ADD KEY `comments_products_products_id_idx` (`products_id`),
				ADD KEY `comments_products_created_idx` (`created`)
		');

		//////////////////////////////////////////////////////////////////////////////////////////
		///// FOREIGN KEYS FOR PRODUCTS, PRODUCTS_CATEGORIES, CATEGORIES, COMMENTS_PRODUCTS /////
		////////////////////////////////////////////////////////////////////////////////////////
		$this->query('
			ALTER TABLE `products` 
				ADD CONSTRAINT `products_products_statuses_id_fk` FOREIGN KEY (`products_statuses_id`) REFERENCES `products_statuses` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
		');

		$this->query('
			ALTER TABLE `products_categories_products`
				ADD CONSTRAINT `products_categories_products_categories_id_fk` FOREIGN KEY (`categories_products_id`) REFERENCES `categories_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				ADD CONSTRAINT `products_categories_products_product_id_fk` FOREIGN KEY (`products_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
		');

		$this->query('
			ALTER TABLE `comments_products`
				ADD CONSTRAINT `comments_products_products_id_fk` FOREIGN KEY (`products_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				ADD CONSTRAINT `comments_products_acl_users_id_fk` FOREIGN KEY (`acl_users_id`) REFERENCES `acl_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
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