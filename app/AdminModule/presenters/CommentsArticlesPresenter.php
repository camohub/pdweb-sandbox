<?php
namespace App\AdminModule\Presenters;

use App\Model\Repositories\ArticlesRepository;
use App\Model\Repositories\CommentsArticlesRepository;
use App\Model\Services\CommentsArticlesService;
use Nette;
use App;
use Tracy\Debugger;

class CommentsArticlesPresenter extends App\AdminModule\Presenters\BasePresenter
{

	/** @var  ArticlesRepository @inject */
	public $articlesRepository;

	/** @var  CommentsArticlesService @inject */
	public $commentsArticlesService;

	/** @var  CommentsArticlesRepository @inject */
	public $commentsArticlesRepository;


	public function renderDefault( $id )
	{
		$this->template->article = $this->articlesRepository->findOneBy( ['id' => (int) $id] );
		$this->template->comments = $this->commentsArticlesRepository->findBy( ['articles_id' => (int) $id] )->order( 'id DESC' );
		$this->template->commentsArticlesRepository = $this->commentsArticlesRepository;
	}


	/**
	 * @secured
	 * @param $comment_id
	 * @throws App\Exceptions\AccessDeniedException
	 */
	public function handleVisibility( $comment_id )
	{
		if ( ! $this->user->isAllowed( 'comment', 'delete' ) )
		{
			throw new App\Exceptions\AccessDeniedException( 'Nemáte oprávnenie mazať komentáre.' );
		}

		$comment = $this->commentsArticlesRepository->findOneBy( ['id' => (int) $comment_id] );

		if ( $comment->acl_users_id != $this->user->id || ! $this->user->isInRole( 'admin' ) )
		{
			throw new App\Exceptions\AccessDeniedException( 'Nemáte oprávnenie zmazať tento komentár.' );
		}

		try
		{
			$this->commentsArticlesService->switchVisibility( $comment->id );
			$this->flashMessage( 'Viditeľnosť komentára bola zmenená.' );
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), Debugger::ERROR );
			$this->flashMessage( 'Pri editovaní komentára došlo k chybe.', 'error' );
		}


		if ( $this->isAjax() )
		{
			$this->redrawControl( 'flash' );
			$this->redrawControl( 'comments' );
			return;
		}

		$this->redirect( 'this' );

	}


}
