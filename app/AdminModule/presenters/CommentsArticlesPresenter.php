<?php


namespace App\AdminModule\Presenters;


use App\Model\Services\CommentsArticlesService;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette;
use App;
use Tracy\Debugger;

class CommentsArticlesPresenter extends App\AdminModule\Presenters\BasePresenter
{

	/** @var  EntityManager @inject */
	public $em;

	/** @var  EntityRepository */
	public $articleRepository;

	/** @var  EntityRepository */
	public $commentArticleRepository;

	/** @var  CommentsArticlesService @inject */
	public $commentsArticlesService;


	public function startup()
	{
		parent::startup();
		$this->articleRepository = $this->em->getRepository( App\Model\Entity\Article::class );
		$this->commentArticleRepository = $this->em->getRepository( App\Model\Entity\CommentArticle::class );
	}


	public function renderDefault( $id )
	{
		$this->template->article = $this->articleRepository->find( (int) $id );
		$this->template->commentArticleRepository = $this->commentArticleRepository;
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

		$comment = $this->commentArticleRepository->findOneBy( ['id' => (int) $comment_id] );

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
