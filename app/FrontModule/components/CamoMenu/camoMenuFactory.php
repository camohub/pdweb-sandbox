<?php

namespace App\FrontModule\Components;

use App\Model\Repositories\CategoriesArticlesRepository;
use	Nette\Application\UI\Control;
use Nette\Caching\Cache;
use	Tracy\Debugger;


class CamoMenu extends Control
{

	/** @var CategoriesArticlesRepository */
	protected $categoriesArticlesRepository;

	/** @var  Cache */
	protected $cache;

	/**
	 * @desc Is id of current category.
	 * @var  int
	 */
	public $current_id = -1;  // null can fail.

	/**
	 * @desc Displays only visible categories if FALSE.
	 * @var  bool
	 */
	public $admin = FALSE;

	/**
	 * @desc Is used if we want to display only one section.
	 * @var null
	 */
	public $only_category = NULL;


	public function __construct( CategoriesArticlesRepository $cAR/*, Cache $cache*/ )
	{
		parent::__construct();

		$this->categoriesArticlesRepository = $cAR;
		//$this->cache = $cache;

	}


	/**
	 * returns Nette\Application\UI\ITemplate
	 */
	public function render()
	{
		$template = $this->template;
		$template->setFile( __DIR__ . '/menu.latte' );

		$template->category = $this->categoriesArticlesRepository->findBy( [ 'parent_id' => $this->only_category ] )->order( 'priority ASC' );

		$template->current_id = $this->current_id;
		$template->categoriesArticlesRepository = $this->categoriesArticlesRepository;

		$template->render();
	}


	/**
	 * @desc This sets the current category
	 * @param $id
	 */
	public function setCategory( $id )
	{
		$this->current_id = (int) $id;
	}


	/**
	 * @desc If is TRUE invisible categories will be displayed.
	 */
	public function setAdmin()
	{
		$this->admin = TRUE;
	}


	/**
	 * @desc This is used if we want to show only one category.
	 * @param $id
	 */
	public function setSection( $id )
	{
		$this->only_category = (int) $id;
	}

}



interface ICamoMenuControlFactory
{

	/**
	 * @return CamoMenu
	 */
	public function create();

}

