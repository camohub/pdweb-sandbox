<?php

namespace App\Model;

use Nette,
	App,
	Nette\Security\Permission;


/**
 * Users management.
 */
class AuthorizatorFactory
{
	/**
	 * @return \Nette\Security\IAuthorizator
	 */
	public function create()
	{
		$permission = new Permission();

		/* zoznam uživateľských rolí */
		$permission->addRole('guest');
		$permission->addRole('registered');
		$permission->addRole('redactor', 'registered');
		$permission->addRole('admin', 'redactor');

		/* seznam zdrojů */
		$permission->addResource('comment');
		$permission->addResource('article');
		$permission->addResource('administration');
		$permission->addResource('user');
		$permission->addResource('menu');
		$permission->addResource('image');
		$permission->addResource('poll');

		/* registered oprávnenia */
		$permission->allow('registered', array('comment'), 'add');

		/* redactor oprávnenia */
		$permission->allow('redactor', array('article', 'image', 'poll'), 'add');
		$permission->allow('redactor', array('article', 'poll'), 'edit');
		$permission->allow('redactor', array('administration'), 'view');

		/* admin oprávnenia - na všetko */
		$permission->allow('admin', Permission::ALL, Permission::ALL);

		return $permission;
	}

}
