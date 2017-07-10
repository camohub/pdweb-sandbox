<?php


namespace App\AdminModule\Presenters;


use App;
use Nette;
use Nette\Application\UI\Form;
use App\Model\Repositories\ArticlesRepository;
use App\Model\Services\ArticlesService;
use Tracy\Debugger;


class ArticlesPresenter extends App\AdminModule\Presenters\BasePresenter
{

	/** @var  ArticlesService @inject */
	public $articlesService;

	/** @var  ArticlesRepository @inject */
	public $articlesRepository;

	/** @var  App\Model\Repositories\AclUsersRepository @inject */
	public $aclUsersRepository;

	/** @var  App\AdminModule\Forms\ArticleFormFactory @inject */
	public $articleFormFactory;

	/** @var  App\AdminModule\Components\ArticlesDataGridFactory @inject */
	public $articlesDataGridFactory;

	/** @var  App\AdminModule\Components\ArticlesDataGrid5Factory @inject */
	public $articlesDataGrid5Factory;

	/** @var  Nette\Database\IRow */
	public $article;

	/** @var  Nette\Http\SessionSection */
	public $adminArticlesSession;

	/** @var  int */
	public $id = NULL;


	public function startup()
	{
		parent::startup();
		$this->adminArticlesSession = $this->getSession( 'adminArticles' );
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

		// This ID will be used to name image uploads/ID directory. Otherwise we can't create article specific dir for images.
		$id = $this->articlesService->createVirtualArticle();
		$this->forward( ':Admin:Articles:edit', $id );
	}


	public function actionEdit( $id )
	{
		$this->id = $this->template->id = $id;
		$article = $this->articlesRepository->findOneBy( ['id' => $id] );

		if ( ! $this->user->isAllowed( 'article', 'edit' )
			|| ! $this->user->id == $article->acl_users_id
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
		$article = $this->articlesRepository->findOneBy( ['id' => (int) $id] );

		if ( ! $this->user->isAllowed( 'article', 'edit' )
			|| ! $article->acl_users_id == $this->user->id
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
			$this->flashMessage( 'Pri upravovaní údajov došlo k chybe.', Debugger::ERROR );
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
		$article = $this->articlesRepository->findOneBy( ['id' => (int) $id] );

		if ( ! $this->user->isAllowed( 'article', 'delete' )
			|| ! $article->acl_users_id == $this->user->id
			|| ! $this->user->isInRole( 'admin' )
		)
		{
			throw new App\Exceptions\AccessDeniedException( 'Nemáte oprávnenie zmazať tento článok.' );
		}

		try
		{
			$this->articlesRepository->update( $article->id, ['articles_statuses_id' => ArticlesRepository::STATUS_DELETED] );
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
