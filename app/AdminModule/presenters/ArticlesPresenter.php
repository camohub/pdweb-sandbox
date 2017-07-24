<?php


namespace App\AdminModule\Presenters;


use App;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette;
use Nette\Application\UI\Form;
use App\Model\Repositories\ArticlesRepository;
use App\Model\Services\ArticlesService;
use Tracy\Debugger;


class ArticlesPresenter extends App\AdminModule\Presenters\BasePresenter
{

	/** @var  EntityManager @inject */
	public $em;

	/** @var EntityRepository */
	public $userRepository;

	/** @var  ArticlesService @inject */
	public $articlesService;

	/** @var  App\AdminModule\Forms\ArticleFormFactory @inject */
	public $articleFormFactory;

	/** @var  App\AdminModule\Components\ArticlesDataGridFactory @inject */
	public $articlesDataGridFactory;

	/** @var  App\Model\Entity\Article */
	public $article;

	/** @var  Nette\Http\SessionSection */
	public $adminArticlesSession;

	/** @var  int */
	public $id = NULL;


	public function startup()
	{
		parent::startup();
		$this->adminArticlesSession = $this->getSession( 'admin_articles' );
		$this->userRepository = $this->em->getRepository( App\Model\Entity\User::class );
	}


	public function renderDefault()
	{

	}


	/**
	 * @desc Create action CREATES "VIRTUAL" ARTICLE before user shows the article form,
	 * because uploaded images need to know article ID to create /uploads/ID directory.
	 * Otherwise we can't create article specific dir for images.
	 * @throws App\Exceptions\AccessDeniedException
	 */
	public function actionCreate()
	{
		if ( ! $this->user->isAllowed( 'article', 'add' ) )
		{
			throw new App\Exceptions\AccessDeniedException( 'Nemáte oprávnenie vytvárať články.' );
		}

		try
		{
			// This ID will be used to name image uploads/ID directory. Otherwise we can't create article specific dir for images.
			$article = $this->articlesService->createDraft();
		}
		catch( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			$this->flashMessage( 'Pri vytváraní článku došlo k chybe. Súste to prosím znova, alebo kontaktujte administrátora.', 'error' );
			$this->forward( ':Admin:Articles:default' );
		}

		$this->forward( ':Admin:Articles:edit', $article->getId() );
	}


	public function actionEdit( $id )
	{
		$this->id = $this->template->id = $id;
		$article = $this->articlesService->articleRepository->find( $id );

		if ( ! $this->user->isAllowed( 'article', 'edit' )
			|| ! $this->user->id == $article->user->getId()
			|| ! $this->user->isInRole( 'admin' )
		)
		{
			throw new App\Exceptions\AccessDeniedException( 'Nemáte právo editovať tento článok.' );
		}

	}


	/**
	 * @secured
	 * @param $id
	 * @throws App\Exceptions\AccessDeniedException
	 */
	public function handleVisibility( $id )
	{
		$article = $this->articlesService->articleRepository->find( $id );

		if ( ! $this->user->isAllowed( 'article', 'edit' )
			|| ! $article->user->getId() == $this->user->id
			|| ! $this->user->isInRole( 'admin' )
		)
		{
			throw new App\Exceptions\AccessDeniedException( 'Nemáte oprávnenie editovať tento článok.' );
		}

		try
		{
			$this->articlesService->switchVisibility( $article );
			$this->flashMessage( 'Zmenili ste vyditeľnosť článku.' );
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in ' . $e->getFile() . ' on line ' . $e->getLine(), Debugger::ERROR );
			$this->flashMessage( 'Pri ukladaní údajov došlo k chybe.', Debugger::ERROR );
			// Do not return. Because of @secured it needs to be redirected.
		}

		$this->redirect( ':Admin:Articles:default' );

	}


	/**
	 * @secured
	 * @param $id
	 * @throws App\Exceptions\AccessDeniedException
	 */
	public function handleDelete( $id )
	{
		$article = $this->articlesService->articleRepository->find( $id );

		if ( ! $this->user->isAllowed( 'article', 'delete' )
			|| ! $article->getUser()->getId() == $this->user->id
			|| ! $this->user->isInRole( 'admin' )
		)
		{
			throw new App\Exceptions\AccessDeniedException( 'Nemáte oprávnenie zmazať tento článok.' );
		}

		try
		{
			$this->articlesService->delete( $article );
			$this->flashMessage( 'Článok bol zmazaný.' );
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			$this->flashMessage( 'Pri ukladaní údajov došlo k chybe.', 'error' );
			return;
		}

		$this->redirect( ':Admin:Articles:default' );
	}


//// COMPONENTS ////////////////////////////////////////////////////////////////

	public function createComponentArticleForm()
	{
		return $this->articleFormFactory->create( $this->id );
	}


	public function createComponentArticlesDataGrid()
	{
		return $this->articlesDataGridFactory->create();
	}


	public function createComponentArticlesDataGrid5()
	{
		return $this->articlesDataGrid5Factory->create();
	}


}
