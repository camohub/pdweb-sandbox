<?php

namespace App\AdminModule\Forms;


use App;
use Nette;
use App\Model\Repositories\CategoriesArticlesRepository;
use App\Model\Repositories\ArticlesCategoriesArticlesRepository;
use App\Model\Services\CategoriesArticlesService;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class ArticlesCategoryEditFormFactory
{

	/** @var  CategoriesArticlesService */
	protected $categoriesArticlesService;

	/** @var  CategoriesArticlesRepository */
	protected $categoriesArticlesRepository;

	/** @var  ArticlesCategoriesArticlesRepository */
	protected $articlesCategoriesArticlesRepository;


	public function __construct( CategoriesArticlesService $cAS, CategoriesArticlesRepository $cAR, ArticlesCategoriesArticlesRepository $aCAR )
	{
		$this->categoriesArticlesService = $cAS;
		$this->categoriesArticlesRepository = $cAR;
		$this->articlesCategoriesArticlesRepository = $aCAR;
	}


	public function create( $id )
	{
		$form = new Nette\Application\UI\Form();
		//$form->elementPrototype->addAttributes( array( 'class' => 'ajax' ) );

		$form->addProtection( 'Vypršal čas vyhradený pre odoslanie formulára. Z dôvodu rizika útoku CSRF bola požiadavka na server zamietnutá.' );

		$form->addText( 'name', 'Zmeňte názov' )
			->setRequired( 'Názov musí byť vyplnené.' );

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
			$presenter->articlesCategories = $this->categoriesArticlesRepository->findBy( ['id' => $values['id']] );
		}

		try
		{
			$this->categoriesArticlesService->updateName( $values['id'], $values['name'] );
		}
		catch ( App\Exceptions\DuplicateEntryException $e )
		{
			$presenter->flashMessage( 'Kategória s názvom ' . $values['name'] . ' už existuje. Musíte vybrať iný názov.', 'error' );
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
