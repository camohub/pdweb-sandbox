<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CategoriesArticlesTableCreate extends AbstractMigration
{
	/**
	* Migrate Up.
	*/
	public function up()
	{

		$this->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		$this->query( 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"' );
		$this->query( 'SET time_zone = "+00:00"' );

		////////////////////////////////////////////////////////////////////////////////
		///// CATEGORIES_ARTICLES TABLE ///////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `categories_articles` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`parent_id` int(11) UNSIGNED DEFAULT NULL,
				`module_id` int(11) UNSIGNED DEFAULT NULL,
				`name` varchar(150) COLLATE utf8_slovak_ci NOT NULL,
				`url` varchar(25) COLLATE utf8_slovak_ci NOT NULL,
				`priority` smallint(5) UNSIGNED NOT NULL,
				`visible` smallint(5) UNSIGNED NOT NULL DEFAULT \'1\',
				`slug` varchar(150) COLLATE utf8_slovak_ci NOT NULL,
				`url_params` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
				`app` smallint(5) UNSIGNED NOT NULL COMMENT \'If app == 1 itme can\'\'t be deleted cause it is default part of application.\'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query('
			ALTER TABLE `categories_articles`
				ADD UNIQUE KEY `categories_articles_slug_uidx` (`slug`),
				ADD KEY `categories_articles_parent_id_idx` (`parent_id`),
				ADD KEY `categories_articles_module_id_idx` (`module_id`),
				ADD KEY `categories_articles_priority_idx` (`priority`)
		');

		$blog_module = $this->fetchRow( 'SELECT * FROM `modules` WHERE `name` = "blog"' );

		$this->table( 'categories_articles' )->insert([
			'module_id' => $blog_module['id'],
			'name' => 'Najnovšie články',
			'url' => ':Front:Articles:category',
			'parent_id' => NULL,
			'priority' => 2,
			'visible' => 1,
			'slug' => 'najnovsie-clanky',
			'url_params' => '',
			'app' => 1,
		])->saveData();

		$this->query('
			ALTER TABLE `categories_articles`
				ADD CONSTRAINT `categories_articles_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `categories_articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				ADD CONSTRAINT `categories_articles_module_id_fk` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
  		');


		$this->query( 'SET FOREIGN_KEY_CHECKS = 1' );
	}


	/**
	* Migrate Down.
	*/
	public function down()
	{

	}

}