<?php

namespace App\Forms;

use App;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignInFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;


	public function __construct( FormFactory $factory, User $user )
	{
		$this->factory = $factory;
		$this->user = $user;
	}


	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Nette\Application\UI\Form;

		$form->addProtection( 'Vypršal čas vyhradený pre odoslanie formulára. Z dôvodu rizika útoku CSRF bola požiadavka na server zamietnutá.' );

		$form->addText( 'user_name', 'Username:' )
			->setRequired( 'Please enter your username.' )
			->setAttribute( 'class', 'formEl' );

		$form->addPassword( 'password', 'Password:' )
			->setRequired( 'Please enter your password.' )
			->setAttribute( 'class', 'formEl' );

		$form->addCheckbox( 'remember', ' Keep me signed in' );

		$form->addSubmit( 'send', 'Prihlásiť' )
			->setAttribute( 'class', 'formElB' );

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array( $this, 'signInFormSucceeded' );
		return $form;
	}


	public function signInFormSucceeded( Form $form, $values )
	{
		if ( $values->remember )
		{
			$this->user->setExpiration( '14 days', FALSE );
		}
		else
		{
			$this->user->setExpiration( 0, TRUE );
		}

		$presenter = $form->getPresenter();

		try
		{
			$this->user->login( $values->user_name, $values->password );
		}
		catch ( Nette\Security\AuthenticationException $e )
		{
			$form->addError( $e->getMessage() );
			return;
		}
		catch ( App\Exceptions\AccessDeniedException $e )
		{
			$presenter->flashMessage( 'Váš účet ešte nebol aktivovaný emailom, alebo je zablokovaný.' );
			return;
		}

		$presenter->flashMessage( 'Vitajte ' . $values['user_name'] );

		if ( $url = $this->getReferer( 'signInReferrer' ) )
		{
			$presenter->redirectUrl( $url );
		}

		$presenter->redirect( ':Front:Default:default' );

	}

}
