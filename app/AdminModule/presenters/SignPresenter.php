<?php

namespace App\AdminModule\Presenters;

use App;
use Nette;
use App\Presenters\BasePresenter;


class SignPresenter extends BasePresenter
{

    /** @var App\Model\UserManager @inject  */
    public $userManager;


    protected function startup()
    {
        parent::startup();
    }


    protected function beforeRender()
    {

    }


    public function actionIn()
    {
        if ( $this->user->isLoggedIn() )
        {
            $this->redirect('Default:');
        }
    }


    /**
     * Sign-in form factory.
     *
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->addProtection('Vypršal časový limit pre prihlásenie, zopakuj prihlásenie!');
        $form->addText('email', 'E-mail')
            ->setRequired('Zadaj e-mail!')
            ->setAttribute('placeholder', 'E-mail');
        $form->addPassword('password', 'Heslo')
            ->setRequired('Zadaj heslo!')
            ->setAttribute('placeholder', 'Heslo');
        $form->addCheckbox('remember', 'Zapamätať prihlásenie');
        $form->addSubmit('signin', 'Prihlásiť');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }


    /**
     * Processing of SignInForm component.
     *
     * @param $form
     * @param $values
     */
    public function signInFormSucceeded($form, $values)
    {
        if ($values->remember) {
            $this->getUser()->setExpiration('14 days', FALSE);
        } else {
            $this->getUser()->setExpiration('20 minutes', TRUE);
        }

        try {
            $this->userManager->login($values->email, $values->password);
            $this->flashMessage('Prihlásenie úspešné!', 'alert-success');
            $this->redirect('Default:');
        } catch (\Nette\Security\AuthenticationException $e) {
            $this->flashMessage('Nesprávne prihlasovacie údaje!', 'alert-danger');
        }
    }


    /**
     * Sign out user.
     */
    public function actionOut()
    {
        $this->getUser()->logout(TRUE);
        $this->flashMessage('Odhlásenie úspešné!', 'alert-success');
        $this->redirect('Sign:in');
    }
}
