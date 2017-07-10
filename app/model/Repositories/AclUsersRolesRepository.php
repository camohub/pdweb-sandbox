<?php

namespace App\Model\Repositories;


use Nette;


class AclUsersRolesRepository extends Repository
{

	const TBL_NAME = 'acl_users_roles';
	const COL_USERS_ID = 'user_id';
	const COL_ACL_ROLES_ID = 'role_id';

}
