<?php

namespace App\AdminModule\Forms;


use App;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette;
use App\Model\Repositories\CategoriesArticlesRepository;
use App\Model\Repositories\ArticlesCategoriesArticlesRepository;
use App\Model\Services\CategoriesArticlesService;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class ArticlesCategoryEditFormFactory
{

	/** @var  EntityManager */
	protected $em;

	/** @var  CategoriesArticlesService */
	protected $categoriesArticlesService;

	/** @var  EntityRepository */
	protected $categoryArticleRepository;


	public function __construct( EntityManager $em, CategoriesArticlesService $cAS )
	{
		$this->em = $em;
		$this->categoriesArticlesService = $cAS;
		$this->categoryArticleRepository = $em->getRepository( App\Model\Entity\CategoryArticle::class );
	}


	public function create( $id )
	{
		$form = new Nette\Application\UI\Form();
		//$form->elementPrototype->addAttributes( array( 'class' => 'ajax' ) );

		$form->addProtection( 'Vypršal čas vyhradený pre odoslanie formulára. Požiadavka bola z bezpečnostných dôvodov zamietnutá.' );

		$names = $form->addContainer( 'titles' );

		$category = $this->categoryArticleRepository->find( $id );
		foreach ( $category->getLangs() as $lang )
		{
			$names->addText( $lang->getCode(), $lang->getCode() )
				->setRequired( 'Názov je povinné pole.' )
				->setAttribute( 'class', 'form-control' )
				->setDefaultValue( $lang->getTitle() );
		}

		$form->addHidden( 'id', $id );

		$form->addSubmit( 'sbmt', 'Premenovať' );

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}


	public function formSucceeded( Form $form, $values )
	{
		$presenter = $form->getPresenter();
		$values = $presenter->isAjax() ? $form->getHttpData() : $form->getValues();

		if ( $presenter->isAjax() ) // Is before try block cause catch returns
		{
			$presenter->redrawControl( 'sortableList' );
			$presenter->redrawControl( 'sortableListScript' );
			$presenter->redrawControl( 'flash' );
			// If need rewrite ONE dynamic snippet, template needs to get ONLY THIS ONE. Render method in presenter sets this value.
			$presenter->articlesCategories = $this->categoryArticleRepository->findBy( ['id =' => $values['id']] );
		}

		try
		{
			$this->categoriesArticlesService->updateTitle( $values['id'], $values['titles'] );
		}
		catch ( App\Exceptions\DuplicateEntryException $e )
		{
			$presenter->flashMessage( 'Kategória s vybraným názvom už existuje. Názov musí byť unikátny pre každý jazyk.', 'error' );
			return $form;
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			$presenter->flashMessage( 'Pri ukladaní údajov došlo k chybe. ', 'error' );
			return $form;
		}

		$presenter->flashMessage( 'Názov kategórie bol zmenený.', 'success' );

		if ( $presenter->isAjax() )
		{
			return;
		}

		$presenter->redirect( ':Admin:Categories:articlesCategories' );
	}

}
