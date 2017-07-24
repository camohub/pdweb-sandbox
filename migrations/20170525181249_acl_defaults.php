<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;


class AclDefaults extends AbstractMigration
{
	/**
	 * Migrate Up.
	 */
	public function up()
	{
		$this->query('SET FOREIGN_KEY_CHECKS=0');
		$this->query('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"');
		$this->query('SET time_zone = "+00:00"');

		/////////////////////////////////////////////////////////////////////////////////////////////////////
		///// USERS ROLES //////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////////////
		$this->table( 'roles' )->insert( [['name' =>"admin"], ['name' => "editor"], ['name' => "registered"]] )->saveData();

		$this->table( 'users' )->insert([
			'user_name' => "Čamo",
			'email' => "kontakt@pdweb.sk",
			'password' => "$2y$10$1/aTxIK.UnCYg9EO5EvPiOyLVxoLe8Vuw91PS1LgIPlEIk3i4mNjq",
			'active' => 1,
			'created' => date( "Y-m-d H:i:s" ),
			'confirmation_code' => NULL,
			'social_network_params' => NULL,
			'resource' => "App",
		])->saveData();

		$user_id = $this->adapter->getConnection()->lastInsertId();
		$admin_id = $this->fetchRow( 'SELECT * FROM `roles` WHERE `name` = "admin"' )['id'];

		$this->table( 'users_roles' )->insert( ['user_id' => $user_id, 'role_id' => $admin_id] )->saveData();


		$this->query( 'SET FOREIGN_KEY_CHECKS=1' );

	}


	/**
	 * Migrate Down.
	 */
	public function down()
	{

	}

}