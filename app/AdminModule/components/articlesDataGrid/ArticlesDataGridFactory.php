<?php

namespace App\AdminModule\Components;


use App\Model\Repositories\ArticlesRepository;
use Ublaboo\DataGrid\DataGrid;
use Nette;


class ArticlesDataGridFactory
{

	/** ArticlesRepository */
	protected $articlesRepository;

	/** Array */
	protected $articlesStatuses;


	public function __construct( ArticlesRepository $aR )
	{
		$this->articlesRepository = $aR;
	}


	public function create()
	{
		$grid = new DataGrid();

		$grid->setDataSource( $this->articlesRepository->findBy( ['articles_statuses_id' => [ArticlesRepository::STATUS_VIRTUAL, ArticlesRepository::STATUS_PUBLISHED, ArticlesRepository::STATUS_UNPUBLISHED]] )->order( 'id DESC' ) );

		$grid->addColumnText( 'id', 'ID' );

		$grid->addColumnText( 'title', 'Title' )
			->setSortable()
			->setFilterText()
			->setSplitWordsSearch(FALSE);

		$grid->addColumnText( 'user_name', 'Author', 'acl_users.user_name' )  // https://ublaboo.org/datagrid/data-source
			->setSortable()
			->setFilterText()
			->setSplitWordsSearch(FALSE);

		$grid->addColumnText( 'articles_statuses_id', 'Status' )
			->setReplacement( [ArticlesRepository::STATUS_VIRTUAL => 'Draft', ArticlesRepository::STATUS_PUBLISHED => 'Publikovaný', ArticlesRepository::STATUS_UNPUBLISHED => 'Nepublikovaný'] )
			->setSortable()
			->setFilterMultiSelect( ['' => 'All items', ArticlesRepository::STATUS_VIRTUAL => 'Draft', ArticlesRepository::STATUS_PUBLISHED => 'Published', ArticlesRepository::STATUS_UNPUBLISHED => 'Unpublished'] );

		$grid->addColumnDateTime( 'created', 'Created' )->setFormat( 'd.m. Y H:i:s' )
			->setSortable()
			->setFilterDateRange();

		$grid->addAction( 'visibility!', '')
			->setClass( function( $row )
			{
				if( $row->articles_statuses_id == ArticlesRepository::STATUS_PUBLISHED ) return 'grid-action color-5';
				if( $row->articles_statuses_id == ArticlesRepository::STATUS_UNPUBLISHED ) return 'grid-action color-7';
				return 'disp-none';
			})
			->setIcon( function( $row )
			{
				if( $row->articles_statuses_id == ArticlesRepository::STATUS_PUBLISHED ) return 'eye';
				if( $row->articles_statuses_id == ArticlesRepository::STATUS_UNPUBLISHED ) return 'eye-slash';
				return '';
			})
			->setTitle( function ( $row )
			{
				if( $row->articles_statuses_id == ArticlesRepository::STATUS_PUBLISHED ) return 'Unpublish';
				if( $row->articles_statuses_id == ArticlesRepository::STATUS_UNPUBLISHED ) return 'Publish';
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