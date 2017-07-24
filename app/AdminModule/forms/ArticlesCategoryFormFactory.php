<?php

namespace App\AdminModule\Forms;


use App;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette;
use App\Model\Repositories\ArticlesCategoriesArticlesRepository;
use App\Model\Repositories\ArticlesCategoriesRepository;
use App\Model\Services\CategoriesArticlesService;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class ArticlesCategoryFormFactory
{

	use BootstrapRenderTrait;


	/** @var  EntityManager */
	protected $em;

	/** @var  CategoriesArticlesService */
	protected $categoriesArticlesService;

	/** @var  EntityRepository */
	protected $langsRepository;


	public function __construct( EntityManager $em, CategoriesArticlesService $cAS )
	{
		$this->em = $em;
		$this->categoriesArticlesService = $cAS;
		$this->langsRepository = $em->getRepository( App\Model\Entity\Lang::class );
	}


	public function create()
	{
		$form = new Nette\Application\UI\Form();
		//$form->elementPrototype->addAttributes( array( 'class' => 'ajax' ) );

		$form->addProtection( 'Vypršal čas vyhradený pre odoslanie formulára. Z dôvodu rizika útoku CSRF bola požiadavka na server zamietnutá.' );

		$langs = $this->langsRepository->findBy( [], ['id' => 'ASC'] );

		$form->addGroup();

		$names = $form->addContainer( 'titles', 'Názov kategórie' );
		foreach ( $langs as $lang )
		{
			$names->addText( $lang->getCode(), 'Názov ' . $lang->getCode() )
				->setRequired( 'Názov je povinné pole pre každý jazyk.' )
				->setAttribute( 'class', 'form-control' );
		}

		$form->addSelect( 'parent_id', 'Vyberte pozíciu', $this->categoriesArticlesService->categoriesToSelect() )
			->setPrompt( '' )
			->setAttribute( 'class', 'form-control select2' );

		$form->addSubmit( 'sbmt', 'Uložiť' )
			->setAttribute( 'class', 'btn btn-primary btn-sm' );

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $this->setBootstrapRender( $form );
	}


	public function formSucceeded( Form $form, $values )
	{
		$presenter = $form->getPresenter();
		$values = $presenter->isAjax() ? $form->getHttpData() : $form->getValues();

		if ( $presenter->isAjax() ) // Is before try block cause catch returns
		{
			$presenter->redrawControl( 'articlesCategories' );
			$presenter->redrawControl( 'sortableList' );
			$presenter->redrawControl( 'flash' );
		}

		try
		{
			$this->categoriesArticlesService->createCategory( $values );
		}
		catch ( App\Exceptions\DuplicateEntryException $e )
		{
			$presenter->flashMessage( 'Kategória s názvom ' . $values['name'] . ' už existuje. Musíte vybrať iný názov.', 'error' );
			return $form;
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			$presenter->flashMessage( 'Pri ukladaní došlo k chybe. ', 'error' );
			return $form;
		}

		$presenter->flashMessage( 'Kategória bola vytvorená.', 'success' );

		if ( $presenter->isAjax() )
		{
			return;
		}

		$presenter->redirect( ':Admin:Categories:articlesCategories' );
	}

}
