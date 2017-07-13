<?php

namespace App\FrontModule\Forms;


use App;
use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class SignFormFactory
{

	/** @var  Translator */
	protected $translator;

	/** @var  Translator */
	protected $pTranslator;

	/** @var  Nette\Security\User */
	protected $user;


	public function __construct( Translator $tr, Nette\Security\User $u )
	{
		$this->translator = $tr;
		$this->pTranslator = $this->translator->domain( 'front.forms.sign' );
		$this->user = $u;
	}


	public function create()
	{
		$form = new Nette\Application\UI\Form;

		$form->setTranslator( $this->pTranslator );

		$form->addProtection( 'csrf' );

		$form->addText( 'user_name', 'user_name.label' )
			->setRequired( 'user_name.required' )
			->setAttribute( 'class', 'form-control' );

		$form->addPassword( 'password', 'password.label' )
			->setRequired( 'password.required' )
			->setAttribute( 'class', 'form-control' );

		$form->addCheckbox( 'remember', 'remember.label' );

		$form->addSubmit( 'submit', 'submit.label' )
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
			$presenter->flashMessage( 'front.forms.sign.access-denied' );
			return;
		}

		$presenter->flashMessage( 'front.forms.sign.success' );

		if ( $url = $presenter->getReferer( 'signInReferrer' ) )
		{
			$presenter->redirectUrl( $url );
		}

		$presenter->redirect( ':Front:Default:default' );

	}

}
