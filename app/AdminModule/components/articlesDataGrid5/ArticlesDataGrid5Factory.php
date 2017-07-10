<?php

namespace App\AdminModule\Components;


use App\Model\Repositories\ArticlesRepository;
use Grido\Grid;
use Nette;


class ArticlesDataGrid5Factory
{

	/** ArticlesRepository */
	protected $articlesRepository;


	public function __construct( ArticlesRepository $aR )
	{
		$this->articlesRepository = $aR;
	}


	public function create()
	{
		$grid = new Grid();

		$grid->setModel( $this->articlesRepository->findAll() );
		$grid->addColumnText( 'title', 'Title' );
		$grid->addColumnText( 'user_name', 'Author' )
			->setColumn( 'acl_users.user_name' )
			->setSortable()
			->setFilterText()
			/*->setCustomRender( function ( $row ) {
				return $row->ref( 'acl_users', 'user_id' )->user_name;
			})*/;
		//$grid->addColumnText( 'user_name', 'Author', 'acl_users.user_name:id' );  // https://ublaboo.org/datagrid/data-source
		$grid->addColumnText( 'created', 'Created' );
		$grid->addColumnText( 'articles_statuses_id', 'Status' );

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