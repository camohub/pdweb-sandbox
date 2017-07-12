<?php

namespace App\Model\Repositories;


use Nette;


class AclUsersRepository extends Repository
{

	const TBL_NAME = 'acl_users';
	const COL_ID = 'id';
	const COL_NAME = 'user_name';
	const COL_PASSWORD = 'password';
	const COL_ROLE = 'role';
	const COL_EMAIL = 'email';
	const COL_ACTIVE = 'active';
	const COL_CONFIRMATION_CODE = 'confirmation_code';

}
