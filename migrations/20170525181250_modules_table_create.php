<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ModulesTableCreate extends AbstractMigration
{
	/**
	* Migrate Up.
	*/
	public function up()
	{
		////////////////////////////////////////////////////////////////////////////////////////
		///// MODULES TABLE ///////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////
		$this->query('
			CREATE TABLE `modules` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`name` varchar(30) COLLATE utf8_slovak_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci
		');

		$this->query( 'INSERT INTO `modules` (`name`) VALUES ("blog"), ("eshop")' );

	}


	/**
	* Migrate Down.
	*/
	public function down()
	{

	}

}