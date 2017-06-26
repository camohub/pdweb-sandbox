<?php

namespace App\Model\Repositories;


use Nette;


class UsersAclRolesRepository extends Repository
{

	const TBL_NAME = 'users';
	const COL_USERS_ID = 'id';
	const COL_ACL_ROLES_ID = 'acl_roles_id';

}
