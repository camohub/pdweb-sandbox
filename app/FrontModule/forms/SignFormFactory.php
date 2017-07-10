<?php

namespace App\FrontModule\Forms;


use App;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class SignFormFactory
{

	/** @var  Nette\Security\User */
	protected $user;


	public function __construct( Nette\Security\User $u )
	{
		$this->user = $u;
	}


	public function create()
	{
		$form = new Nette\Application\UI\Form;

		$form->addProtection( 'Vypršal čas vyhradený pre odoslanie formulára. Z dôvodu rizika útoku CSRF bola požiadavka na server zamietnutá.' );

		$form->addText( 'user_name', 'Username:' )
			->setRequired( 'Please enter your username.' )
			->setAttribute( 'class', 'form-control' )
			->setAttribute( 'placeholder', 'Meno' );

		$form->addPassword( 'password', 'Password:' )
			->setRequired( 'Please enter your password.' )
			->setAttribute( 'class', 'form-control' )
			->setAttribute( 'placeholder', 'Heslo' );

		$form->addCheckbox( 'remember', ' Zapamätať prihlásenie' );

		$form->addSubmit( 'submit', 'Prihlásiť' )
			->setAttribute( 'class', 'btn btn-primary' );

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array( $this, 'formSucceeded' );

		return $form;
	}


	public function formSucceeded( Form $form, $values )
	{
		$presenter = $form->getPresenter();

		if ( $values->remember )
		{
			$this->user->setExpiration( '14 days', FALSE );
		}
		else
		{
			$this->user->setExpiration( 0, TRUE );
		}

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

		Debugger::barDump( $presenter );
		if ( $url = $presenter->getReferer( 'signInReferrer' ) )
		{
			$presenter->redirectUrl( $url );
		}

		$presenter->redirect( ':Front:Default:default' );

	}

}
