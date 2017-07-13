<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ArticlesTableCreate extends AbstractMigration
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
		///// ARTICLES_STATUSES TABLE CREATE //////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `articles_statuses` (
			  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `title` varchar(30) COLLATE utf8_slovak_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->table( 'articles_statuses' )
			->insert([
				['title' => 'virtual'],
				['title' => 'published'],
				['title' => 'unpublished'],
				['title' => 'deleted'],
			])
			->save();

		/////////////////////////////////////////////////////////////////////////////////
		///// ARTICLES TABLE ///////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `articles` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`acl_users_id` int(11) UNSIGNED DEFAULT NULL,
				`articles_statuses_id` int(11) UNSIGNED NOT NULL,
				`created` datetime NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query('
			ALTER TABLE `articles`
				ADD KEY `articles_created_idx` (`created`),
				ADD KEY `articles_acl_users_id_idx` (`acl_users_id`),
				ADD KEY `articles_articles_statuses_id_idx` (`articles_statuses_id`)
		');

		/////////////////////////////////////////////////////////////////////////////////
		///// ARTICLES_LANGS TABLE /////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `articles_langs` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`articles_id` int(11) UNSIGNED NOT NULL,
				`langs_code` varchar(10) NULL, 
				`meta_desc` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`title` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`slug` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`perex` longtext COLLATE utf8_slovak_ci NOT NULL,
				`content` longtext COLLATE utf8_slovak_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query('
			ALTER TABLE `articles_langs` 
				ADD KEY `articles_langs_slug_idx` (`slug`),
				ADD KEY `articles_langs_langs_code_idx` (`langs_code`),
				ADD KEY `articles_langs_articles_id_idx` (`articles_id`)
		');

		/////////////////////////////////////////////////////////////////////////////////
		///// ARTICLES_CATEGORIES_ARTICLES TABLE ///////////////////////////////////////
		///// JOIN CATEGORIES_ARTICLES TABLE //////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `articles_categories_articles` (
				`articles_id` int(11) UNSIGNED NOT NULL,
				`categories_articles_id` int(11) UNSIGNED NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		');

		$this->query('
			ALTER TABLE `articles_categories_articles`
				ADD PRIMARY KEY (`articles_id`,`categories_articles_id`),
				ADD KEY `articles_categories_articles_articles_id_idx` (`articles_id`),
				ADD KEY `articles_categories_articles_categories_articles_id_idx` (`categories_articles_id`)
		');

		////////////////////////////////////////////////////////////////////////////////
		///// COMMENTS_ARTICLES TABLE /////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `comments_articles` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`articles_id` int(11) UNSIGNED DEFAULT NULL,
				`acl_users_id` int(11) UNSIGNED DEFAULT NULL,
				`user_name` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`email` varchar(50) COLLATE utf8_slovak_ci NOT NULL,
				`content` longtext COLLATE utf8_slovak_ci NOT NULL,
				`created` datetime NOT NULL,
				`status` int(11) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query('
			ALTER TABLE `comments_articles`
				ADD KEY `comments_articles_users_id_idx` (`acl_users_id`),
				ADD KEY `comments_articles_articles_id_idx` (`articles_id`),
				ADD KEY `comments_articles_created_idx` (`created`);
		');

		//////////////////////////////////////////////////////////////////////////////////////////
		///// FOREIGN KEYS FOR ARTICLES, ARTICLES_CATEGORIES_ARTICLES, COMMENTS_ARTICLES ////////
		////////////////////////////////////////////////////////////////////////////////////////
		$this->query('
			ALTER TABLE `articles` 
				ADD CONSTRAINT `articles_acl_users_id_fk` FOREIGN KEY (`acl_users_id`) REFERENCES `acl_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
		');

		$this->query('
			ALTER TABLE `articles` 
				ADD CONSTRAINT `articles_articles_statuses_id_fk` FOREIGN KEY (`articles_statuses_id`) REFERENCES `articles_statuses` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
		');

		$this->query('
			ALTER TABLE `articles_langs`
				ADD CONSTRAINT `articles_langs_articles_id_fk` FOREIGN KEY (`articles_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
		');

		$this->query('
			ALTER TABLE `articles_langs`
				ADD CONSTRAINT `articles_langs_langs_code_fk` FOREIGN KEY (`langs_code`) REFERENCES `langs` (`code`) ON DELETE SET NULL ON UPDATE CASCADE
		');

		$this->query('
			ALTER TABLE `articles_categories_articles`
				ADD CONSTRAINT `articles_categories_articles_categories_id_fk` FOREIGN KEY (`categories_articles_id`) REFERENCES `categories_articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				ADD CONSTRAINT `articles_categories_articles_articles_id_fk` FOREIGN KEY (`articles_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
		');

		$this->query('
			ALTER TABLE `comments_articles`
				ADD CONSTRAINT `comments_articles_articles_id_fk` FOREIGN KEY (`articles_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				ADD CONSTRAINT `comments_articles_acl_users_id_fk` FOREIGN KEY (`acl_users_id`) REFERENCES `acl_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
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