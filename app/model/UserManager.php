<?php


namespace App\Model;


use Nette;
use App;
use Nette\Security\Passwords;
use App\Model\Repositories\UsersRepository;
use App\Model\Repositories\UsersAclRolesRepository;
use Nette\Utils\Random;


/**
 * Users management.
 * Do not use this class to manage users from social networks like FB
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{

	/** @var Nette\Database\Context */
	private $database;

	/** @var UsersRepository */
	private $usersRepository;

	/** @var UsersAclRolesRepository */
	private $usersAclRolesRepository;


	public function __construct( Nette\Database\Context $database, UsersRepository $uR, UsersAclRolesRepository $uARR )
	{
		$this->database = $database;
		$this->usersRepository = $uR;
		$this->usersAclRolesRepository = $uARR;
	}


	/**
	 * @param array $credentials
	 * @return Nette\Security\Identity
	 * @throws App\Exceptions\AccessDeniedException
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate( array $credentials )
	{
		list( $user_name, $password ) = $credentials;

		$user_row = $this->usersRepository->findOneBy( [ UsersRepository::COL_NAME => $user_name, UsersRepository::COL_PASSWORD . ' NOT' => NULL ] );

		if ( ! $user_row )
		{
			throw new Nette\Security\AuthenticationException( 'The username is incorrect.', self::IDENTITY_NOT_FOUND );
		}
		elseif ( ! $user_row->active )
		{
			throw new App\Exceptions\AccessDeniedException;
		}
		elseif ( ! Passwords::verify( $password, $user_row->password ) )
		{
			throw new Nette\Security\AuthenticationException( 'The password is incorrect.', self::INVALID_CREDENTIAL );
		}
		elseif ( Passwords::needsRehash( $user_row->password ) )
		{
			$user_row->update( ['password' => Passwords::hash( $password )] );
		}

		$userArr = $user_row->toArray();
		unset( $userArr[UsersRepository::COL_PASSWORD] );

		$rolesArr = array();
		foreach( $user_row->related('users_acl_roles', 'users_id') as $role )
		{
			$rolesArr[] = $role->ref('acl_roles', 'acl_roles_id')->name;
		}

		return new Nette\Security\Identity( $user_row->id, $rolesArr, $userArr );
	}


	/**
	 * @desc Do not use it for users from social networks. They have its own manager classes.
	 * @param $params
	 * @param bool $admin
	 * @return Nette\Database\IRow
	 * @throws App\Exceptions\DuplicateEntryException
	 * @throws \Exception
	 */
	public function add( $params, $admin = FALSE )
	{
		// Do not use transacion here. It is used in RegisterPresenter

		// If $admin is false is possible create only role "registered".
		if ( ! $admin || ! isset( $params['roles'] ) )
		{
			$params['roles'] = [ 'registered' ];
		}

		$params['roles'] = $this->aclRolesRepository->findBy( [ 'name' => $params['roles'] ] );

		$params[UsersRepository::COL_PASSWORD] = Passwords::hash( $params['password'] );
		$params['resource'] = 'App';

		// Do not use transacion here. It is used in RegisterPresenter
		$params['password'] = Passwords::hash($params['password']);
		$code = Random::generate( 10,'0-9a-zA-Z' );
		try
		{
			$row = $this->usersRepository->add([
				UsersRepository::COL_NAME => $params['user_name'],
				UsersRepository::COL_PASSWORD => $params['password'],
				UsersRepository::COL_EMAIL => $params['email'],
				UsersRepository::COL_ACTIVE => 0,
				UsersRepository::COL_CONFIRMATION_CODE => $code,
			]);
		}
		catch(\PDOException $e)
		{
			// This catch ONLY checks duplicate entry to fields with UNIQUE KEY
			$info = $e->errorInfo;
			// mysql==1062  sqlite==19  postgresql==23505
			if ( $info[0] == 23000 && $info[1] == 1062 )
			{
				// if/elseif returns the name of problematic field and value
				if( $this->usersRepository->findOneBy( ['user_name = ?', $params['user_name']] ) )
				{
					$msg = 'user_name';	$code = 1;
				}
				elseif( $this->usersRepository->findOneBy( ['email = ?', $params['email']] ) )
				{
					$msg = 'email';	$code = 2;
				}
				throw new App\Exceptions\DuplicateEntryException( $msg, $code );
			}
			else { throw $e; }
		}

		$this->usersAclRolesRepository->add( [UsersAclRolesRepository::COL_USERS_ID => $row->id, UsersAclRolesRepository::COL_ACL_ROLES_ID => 3] );

		return $row;

	}
}
