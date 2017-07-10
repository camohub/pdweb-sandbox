<?php

namespace App\FrontModule\Forms;


use App;
use App\Model\Services\CommentsArticlesService;
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

	/** @var  Nette\Security\User */
	protected $user;


	public function __construct( CommentsArticlesService $cAS, ArticlesRepository $aR, Nette\Security\User $u )
	{
		$this->commentsArticlesService = $cAS;
		$this->articlesRepository = $aR;
		$this->user = $u;
	}


	public function create()
	{
		$form = new Nette\Application\UI\Form;
		$form->addProtection( 'Vypšal čas k odoslaniu formulára. Požiadavka bola zamietnutá.' );

		$form->addTextArea( 'content', 'Vložte komentár' )
			->setRequired( 'Komentár je povinná položka' )
			->setAttribute( 'class', 'form-control' );

		// This is a trap for robots
		$form->addText( 'name', 'Vyplňte meno' )
			->setAttribute( 'class', 'disp-none' );

		$form->addSubmit( 'send', 'Uložiť komentár' )
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
			$presenter->flashMessage( 'Pridávať komentáre môžu iba regirovaní užívatelia.', 'error' );
			throw new App\Exceptions\AccessDeniedException( 'Pridávať komentáre môžu iba regirovaní užívatelia.' );
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
			$presenter->flashMessage( 'Ďakujeme za komentár', 'success' );
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			$form->addError( 'Došlo k chybe. Váš komentár sa nepodarilo odoslať. Skúste to prosím neskôr.' );
			return;
		}

		$presenter->redirect( 'this' );

	}

}
