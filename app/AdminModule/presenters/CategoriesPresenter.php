<?php


namespace App\AdminModule\Presenters;


use App;
use Nette;
use App\AdminModule\Forms\ArticlesCategoryEditFormFactory;
use App\AdminModule\Forms\ArticlesCategoryFormFactory;
use App\Model\Repositories\CategoriesArticlesRepository;
use Tracy\Debugger;


class CategoriesPresenter extends App\AdminModule\Presenters\BasePresenter
{

	/** @var  CategoriesArticlesRepository @inject */
	public $categoriesArticlesRepository;

	/** @var  App\Model\Services\CategoriesArticlesService @inject */
	public $categoriesArticlesService;

	/** @var  ArticlesCategoryFormFactory @inject */
	public $articlesCategoryFormFactory;

	/** @var  ArticlesCategoryEditFormFactory @inject */
	public $articlesCategoryEditFormFactory;

	/** @var  array */
	public $articlesCategories;


	public function startup()
	{
		parent::startup();

		if ( ! $this->user->isAllowed( 'menu', 'edit' ) )
		{
			throw new App\Exceptions\AccessDeniedException( 'Nemáte oprávnenie editovať kategórie.' );
		}
	}


	public function actionArticlesCategories( $id = NULL )
	{
		$this->articlesCategories = $this->categoriesArticlesRepository->findBy( ['parent_id' => NULL] )->order( 'priority ASC' );
	}


	public function renderArticlesCategories( $id = NULL )
	{
		// $categories can be changed by some handlers, actions or forms e.g. articlesCategoryEditForm.
		$this->template->categories = $this->articlesCategories;
		$this->template->categoriesArticlesRepository = $this->categoriesArticlesRepository;
	}


	/**
	 * @secured
	 */
	public function handleCategoriesArticlesPriority()
	{
		try
		{
			$this->categoriesArticlesService->updatePriority( $_GET['categoryItems'] );
			$this->flashMessage( 'Poradie položiek bolo upravené.' );
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			$this->flashMessage( 'Pri ukladaní údajov došlo k chybe.', 'error' );
		}

		if ( $this->isAjax() )
		{
			$this->redrawControl( 'flash' );
			return;
		}

		$this->redirect( 'this' );

	}


	/**
	 * @param $id integer
	 * @secured
	 */
	public function handleChangeArticlesCategoryVisibility( $id )
	{
		if ( $this->isAjax() )
		{
			$this->redrawControl( 'sortableList' );
			$this->redrawControl( 'sortableListScript' );
			$this->redrawControl( 'flash' );
		}

		try
		{
			$this->categoriesArticlesService->switchVisibility( $id );
			// Dynamic snippet redraw needs to set only one item to template.
			$this->articlesCategories = $this->categoriesArticlesRepository->findBy( ['id' => $id] );
			$this->flashMessage( 'Viditeľnosť položky bola upravená.', 'success' );
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage(), 'error' );
			$this->flashMessage( 'Pri ukladaní údajov došlo k chybe.', 'error' );
		}

		if( $this->isAjax() )
		{
			return;
		}

		$this->redirect( ':Admin:Menu:default' );

	}


	/**
	 * @param $id
	 * @secured
	 */
	public function handleDeleteArticleCategory( $id )
	{
		if ( $this->isAjax() )  // Not used for now.
		{
			$this->redrawControl( 'sortableList' );
			$this->redrawControl( 'flash' );
		}

		try
		{
			$names = $result = $this->categoriesArticlesService->delete( $id );
			$this->articlesCategories = $this->categoriesArticlesRepository->findBy( ['parent_id' => NULL] )->order( 'priority ASC' );
			$this->flashMessage( 'Item(s) ' . join( ', ', $names ) . ' has(ve) been deleted.' );
		}
		catch ( App\Model\Services\NoArticleException $e )
		{
			return;
		}
		catch ( App\Model\Services\PartOfAppException $e )
		{
			$this->flashMessage( $e->getMessage(), 'error' );
			return;
		}
		catch ( App\Model\Services\ContainsArticleException $e )
		{
			$this->flashMessage( $e->getMessage(), 'error' );
			return;
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
			$this->flashMessage( 'Pri ukladaní údajov došlo k chybe.', 'error' );
			return;
		}

		if ( $this->isAjax() )
		{
			return;
		}

		$this->redirect( ':Admin:Categories:articlesCategories' );

	}


////// Controls ////////////////////////////////////////////////////////////////////////

	public function createComponentArticlesCategoryForm()
	{
		return $this->articlesCategoryFormFactory->create();
	}


	public function createComponentEditCategoryForm()
	{
		$that = $this;
		return new Nette\Application\UI\Multiplier( function ( $id ) use ( $that )
		{
			$form = $this->articlesCategoryEditFormFactory->create( $id );
			// Forms in snippets needs to be set in template as _form.
			// In this case dynamic forms have to be set white rendering separately.
			$that->template->_form = $form;
			return $form;
		});
	}

}
