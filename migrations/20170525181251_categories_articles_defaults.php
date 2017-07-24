<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CategoriesArticlesDefaults extends AbstractMigration
{
	/**
	* Migrate Up.
	*/
	public function up()
	{

		$this->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		$this->query( 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"' );
		$this->query( 'SET time_zone = "+00:00"' );

		////////////////////////////////////////////////////////////////////////////////////////////
		///// CATEGORIES_ARTICLES DEFAULTS ////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////
		$status_published = $this->fetchRow( 'SELECT * FROM `statuses` WHERE `title` = "published"' );

		// Is important to keep ids on this values for class constants.
		$this->table( 'categories_articles' )->insert([
			'id' => 1,
			'url' => ':Front:Articles:show',
			'parent_id' => NULL,
			'priority' => 1,
			'statuses_id' => $status_published['id'],
			'app' => 1,
		])->saveData();

		$category_id = $this->adapter->getConnection()->lastInsertId();
		$langs = $this->fetchAll( 'SELECT * FROM `langs`' );

		foreach ( $langs as $lang )
		{
			$this->table( 'categories_articles_langs' )->insert([
				'category_article_id' => $category_id,
				'title' => $lang['code'] != 'sk' ? 'Latest news': 'Najnovšie články',
				'slug' => $lang['code'] != 'sk' ? 'latest-news': 'najnovsie-clanky',
				'lang_id' => $lang['id']
			])->saveData();
		}


		$this->query( 'SET FOREIGN_KEY_CHECKS = 1' );
	}


	/**
	* Migrate Down.
	*/
	public function down()
	{

	}

}