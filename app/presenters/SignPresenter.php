<?php


namespace App\Presenters;


use Nette;
use App;


class SignPresenter extends BasePresenter
{

	public function renderIn()
	{
		// Sign:in Facebook login makes redirect to itself(It means this action).
		// But user is already logged in.
		if ( $this->user->isLoggedIn() )
		{
			$this->redirect( ':Articles:show' );
		}

		$this->getComponent( 'breadcrumbs' )->add( 'Prihlásiť', 'Sign:in' );

		$this->template->fb = TRUE;
		$this->setHeaderTags( NULL, NULL, $robots = 'noindex, nofolow' );

		$this->setReferer( 'signInReferrer' );
	}



	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage( 'Boli ste odhlásený.' );

		$this->redirect( 'Articles:show' );
	}


//////component//////////////////////////////////////////////////////////////

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
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
			$form->addError( $e->getMessage() );
			return;
		}
		catch ( App\Exceptions\AccessDeniedException $e )
		{
			$this->flashMessage( 'Váš účet ešte nebol aktivovaný emailom, alebo je zablokovaný.' );
			return;
		}

		$this->flashMessage( 'Vitajte ' . $values['user_name'] );

		if ( $url = $this->getReferer( 'signInReferrer' ) )
		{
			$this->redirectUrl( $url );
		}

		$this->redirect( ':Articles:show' );

	}

}
