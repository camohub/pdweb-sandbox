<?php

namespace App\AdminModule\Presenters;

use App;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette;
use Tracy\Debugger;


class DefaultPresenter extends BasePresenter
{

	/** @var EntityManager @inject */
	public $em;

	/** @var App\Model\Services\ArticlesService @inject */
	public $articleService;

	/** @var EntityRepository */
	public $articleRepository;


	public function startup()
	{
		parent::startup();
		$this->articleRepository = $this->em->getRepository( App\Model\Entity\Article::class );
	}

	protected function beforeRender()
	{
		$this->template->config = $this->context->parameters;

	}


	public function renderDefault()
	{

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
