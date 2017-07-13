<?php

namespace App\FrontModule\Forms;


use App;
use App\Model\Services\CommentsArticlesService;
use Kdyby\Translation\Translator;
use Nette;
use App\Model\Repositories\ArticlesRepository;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class CommentFormFactory
{

	/** @var  CommentsArticlesService */
	protected $commentsArticlesService;

	/** @var  ArticlesRepository */
	protected $articlesRepository;

	/** @var  Translator */
	protected $translator;

	/** @var  Translator */
	protected $pTranslator;

	/** @var  Nette\Security\User */
	protected $user;


	public function __construct( CommentsArticlesService $cAS, ArticlesRepository $aR, Translator $tr, Nette\Security\User $u )
	{
		$this->commentsArticlesService = $cAS;
		$this->articlesRepository = $aR;
		$this->user = $u;
		$this->translator = $tr;
		$this->pTranslator = $this->translator->domain( 'front.forms.comment-form' );
	}


	public function create()
	{
		$form = new Nette\Application\UI\Form;

		$form->setTranslator( $this->pTranslator );

		$form->addProtection( 'csrf' );

		$form->addTextArea( 'content', 'content.label' )
			->setRequired( 'content.required' )
			->setAttribute( 'class', 'form-control' );

		// This is a trap for robots
		$form->addText( 'name', 'name.label' )
			->setAttribute( 'class', 'disp-none' );

		$form->addSubmit( 'send', 'send.label' )
			->setAttribute( 'class', 'btn btn-primary btn-sm' );

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}


	public function formSucceeded( Form $form )
	{
		$presenter = $form->getPresenter();
		$values = $form->getValues();

		if ( ! $this->user->isAllowed( 'comment', 'add' ) )
		{
			$presenter->flashMessage( 'front.forms.comment-form.error1', 'error' );
			throw new App\Exceptions\AccessDeniedException( 'front.forms.comment-form.error1' );
		}

		if ( $values->name )  // Probably robot insertion
		{
			return;
		}

		$title = $presenter->getParameter( 'title' );
		$article = $this->articlesRepository->findOneBy( [ 'slug =' => $title ] );

		try
		{
			$this->commentsArticlesService->insertComment( $article->id, $values );
			$presenter->flashMessage( 'front.forms.comment-form.success', 'success' );
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			$form->addError( $this->translator->translate( 'front.forms.comment-form.error2' ) );
			return;
		}

		$presenter->redirect( 'this' );

	}

}
