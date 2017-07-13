<?php


namespace App\FrontModule\Presenters;


use Nette;
use App;
use App\FrontModule\Forms\SignFormFactory;


class SignPresenter extends BasePresenter
{

	/** @var SignFormFactory @inject */
	public $signFormFactory;


	public function renderIn()
	{
		// Sign:in Facebook login makes redirect to itself(It means this action).
		// But user is already logged in.
		if ( $this->user->isLoggedIn() )
		{
			$this->redirect( ':Front:Default:default' );
		}

		$this->setReferer( 'signInReferrer' );
	}



	public function actionOut()
	{
		$this->getUser()->logout( true );
		$this->flashMessage( 'front.sign.out.flash' );

		$this->redirect( ':Front:Default:default' );
	}


//////component//////////////////////////////////////////////////////////////


	protected function createComponentSignForm()
	{
		return $this->signFormFactory->create();
	}


	public function signInFormSucceeded( $form, $values )
	{
		if ( $values->remember )
		{
			$this->getUser()->setExpiration( '14 days', FALSE );
		}
		else
		{
			$this->getUser()->setExpiration( 0, TRUE );
		}

		try
		{
			$this->getUser()->login( $values->user_name, $values->password );
		}
		catch ( Nette\Security\AuthenticationException $e )
		{
			$form->addError( $this->translator->translate( $e->getMessage() ) );
			return;
		}
		catch ( App\Exceptions\AccessDeniedException $e )
		{
			$this->flashMessage( 'front.forms.sign.in.access-denied' );
			return;
		}

		$this->flashMessage( 'front.forms.sign.in.success' );

		if ( $url = $this->getReferer( 'signInReferrer' ) )
		{
			$this->redirectUrl( $url );
		}

		$this->redirect( ':Front:Default:default' );

	}

}
