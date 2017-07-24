<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class StatusesLangsAndModulesDefaults extends AbstractMigration
{
	/**
	* Migrate Up.
	*/
	public function up()
	{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		///// STATUSES LANGS MODULES DEFAULTS /////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////

		// Is important to keep ids on this values for class constants.
		$this->table( 'statuses' )->insert([
			['id' => 1, 'title' => 'draft'],
			['id' => 2, 'title' => 'published'],
			['id' => 3, 'title' => 'unpublished'],
			['id' => 4, 'title' => 'deleted']
		])->saveData();

		// Is important to keep ids on this values for class constants.
		$this->table( 'langs' )->insert( [['id' => 1, 'code' => 'sk'], ['id' => 2, 'code' => 'en']] )->saveData();

		// Is important to keep ids on this values for class constants.
		$this->table( 'modules' )->insert( [['id' => 1, 'name' => 'blog'], ['id' => 2, 'name' => 'eshop']] )->saveData();

	}


	/**
	* Migrate Down.
	*/
	public function down()
	{

	}

}