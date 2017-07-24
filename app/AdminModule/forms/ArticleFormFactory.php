<?php

namespace App\AdminModule\Forms;


use App;
use Nette;
use App\Model\Entity;
use Kdyby\Doctrine\EntityManager;
use Doctrine\ORM\EntityRepository;
use App\Model\Services\CategoriesArticlesService;
use App\Model\Services\ArticlesService;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class ArticleFormFactory
{

	use BootstrapRenderTrait;


	/** @var  EntityManager */
	protected $em;

	/** @var  EntityRepository */
	protected $langRepository;

	/** @var  EntityRepository */
	protected $articleRepository;

	/** @var  ArticlesService */
	protected $articlesService;

	/** @var  CategoriesArticlesService */
	protected $categoriesService;


	public function __construct( EntityManager $em, ArticlesService $aS, CategoriesArticlesService $cAS )
	{
		$this->em = $em;
		$this->articlesService = $aS;
		$this->categoriesArticlesService = $cAS;
		$this->langRepository = $this->em->getRepository( Entity\Lang::class );
		$this->articleRepository = $this->em->getRepository( Entity\Lang::class );
	}


	public function create( $id )
	{
		$form = new Form;

		// This line is IMPORTANT!!!
		$form->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');

		$form->addProtection( 'Vypršal čas vyhradený pre odoslanie formulára. Z dôvodu rizika útoku CSRF bola požiadavka na server zamietnutá.' );

		$article = $this->articlesService->articleRepository->find( $id );

		$form->addGroup( 'Popis' );
		$meta_desc = $form->addContainer( 'meta_desc' );

		foreach ( $article->getLangs() as $lang )
		{
			$meta_desc->addText( $lang->getCode(), $lang->getCode(), 60 )
				->setRequired( 'Popis je povinná položka.' )
				->setAttribute( 'class', 'form-control' )
				->setDefaultValue( $lang->getMetaDesc() );
		}

		$form->addGroup( 'Nadpis' );
		$title = $form->addContainer( 'title' );

		foreach ( $article->getLangs() as $lang )
		{
			$title->addText( $lang->getCode(), $lang->getCode(), 60 )
				->setRequired( 'Nadpis je povinná položka.' )
				->setAttribute( 'class', 'form-control' )
				->setDefaultValue( $lang->getTitle() );
		}

		$form->addGroup( 'Perex' );
		$perex = $form->addContainer( 'perex' );

		foreach ( $article->getLangs() as $lang )
		{
			$perex->addTextArea( $lang->getCode(), $lang->getCode() )
				->setRequired( 'Perex je povinná položka.' )
				->setAttribute( 'class', 'show-hidden-error form-control editor' )  // show-hidden-errors is necessary because of live-form-validation.js
			->setDefaultValue( $lang->getPerex() );
		}

		$form->addGroup( 'Text' );
		$content = $form->addContainer( 'content' );

		foreach ( $article->getLangs() as $lang )
		{
			$content->addTextArea( $lang->getCode(), $lang->getCode(), 120, 15 )
				->setRequired( 'Text je povinná položka.' )
				->setAttribute( 'class', 'show-hidden-error form-control editor' )
				->setDefaultValue( $lang->getContent() );
		}

		$form->addGroup( 'Ďalšie nastavenia' );
		$cat_sel = $this->categoriesArticlesService->categoriesToSelect();
		$form->addMultiSelect( 'categories', 'Vyberte kategóriu', $cat_sel, 8 )
			->setAttribute( 'class', 'form-control' )
			->setDefaultValue( $article->getDefaultCategoriesToSelect() );

		// May be it should be a select in the future.
		$form->addCheckbox( 'statuses_id', ' Zverejniť' )
			->setDefaultValue( $article->status->getId() || Entity\Status::STATUS_DRAFT || Entity\Status::STATUS_PUBLISHED ? true : false );


		$form->addSubmit( 'sbmt', 'Uložiť článok' )
			->setAttribute( 'class', 'btn btn-primary' );

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $this->setBootstrapRender( $form );
	}


	public function formSucceeded( Form $form )
	{
		$values = $form->getValues( true );
		$presenter = $form->getPresenter();
		$id = (int) $presenter->getParameter( 'id' );

		$values['perex'] = preg_replace( '#<pre>#', '<pre class="prettyprint custom">', $values['perex'] );
		$values['content'] = preg_replace( '#<pre>#', '<pre class="prettyprint custom">', $values['content'] );

		// We do not test ID cause every new article already has an ID as "draft" article.
		try
		{
			$this->articlesService->updateArticle( $values, $id );
			$presenter->flashMessage( 'Článok bol uložený.' );
		}
		catch ( App\Exceptions\DuplicateEntryException $e )
		{
			$presenter->flashMessage( $e->getMessage(), 'error' );
			return $form;
		}
		catch ( \Exception $e )
		{
			$form->addError( 'Pri ukladaní článku došlo k chybe. Skúste to prosím znova, alebo kontaktujte adminstrátora.' );
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, Debugger::ERROR );
			return $form;
		}

		$presenter->redirect( ':Admin:Articles:default' );
	}

}
