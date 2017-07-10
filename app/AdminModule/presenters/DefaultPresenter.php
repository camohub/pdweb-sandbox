<?php

namespace App\AdminModule\Presenters;

use App;
use Nette;
use Tracy\Debugger;


class DefaultPresenter extends BasePresenter
{

    protected function beforeRender()
    {
        $this->template->config = $this->context->parameters;
    }


    public function createComponentTinyForm()
	{
		$form = new Nette\Application\UI\Form();

		$form->addText( 'text', 'Input' )
			->setRequired( 'Vyplnte input' )
			->setAttribute( 'class', ['form-control'] );

		$form->addTextArea( 'editor', 'TinyEditor', 50, 20 )
			->setRequired( 'Vyplnte obsah' )
			->setAttribute( 'class', ['form-control'] );

		$form->addSubmit( 'submit', 'Send' )
			->setAttribute( 'class', 'btn btn-primary' );

		$form->onSuccess[] = [$this, 'tinyFormSuccess'];

		return $form;
	}


	public function tinyFormSuccess( $form )
	{
		$this->flashMessage( 'Formular bol odoslany', 'success' );
		$this->redirect( ':Admin:Default:default' );
	}

}
