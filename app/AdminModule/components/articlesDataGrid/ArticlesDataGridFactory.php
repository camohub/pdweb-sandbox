<?php

namespace App\AdminModule\Components;


use App\Model\Entity;
use App\Model\Repositories\ArticlesRepository;
use App\Model\Services\ArticlesService;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Tracy\Debugger;
use Ublaboo\DataGrid\DataGrid;
use Nette;


class ArticlesDataGridFactory
{

	/** @var EntityManager  */
	protected $em;

	/** ArticleService */
	protected $articlesService;

	/** ArticleRepository */
	protected $articlesRepository;

	/** Translator */
	protected $translator;


	public function __construct( EntityManager $em, ArticlesService $aS, Translator $t )
	{
		$this->em = $em;
		$this->articlesService = $aS;
		$this->articleRepository = $em->getRepository( Entity\Article::class );
		$this->translator = $t;
	}


	public function create()
	{
		$grid = new DataGrid();

		$grid->setDataSource( $this->articlesService->findByForDatagrid([
			'status.id =' => [Entity\Status::STATUS_DRAFT, Entity\Status::STATUS_PUBLISHED, Entity\Status::STATUS_UNPUBLISHED],
		]));

		$grid->addColumnText( 'id', 'ID' );

		$grid->addColumnText( 'title', 'Title', 'langs.title' )
			->setRenderer( function ( $article ) {
				return $article->lang->getTitle();  // Calls $article->getDefaultLang( 'sk' )
			})
			->setSortable()
			->setFilterText()
			->setSplitWordsSearch( FALSE );

		$grid->addColumnText( 'user_name', 'Author', 'user.user_name' )  // https://ublaboo.org/datagrid/data-source
			->setSortable()
			->setFilterText()
			->setSplitWordsSearch( FALSE );

		$grid->addColumnText( 'statuses_id', 'Status', 'status.id' )
			->setReplacement( [Entity\Status::STATUS_DRAFT => 'Draft', Entity\Status::STATUS_PUBLISHED => 'Publikovaný', Entity\Status::STATUS_UNPUBLISHED => 'Nepublikovaný'] )
			->setSortable()
			->setFilterMultiSelect( ['' => 'All items', Entity\Status::STATUS_DRAFT => 'Draft', Entity\Status::STATUS_PUBLISHED => 'Published', Entity\Status::STATUS_UNPUBLISHED => 'Unpublished'] );

		$grid->addColumnDateTime( 'created', 'Created' )->setFormat( 'd.m. Y H:i:s' )
			->setSortable()
			->setFilterDateRange();

		$grid->addAction( 'visibility!', '')
			->setClass( function( $row )
			{
				if( $row->status->getId() == Entity\Status::STATUS_PUBLISHED ) return 'grid-action color-5';
				if( $row->status->getId() == Entity\Status::STATUS_UNPUBLISHED ) return 'grid-action color-7';
				return 'disp-none';
			})
			->setIcon( function( $row )
			{
				if( $row->status->getId() == Entity\Status::STATUS_PUBLISHED ) return 'eye';
				if( $row->status->getId() == Entity\Status::STATUS_UNPUBLISHED ) return 'eye-slash';
				return '';
			})
			->setTitle( function ( $row )
			{
				if( $row->status->getId() == Entity\Status::STATUS_PUBLISHED ) return 'Unpublish';
				if( $row->status->getId() == Entity\Status::STATUS_UNPUBLISHED ) return 'Publish';
				return '';
			});

		$grid->addAction( 'edit', '')->setIcon( 'pencil' )->setClass( 'grid-action color-5' )->setTitle( 'Edit' );

		$grid->addAction( ':Admin:CommentsArticles:default', '')->setIcon( 'comment-o' )->setClass( 'color-6' )->setTitle( 'Comments' );

		$grid->addAction( 'delete!', '')
			->setConfirm( 'Do you really want to delete this item?' )
			->setIcon( 'trash' )
			->setClass( 'grid-action-danger color-7' )
			->setTitle( 'Delete' );

		return $grid;
	}


	/**
	 * @desc This method creates compoment ArticlesDataGrid in component ArticlesDataGrid :)
	 * @param $name
	 * @return DataGrid
	 */
	/*public function createComponentArticlesDataGrid( $name )
	{


	}*/


}

/*
interface IArticlesDataGridFactory
{

	/**
	 * @return ArticlesDataGridControl
	 /
	public function create();
}*/