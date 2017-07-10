<?php

namespace App\AdminModule\Forms;


use Nette;
use App;
use App\Model\Repositories\ArticlesCategoriesArticlesRepository;
use App\Model\Repositories\CategoriesArticlesRepository;
use App\Model\Services\CategoriesArticlesService;
use App\Model\Repositories\ArticlesRepository;
use App\Model\Services\ArticlesService;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class ArticleFormFactory
{

	/** @var  ArticlesRepository */
	protected $articlesRepository;

	/** @var  ArticlesService */
	protected $articlesService;

	/** @var  CategoriesArticlesService */
	protected $categoriesService;

	/** @var  ArticlesCategoriesArticlesRepository */
	protected $articlesCategoriesArticlesRepository;


	public function __construct( ArticlesRepository $aR, ArticlesService $aS, CategoriesArticlesService $cAS, ArticlesCategoriesArticlesRepository $aCAR )
	{
		$this->articlesRepository = $aR;
		$this->articlesService = $aS;
		$this->categoriesArticlesService = $cAS;
		$this->articlesCategoriesArticlesRepository = $aCAR;
	}


	public function create( $id )
	{
		$form = new Form;

		$form->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');

		$form->addProtection( 'Vypršal čas vyhradený pre odoslanie formulára. Z dôvodu rizika útoku CSRF bola požiadavka na server zamietnutá.' );

		$form->addText( 'meta_desc', 'Popis', 60 )
			->setRequired( 'Popis je povinná položka.' )
			->setAttribute( 'class', 'form-control' );

		$form->addText( 'title', 'Nadpis', 60 )
			->setRequired( 'Nadpis je povinná položka.' )
			->setAttribute( 'class', 'form-control' );

		$form->addTextArea( 'perex', 'Perex' )
			->setRequired( 'Perex je povinná položka.' )
			->setAttribute( 'class', 'show-hidden-error form-control editor' );  // show-hidden-errors is necessary because of live-form-validation.js

		$form->addTextArea( 'content', 'Text', 120, 25 )
			->setRequired( 'Text je povinná položka.' )
			->setAttribute( 'class', 'show-hidden-error form-control editor' );

		$cat_sel = $this->categoriesArticlesService->toSelect();
		$form->addMultiSelect( 'categories', 'Vyberte kategóriu', $cat_sel, 8 )
			->setRequired( 'Aplikácia vyžaduje, aby bola priradená kategória pre článok.' )
			->setAttribute( 'class', 'form-control' );

		// May be it should be a select in the future.
		$form->addCheckbox( 'articles_statuses_id', ' Zverejniť' );

		$article = $this->articlesRepository->findOneBy( ['id' => $id] );
		$form->setDefaults( $article );
		$form['articles_statuses_id']->setDefaultValue( $article->articles_statuses_id == ArticlesRepository::STATUS_PUBLISHED || $article->articles_statuses_id == ArticlesRepository::STATUS_VIRTUAL ? true : false );
		$form['categories']->setDefaultValue( $this->articlesCategoriesArticlesRepository->findBy( ['articles_id' => $id, 'NOT categories_articles_id' => CategoriesArticlesRepository::NEWS_CATEGORY] )->fetchPairs( 'categories_articles_id', 'categories_articles_id' ) );

		$form->addSubmit( 'sbmt', 'Uložiť' )
			->setAttribute( 'class', ['btn btn-primary'] );

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}


	public function formSucceeded( Form $form )
	{
		$values = $form->getValues( true );
		$presenter = $form->getPresenter();
		$id = (int) $presenter->getParameter( 'id' );

		$values['perex'] = preg_replace( '#<pre>#', '<pre class="prettyprint custom">', $values['perex'] );
		$values['content'] = preg_replace( '#<pre>#', '<pre class="prettyprint custom">', $values['content'] );

		// We do not test ID cause every new article already has an ID as "virtual" article.
		try
		{
			$this->articlesService->updateArticle( $values, $id );
			$presenter->flashMessage( 'Článok bol upravený.' );
		}
		catch ( App\Exceptions\DuplicateEntryException $e )
		{
			$presenter->flashMessage( $e->getMessage(), 'error' );
			return $form;
		}
		catch ( \Exception $e )
		{
			$form->addError( 'Pri ukladaní článku došlo k chybe.' );
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, Debugger::ERROR );
			return $form;
		}

		$presenter->redirect( ':Admin:Articles:default' );
	}

}
